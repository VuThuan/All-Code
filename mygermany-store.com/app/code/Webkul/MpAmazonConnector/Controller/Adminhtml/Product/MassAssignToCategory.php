<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Webkul\MpAmazonConnector\Api\ProductMapRepositoryInterface;
use Webkul\MpAmazonConnector\Controller\Adminhtml\Product;

class MassAssignToCategory extends Product
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductmapRepositoryInterface
     */
    private $productMapRepository;

    /**
     * @param Context                                         $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param ProductmapRepositoryInterface                   $productMapRepository
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        ProductMapRepositoryInterface $productMapRepository,
        \Webkul\MpAmazonConnector\Helper\Data $helper
    ) {
        $this->productRepository = $productRepository;
        $this->productMapRepository = $productMapRepository;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $sellerId = $this->helper->getCustomerId($params['id']);
        $collection =  $this->productMapRepository
                        ->getByIds($params['productEntityIds'], $sellerId);
        $prodMapUpdate = 0;
        foreach ($collection as $proMap) {
            $catId = $this->getRequest()->getParam('magecate');
            $pro = $this->productRepository->getById($proMap->getMagentoProId());
            $pro->setCategoryIds([$catId]);
            $this->productRepository->save($pro, true);
            $proMap->setMageCatId($catId)->save();
            ++$prodMapUpdate;
        }
        $this->messageManager->addSuccess(
            __("A total of %1 record(s) have been updated.", $prodMapUpdate)
        );

        return $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        )->setPath(
            '*/accounts/edit',
            [
                'id'=>$params['id'],
                'active_tab' => 'product_sync'
            ]
        );
    }
}
