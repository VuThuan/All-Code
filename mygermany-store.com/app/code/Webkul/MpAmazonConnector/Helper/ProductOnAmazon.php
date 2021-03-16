<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\MpAmazonConnector\Helper;

use Webkul\MpAmazonConnector\Api\ProductMapRepositoryInterface;

class ProductOnAmazon extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $amzClient;

    /*
    \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /*
    Data
     */
    private $helper;

    /*
    \Webkul\MpAmazonConnector\Model\ProductMap
     */
    private $productMap;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Data $helper,
        \Webkul\MpAmazonConnector\Model\ProductMap $productMap,
        \Webkul\MpAmazonConnector\Logger\Logger $logger,
        ProductMapRepositoryInterface $productMapRepo,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->productMap = $productMap;
        $this->logger = $logger;
        $this->productMapRepo = $productMapRepo;
        $this->objectManager = $objectManager;
    }

    /**
     * mange magento product to sync to amazon
     * @param  array $params
     * @return array
     */
    public function manageMageProduct($productIds)
    {
        $result = null;
        $postProductData = [];
        $exportErrorsData = [];
        $totalCount = count($productIds);
        $errorCount = 0;
        $amzCurrencyCode = $this->helper->config['currency_code'];
        $currencyRate = $this->helper->getCurrencyRate($amzCurrencyCode);

        foreach ($productIds as $productId) {
            $product = $this->productFactory->create()
                        ->load($productId);
            if ($product->getEntityId()) {
                $exportProType = $this->helper
                    ->getProductAttrValue($product, 'identification_label');
                $exportProValue = $this->helper
                    ->getProductAttrValue($product, 'identification_value');
                $mwsProduct = $this->objectManager->create('Webkul\MpAmazonConnector\Helper\MwsProduct');
                $mwsProduct->mageProductId = $product->getId();
                $mwsProduct->sku = $product->getSku();
                $actualPrice = empty($currencyRate) ? $product->getPrice() : ($product->getPrice()*$currencyRate);
                $newPrice = str_replace(',', '', number_format($actualPrice, 2));
                $mwsProduct->price = $newPrice;
                $mwsProduct->productId = $exportProValue;
                $mwsProduct->productIdType = $exportProType;
                $mwsProduct->conditionType = 'New';
                $mwsProduct->quantity = $product->getQuantityAndStockStatus()['qty'];

                if ($mwsProduct->validate()) {
                    $postProductData[] = $mwsProduct;
                } else {
                    $exportErrorsData[] = $mwsProduct->getValidationErrors();
                    $errorCount++;
                }
            } else {
                $errorCount++;
            }
        }
        $exportedProducts = $totalCount - $errorCount;

        if (!empty($postProductData)) {
            $result = $this->amzClient->postProduct($postProductData);
            $this->saveDataInTable($postProductData, $result);
        }

        empty($errorCount) ? '' : $this->logger->info('Helper ProductOnAmazon manageMageProduct : error log '.json_encode($exportErrorsData));
        
        return ['count' => $exportedProducts, 'error_count'=>$errorCount];
    }

    /**
     * save exported data in table
     *
     * @param object $submitedData
     * @param array $mwsResponse
     * @return void
     */
    public function saveDataInTable($submitedData, $mwsResponse)
    {
        if (isset($mwsResponse['FeedSubmissionId']) && $mwsResponse['FeedSubmissionId']) {
            foreach ($submitedData as $subProduct) {
                $product = $this->productFactory->create()
                            ->load($subProduct->mageProductId);
                $cats = $product->getCategoryIds();
                $firstCategoryId = null;
                if (count($cats)) {
                    $firstCategoryId = $cats[0];
                }
                $data = [
                    'magento_pro_id' => $product->getEntityId(),
                    'mage_cat_id' => $firstCategoryId,
                    'name' => $product->getName(),
                    'product_type' => $product->getTypeId(),
                    'amazon_pro_id' => '',
                    'seller_id' => $this->helper->getCustomerId(),
                    'amz_product_id' => $subProduct->productId,
                    'feedsubmission_id' => $mwsResponse['FeedSubmissionId'],
                    'export_status' =>'0',
                    'error_status' =>'0',
                    'pro_status_at_amz' =>'Pending',
                    'product_sku' => $subProduct->sku
                ];
                $record = $this->productMap;
                $record->setData($data)->save();
            }
        }
    }

    /**
     * get product quantity related data
     * @param  object $product
     * @return array
     */
    public function updateQtyData($product)
    {
        $updateQuantityArray = [
            $product->getSku() => $product->getQuantityAndStockStatus()['qty']
        ];
        return $updateQuantityArray;
    }

    /**
     * get product price related data
     * @param  object $product
     * @return array
     */
    public function updatePriceData($product)
    {
        $amzCurrencyCode = $this->helper->config['currency_code'];
        $currencyRate = $this->helper->getCurrencyRate($amzCurrencyCode);
        $price = empty($currencyRate) ? round($product->getPrice(), 2) : round(($product->getPrice()*$currencyRate), 2);
        $updatePriceArray = [
            $product->getSku() => $price
        ];
        return $updatePriceArray;
    }

    /**
     * check exported product status
     *
     * @param array $feedIds
     * @return void
     */
    public function checkProductFeedStatus($feedIds)
    {
        $productFeedStatus = [];
        $result = $this->feedSubmitionResult($feedIds);
    }

    /**
     * proccessed exported product response
     *
     * @param [type] $feed
     * @param [type] $feedResponse
     * @return void
     */
    public function processFeedResult($feed, $feedResponse)
    {
        try {
            $this->logger->info('feedresponse '.json_encode($feedResponse));
            $response = [];
            $updatedRecods = 0;
            $failedErrorCodes = ['8058','8560','8047','8105','6024'];
            foreach ($feedResponse as $feedArray) {
                $errorCode = '';
                $errorMsg = '';
                $productAsign = null;
                $productStatus = null;
                $mapProductData = $this->productMapRepo->getBySku($feedArray['product_sku']);
                if ($mapProductData->getSize()) {
                    if (in_array($feedArray['error_code'], $failedErrorCodes)) {
                        $productStatus = 'Failed';//failed
                        $errorMsg = $feedArray['error_msg']. '(error code '.$feedArray['error_code']. ')';
                    } else {
                        $amzProData = $this->amzClient->getMyPriceForSKU([$feedArray['product_sku']]);
                        if (isset($amzProData['GetMyPriceForSKUResult']['Product'])) {
                            $productStatus = 'Exported';//active
                            $amzProductForSku = $amzProData['GetMyPriceForSKUResult']['Product'];
                            $productAsign = $amzProductForSku['Product']['Identifiers']['MarketplaceASIN']['ASIN'];
                        } else {
                            $productStatus = 'Exported';//inactive
                        }
                    }
                    foreach ($mapProductData as $proData) {
                        $proData->setExportStatus('1');
                        $proData->setErrorStatus($errorMsg);
                        $proData->setProStatusAtAmz($productStatus);
                        $proData->setAmazonProId($productAsign);
                        $proData->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProdudtOnAmazon processFeedResult : '.$e->getMessage());
        }
    }

    /**
     * send feed submit request
     */
    public function feedSubmitionResult($feedIds)
    {
        try {
            $feedResponse = [];
            foreach ($feedIds as $key => $feed) {
                $feedResult = $this->amzClient->getFeedSubmissionResult($feed);
                $feedResponse = $this->convertTxtToArray($feedResult);
                $this->processFeedResult($feed, $feedResponse);
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProdudtOnAmazon feedSubmitionResult : '.$e->getMessage());
        }
        return $feedResponse;
    }

    /**
     * convert text to array
     *
     * @param string $content
     * @return array
     */
    public function convertTxtToArray($content)
    {
        try {
            $reportContent = str_replace([ "\n" , "\t" ], [ "[NEW*LINE]" , "[tAbul*Ator]" ], $content);
            $reportArr = explode("[NEW*LINE]", $reportContent);
            $i = 4;
            $exportErrors = [];
            // $reportHeadingArr = explode("[tAbul*Ator]", utf8_encode($reportArr[4]));
            for ($i =5; $i < count($reportArr); $i++) {
                $errorReport = explode("[tAbul*Ator]", utf8_encode($reportArr[$i]));
                if (isset($errorReport[1])) {
                    $exportErrors[] = [
                        'product_sku' => $errorReport[1],
                        'error_code' => $errorReport[2],
                        'error_msg' => $errorReport[4]
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProdudtOnAmazon convertTxtToArray : '.$e->getMessage());
        }
        return $exportErrors;
    }

    /**
     * check product status by sku
     *
     * @param array $amazProSku
     * @return void
     */
    public function checkProductStatusBySku($amazProSku)
    {
        try {
            $exportedAmzIds = [];
            $response = $this->amzClient->getCompetitivePricingForSKU($amazProSku);
            if (isset($response['GetCompetitivePricingForSKUResult'])) {
                foreach ($response['GetCompetitivePricingForSKUResult'] as $result) {
                    if (isset($result['Product'])) {
                        $asinData = $result['Product']['Identifiers']['MarketplaceASIN'];
                        $skuData = $result['Product']['Identifiers']['SKUIdentifier'];
                        $exportedAmzIds[$skuData['SellerSKU']] = $asinData['ASIN'];
                    }
                }
            }
            foreach ($exportedAmzIds as $sku => $asin) {
                $mapProductData = $this->productMapRepo->getBySku($sku);
                foreach ($mapProductData as $proData) {
                    $proData->setExportStatus('1');
                    $proData->setProStatusAtAmz('Exported');
                    $proData->setAmazonProId($asin);
                    $proData->save();
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProdudtOnAmazon checkProductStatusBySku : '.$e->getMessage());
        }
    }
}
