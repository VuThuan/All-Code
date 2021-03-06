<?php

namespace Mangoit\MpMassUploadPatch\Helper\Webkul\MpMassUpload;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory as SellerCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollection;
use Magento\Framework\Xml\Parser;
use Magento\Framework\Filesystem\Driver\File;
use Webkul\MpMassUpload\Model\ResourceModel\AttributeProfile\CollectionFactory as AttributeProfile;
use Webkul\MpMassUpload\Api\AttributeProfileRepositoryInterface;
use Webkul\MpMassUpload\Model\ResourceModel\AttributeMapping\CollectionFactory as AttributeMapping;
use Webkul\MpMassUpload\Api\AttributeMappingRepositoryInterface;
use Webkul\MpMassUpload\Api\ProfileRepositoryInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProTypeModel;
use Magento\Framework\Filesystem\Io\File as fileUpload;


class Data extends \Webkul\MpMassUpload\Helper\Data
{
    /**
     * @var Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $_entity;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_product;

    /**
     * @var \Webkul\MpMassUpload\Model\ProfileFactory
     */
    protected $_profile;

    /**
     * @var \Webkul\Marketplace\Controller\Product\SaveProduct
     */
    protected $_saveProduct;

    /**
     * @var SellerCollection
     */
    protected $_sellerCollection;

    /**
     * @var CategoryCollection
     */
    protected $_categoryCollection;

    /**
     * @var AttributeCollection
     */
    protected $_attributeCollection;

    /**
     * @var CustomerCollection
     */
    protected $_customerCollection;

    /**
     * @var AttributeSetCollection
     */
    protected $_attributeSetCollection;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_fileDriver;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $_csvReader;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploader;

    /**
     * @var \Webkul\MpMassUpload\Model\Zip
     */
    protected $_zip;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Customer\Model\Group
     */
    protected $_customerGroup;

    /**
     * @var Parser
     */
    protected $_parser;

    /**
     * @var File
     */
    protected $_file;

    /**
     * @var AttributeProfile
     */
    protected $_attributeProfile;

    /**
     * @var AttributeProfileRepositoryInterface
     */
    protected $_attributeProfileRepository;

    /**
     * @var AttributeMapping
     */
    protected $_attributeMapping;

    /**
     * @var AttributeMappingRepositoryInterface
     */
    protected $_attributeMappingRepository;

    /**
     * @var ProfileRepositoryInterface
     */
    protected $_profileRepository;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * File helper downloadable product
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $fileHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var ConfigurableProTypeModel
     */
    protected $_configurableProTypeModel;

	/**
     * @var CustomerCollection
     */
    protected $csvWriter;
    protected $scopeConfig;
    protected $_messageManager;
    protected $_logger;

	    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Eav\Model\Entity $entity
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\MpMassUpload\Model\ProfileFactory $profile
     * @param \Webkul\Marketplace\Controller\Product\SaveProduct $saveProduct
     * @param SellerCollection $sellerCollectionFactory
     * @param CategoryCollection $categoryCollectionFactory
     * @param AttributeCollection $attributeCollectionFactory
     * @param CustomerCollection $customerCollectionFactory
     * @param AttributeSetCollection $attributeSetCollectionFactory
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\File\Csv $csvReader
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Webkul\MpMassUpload\Model\Zip $zip
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Customer\Model\Group $customerGroup
     * @param Parser $parser
     * @param File $file
     * @param AttributeProfile $attributeProfile
     * @param AttributeProfileRepositoryInterface $attributeProfileRepository
     * @param AttributeMapping $attributeMapping
     * @param AttributeMappingRepositoryInterface $attributeMappingRepository
     * @param ProfileRepositoryInterface $profileRepository
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Downloadable\Helper\File $fileHelper
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Eav\Model\Entity $entity,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\MpMassUpload\Model\ProfileFactory $profile,
        \Webkul\Marketplace\Controller\Product\SaveProduct $saveProduct,
        SellerCollection $sellerCollectionFactory,
        CategoryCollection $categoryCollectionFactory,
        AttributeCollection $attributeCollectionFactory,
        CustomerCollection $customerCollectionFactory,
        AttributeSetCollection $attributeSetCollectionFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\File\Csv $csvReader,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Webkul\MpMassUpload\Model\Zip $zip,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\Group $customerGroup,
        Parser $parser,
        File $file,
        AttributeProfile $attributeProfile,
        AttributeProfileRepositoryInterface $attributeProfileRepository,
        AttributeMapping $attributeMapping,
        AttributeMappingRepositoryInterface $attributeMappingRepository,
        ProfileRepositoryInterface $profileRepository,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Downloadable\Helper\File $fileHelper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        ConfigurableProTypeModel $configurableProTypeModel,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        fileUpload $fileUpload,
        DirectoryList $directoryList,
        \Magento\Framework\File\Csv $csvWriter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->csvWriter = $csvWriter;
    	$this->scopeConfig = $scopeConfig;
        $this->_messageManager = $messageManager;
        $this->_objectManager = $this->getObjectManager();
        $this->_logger = $this->setLogger();
        parent::__construct($context, $storeManager, $customerSession, $filesystem, $entity, $config, $formKey, $productFactory, $profile, $saveProduct, $sellerCollectionFactory, $categoryCollectionFactory, $attributeCollectionFactory, $customerCollectionFactory, $attributeSetCollectionFactory, $fileDriver, $csvReader, $fileUploaderFactory, $zip, $objectManager, $resource, $customerGroup, $parser, $file, $attributeProfile, $attributeProfileRepository, $attributeMapping, $attributeMappingRepository, $profileRepository, $jsonHelper, $fileHelper, $marketplaceHelper, $configurableProTypeModel, $timezoneInterface, $fileUpload, $directoryList);
        /*parent::__construct(
    		$context,
			$storeManager,
			$customerSession,
			$filesystem,
			$entity,
			$config,
			$formKey,
			$productFactory,
			$profile,
			$saveProduct,
			$sellerCollectionFactory,
			$categoryCollectionFactory,
			$attributeCollectionFactory,
			$customerCollectionFactory,
			$attributeSetCollectionFactory,
			$fileDriver,
			$csvReader,
			$fileUploaderFactory,
			$zip,
			$objectManager,
			$resource,
			$customerGroup,
			$parser,
			$file,
			$attributeProfile,
			$attributeProfileRepository,
			$attributeMapping,
			$attributeMappingRepository,
			$profileRepository,
			$jsonHelper,
			$fileHelper,
            $resultPageFactory,
			$marketplaceHelper
    	);*/
    }

    public function getObjectManager()
    {
        //Get Object Manager Instance
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }

    public function setLogger()
    {        
        return $this->_objectManager->create('\Psr\Log\LoggerInterface');
    }

	/**
     * Validate Uploaded Files
     *
     * @return array
     */
    public function validateUploadedFiles($noValidate)
    {
        $this->_logger->info("######## MpMassUpload Logger Start #########");
        $validateCsv = $this->validateCsv();
        if ($validateCsv['error']) {
            $this->_logger->info("######## validateCsv[error] #########");
            return $validateCsv;
        }
        $csvFile = $validateCsv['csv'];
        $validateZip = $this->validateZip();
        if ($validateZip['error']) {
            $this->_logger->info("######## validateZip[error] #########");
            return $validateZip;
        }
        // Start: Calculate Profile Mapped Attribute Data Array
        // for coverting uploaded file data attributes into magento attributes
        $atrrProfileId = $this->_request->getParam('attribute_profile_id');
        $attributeMappedData = $this->_attributeMappingRepository
            ->getByProfileId($atrrProfileId);
        $attributeMappedArr = [];
        foreach ($attributeMappedData as $key => $value) {
            if ($value['mage_attribute'] == 'image') {
                $attributeMappedArr[$value['file_attribute']] = 'images';
            } else if ($value['mage_attribute'] == 'category_ids') {
                $attributeMappedArr[$value['file_attribute']] = 'category';
            } else {
                $attributeMappedArr[$value['file_attribute']] = $value['mage_attribute'];
            }
        }
        // End: Calculate Profile Mapped Attribute Data Array
        $csvFilePath = $validateCsv['path'];
        if ($validateCsv['extension'] == 'csv') {
            $uploadedFileRowData = $this->readCsv($csvFilePath, $attributeMappedArr);
            $csvData = $this->csv_to_array($csvFilePath);
            // if (($handle = fopen($csvFilePath, "r")) !== false) {
            //     $filesize = filesize($csvFilePath);
            //     $firstRow = true;
            //     $aData = array();
            //     while (($data = fgetcsv($handle, $filesize, ";")) !== false) {
            //         if($firstRow) {
            //             $aData = $data;
            //             $firstRow = false;
            //         } else {
            //             for($i = 0;$i < count($data); $i++) {
            //                 $aData[$i][] = $data[$i];
            //             }
            //         }
            //     }
            //     fclose($handle);
            // }
            // echo "<pre>";
            // print_r($csvData);
            // print_r($csvData['product_cat_type']);
            // die('died 319');
        } else if ($validateCsv['extension'] == 'xml') {
            $uploadedFileRowData = $this->_parser->load($csvFilePath)->xmlToArray();
            $dataKeyProductArray = [];
            $dataValueArray = [];
            $count = count($uploadedFileRowData);
            $flag = 1;
            foreach ($uploadedFileRowData['node']['product'] as $key => $value) {
                if (is_array($value) && is_numeric($key)) {
                    $flag = 0;
                    $dataValueProductArray = [];
                    foreach ($value as $productkey => $productValue) {
                        // Start: Coverting uploaded file data attributes into magento attributes
                        if (!empty($attributeMappedArr[$productkey])) {
                            $productkey = $attributeMappedArr[$productkey];
                        }
                        // End: Coverting uploaded file data attributes into magento attributes
                        $dataKeyProductArray[$productkey] = $productkey;
                        $dataValueProductArray[$productkey] = $productValue;
                    }
                    $dataValueArray[] = $dataValueProductArray;
                } else {
                    $dataKeyProductArray[$key] = $key;
                    $dataValueArray[] = $value;
                }
            }
            $i = 0;
            $dataKeyArray = [];
            foreach ($dataKeyProductArray as $key => $value) {
                $dataKeyArray[$i] = $value;
                if (!$flag) {
                    foreach ($dataValueArray as $productkey => $productvalue) {
                        if (empty($dataValueArray[$productkey][$value])) {
                            $dataValueArray[$productkey][$i] = '';
                            unset($dataValueArray[$productkey][$value]);
                        } else {
                            $dataValueArray[$productkey][$i] = $dataValueArray[$productkey][$value];
                            unset($dataValueArray[$productkey][$value]);
                        }
                    }
                }
                $i++;
            }
            $data[0] = $dataKeyArray;
            if (!$flag) {
                $i = 1;
                foreach ($dataValueArray as $key => $value) {
                    $data[$i] = $value;
                    $i++;
                }
            } else {
                $data[1] = $dataValueArray;
            }
            $uploadedFileRowData = $data;
        } else if ($validateCsv['extension'] == 'json') {

        	try {
	            $jsonContent = @file_get_contents($csvFilePath);
	            if($jsonContent != '') {

		            // echo "<pre>";
		            $jsonAry = json_decode($jsonContent, true);
		            if(count($jsonAry)) {
			            $uploadedFileRowData[0] = array_keys(reset($jsonAry));
			            // print_r($uploadedFileRowData);
			            // print_r($uploadedFileRowData);
			            // print_r($uploadedFileRowData);
			            // die;
			            // $data = [];
			            // $data[0] = array_keys(reset($jsonAry));
			            $i = 1;
			            foreach ($jsonAry as $value) {
			            	$uploadedFileRowData[$i] = array_values($value);
			            	$i++;
			            }
		            	// $uploadedFileRowData[1] = $data;

			            $csvJSONFilePath = $this->_filesystem->getDirectoryWrite(
			                DirectoryList::MEDIA
			            )->getAbsolutePath('/xlscoverted').$csvFile.'.csv';

	                    $this->csvWriter
				            ->setEnclosure('"')
				            ->setDelimiter(',')
				            ->saveData($csvJSONFilePath ,$uploadedFileRowData);
				        $uploadedFileRowData = $this->readCsv($csvJSONFilePath, $attributeMappedArr);
		            }
		            // print_r();
		            // print_r($uploadedFileRowData);
		            // die('asd');

		            // $configDataRow = $productsRow;
              		// $fileContents = $fileName = $fileTyp = '';
                    // if($data['export_type'] == "CSV") {
	                   //  $writer = $this->_writer;
	                   //  $writer->setHeaderCols($configDataRow[0]);
	                   //  foreach ($configDataRow[1] as $dataRow) {
	                   //      if (!empty($dataRow)) {
	                   //          $writer->writeRow($dataRow);
	                   //      }
	                   //  }
	                   //  $fileTyp = 'text/csv';
	                   //  $fileName = $productType.'_product.csv';
	                   //  $fileContents = $writer->getContents();
                    // }         
	            }
            } catch (\Exception $e) {
	            $msg = 'There is some problem with the JSON file.';
	            $result = ['error' => true, 'msg' => $msg];
	            return $result;
	        }
        } else {
            $inputFileType = 'Excel5';

            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcelReader = $objReader->load($csvFilePath);

            $loadedSheetNames = $objPHPExcelReader->getSheetNames();

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcelReader, 'CSV');

            $csvXLSFilePath = $this->_filesystem->getDirectoryWrite(
                DirectoryList::MEDIA
            )->getAbsolutePath('/xlscoverted').$csvFile.'.csv';
            foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {
                $objWriter->setSheetIndex($sheetIndex);
                $objWriter->save($csvXLSFilePath);
            }
            $uploadedFileRowData = $this->readCsv($csvXLSFilePath, $attributeMappedArr);
        }

        $swichedVariable = $uploadedFileRowData;
        $headers = $uploadedFileRowData[0];
        $catIndex = array_search('product_cat_type', $headers);
        $deliveryFIndex = array_search('delivery_days_from', $headers);
        $deliveryToIndex = array_search('delivery_days_to', $headers);
        $imagesIndex = array_search('images', $headers);
        $electronicTypeVal = $this->scopeConfig->getValue('marketplace/product_cat_type/electronics_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $nonElectronicTypeVal = $this->scopeConfig->getValue('marketplace/product_cat_type/non_electronics_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        foreach ($uploadedFileRowData as $key => $value) {
            if ($key != 0) {
                if ($value[$catIndex] == 'Non-Electronics') {
                    $value[$catIndex] = $nonElectronicTypeVal;
                } elseif($value[$catIndex] == 'Electronics') {
                    $value[$catIndex] = $electronicTypeVal;
                } else {
                    $result = ['error' => true, 'msg' => 'Check product cat type value, it should be either Electronics or Non-Electronics'];
                    return $result;
                }
                $deliveryDateFrom = $value[$deliveryFIndex];
                $deliveryTo = $value[$deliveryToIndex];
                if (preg_match("/[\/!@#$%^&*(),.?:{}|<>]/", $deliveryDateFrom)) {
                    $result = ['error' => true, 'msg' => 'Delivery date from must be integer number'];
                    return $result;
                }
                if (preg_match("/[\/!@#$%^&*(),.?:{}|<>]/", $deliveryTo)) {
                    $result = ['error' => true, 'msg' => 'Delivery date to must be integer number'];
                    return $result;
                }

                if ($deliveryTo <= $deliveryDateFrom) {
                    $result = ['error' => true, 'msg' => 'Delivery days from must be less than delivery days to!'];
                    return $result;
                }
                if ($deliveryTo > 30 && ($deliveryDateFrom > 29)) {
                    $result = ['error' => true, 'msg' => 'Delivery days should be less than 30!'];
                    return $result;
                }

                if(is_int($deliveryDateFrom) && (strpos($deliveryDateFrom, '-') !== false) && (strpos($deliveryTo, '-') !== false)) {
                    $msg = 'There is some problem with the your file. Please recheck values of your file!';
                    $result = ['error' => true, 'msg' => $msg];
                    return $result;
                }

                if ($value[$imagesIndex] == '') {
                    $result['error'] = 1;
                    $result['msg'] = __('Skipped row %1. product image can not be empty.', $key);
                    return $result;
                }
            }
        }
        $validateCsvData = $this->validateCsvData($uploadedFileRowData);
        if ($validateCsvData['error']) {
            return $validateCsvData;
        }
        $productType = $validateCsvData['type'];
        $isDownloadableAllowed = $this->isProductTypeAllowed('downloadable');
        if ($productType == 'downloadable' && $isDownloadableAllowed) {
            $validateLinkFiles = $this->validateLinkFiles();
            if ($validateLinkFiles['error']) {
                return $validateLinkFiles;
            }
            if ($this->_request->getParam('is_link_samples')) {
                $validateLinkSampleFiles = $this->validateLinkSampleFiles();
                if ($validateLinkSampleFiles['error']) {
                    return $validateLinkSampleFiles;
                }
            }
            if ($this->_request->getParam('is_samples')) {
                $validateSampleFiles = $this->validateSampleFiles();
                if ($validateSampleFiles['error']) {
                    return $validateSampleFiles;
                }
            }
        }
        $result = [
            'error' => false,
            'type' => $productType,
            'csv' => $csvFile,
            'csv_data' => $uploadedFileRowData,
            'extension' => $validateCsv['extension']
        ];
        return $result;
    }

    /**
     * Save Product
     *
     * @param int $sellerId
     * @param int $row
     * @param array $wholeData
     *
     * @return array
     */
    public function saveProduct($sellerId, $row, $wholeData) {
        $result = ['error' => 0, 'config_error' => 0, 'msg' => ''];
        try {
            /* Check if authorized seller */
            if (!empty($wholeData['id']) && empty($wholeData['error'])) {
                $productId = $wholeData['id'];
                $helper = $this->marketplaceHelper;
                $rightseller = $helper->isRightSeller($productId);
                if (!$rightseller) {
                    $wholeData['msg'] = __(
                        'Skipped row %1. You are not authorize to add this product.',
                        $row
                    );
                    $wholeData['error'] = 1;
                }
            }
            $uploadedPro = $wholeData['row']-1;
            /*Set Product Add Status According to seller Group*/
            if ($this->isSellerGroupEnable() && !$this->checkProductAllowedStatus($uploadedPro)) {
                $result['error'] = 2;
                if ($this->getAllowedProductQty()) {
                    $result['msg'] = __('You are not allowed to add more than %1 Product(s)', $this->getAllowedProductQty());
                } else {
                    $result['msg'] = __('YOUR GROUP PACK IS EXPIRED...');
                }
                return $result;
            } else {
                if (!empty($wholeData['error'])) {
                    $result['error'] = $wholeData['error'];
                    $result['msg'] = $wholeData['msg'];
                } else {
                    $result = $this->_saveProduct->saveProductData($sellerId, $wholeData);
                    $isInStock = 1;
                    if (!(int)$wholeData['product']['quantity_and_stock_status']['qty']) {
                        $isInStock = 0;
                    }
                    $result['is_in_stock'] = $isInStock;
                }
            }
        } catch (\Exception $e) {
            $result['msg'] = __('Skipped row %1. %2', $row, $e->getMessage());
            $result['error'] = 1;
        }
        $result['total_row_count'] = $wholeData['total_row_count'];
        $result['row'] = $row;
        if ($wholeData['total_row_count'] != $row) {
            $nextRow = $row+1;
            $result['next_row_data'] = $this->calculateProductRowData(
                $sellerId, $wholeData['profile_id'], $nextRow, $wholeData['type']
            );
            $result['next_row_data']['profile_id'] = $wholeData['profile_id'];
            $result['next_row_data']['row'] = $nextRow;
            $result['next_row_data']['total_row_count'] = $wholeData['total_row_count'];
            $result['next_row_data']['seller_id'] = $sellerId;
        }
        if ($result['error'] == 1) {
            if (!empty($result['message'])) {
                $result['msg'] = $result['message'];
            }
            return $result;
        } else {
            if (empty($result['product_id'])) {
                $result['product_id'] = 0;
            }
            $productId = (int) $result['product_id'];
        }
        if ($productId == 0) {
            $result['error'] = 1;
            $result['msg'] = __('Skipped row %1. error in importing product.', $row);
        }

        return $result;
    }

        /**
     * Process Configurable Data
     *
     * @param array $wholeData
     * @param array $data
     * @param int $profileId
     *
     * @return array $wholeData
     */
    public function processConfigurableData($wholeData, $data, $row, $childRowArr, $uploadedFileRowData, $profileId)
    {
        try {
            $attributeCodes = $data['product']['_super_attribute_code'];
            $error = 0;
            $attributeData = $this->processAttributeData($attributeCodes);
            $attributes = $attributeData['attributes'];
            // print_r($attributes);
            // die;
            if(isset($attributes[0]) && !count($attributes[0])) {
                $this->_messageManager->addError("Please check values in '_super_attribute_code', Values entered are incorrect.");
                return $this->resultRedirectFactory->create()->setPath('*/*/view');
            }
            $flag = $attributeData['flag'];
            if ($flag == 1) {
                $msg = __('Skipped row %1. Some of super attributes are not valid.', $row);
                $validate['msg'] = $msg;
                $validate['error'] = 1;
                if ($validate['error']) {
                    $wholeData['error'] = $validate['error'];
                    $wholeData['msg'] = $validate['msg'];
                }
            }

            foreach ($attributes as $attribute) {
                $attributeId = $attribute['attribute_id'];
                $wholeData['attributes'][] = $attributeId;
            }
            $attributeOptions = [];
            foreach ($childRowArr as $key => $childRow) {
                // Prepare Associated product row data
                $i=0;
                $j=0;
                $childRowData = $uploadedFileRowData[$childRow];
                $customData = [];
                foreach ($uploadedFileRowData[0] as $value) {
                    $key = $i++;
                    if (empty($childRowData[$key])) {
                        $customData['product'][$value] = '';
                    } else {
                        $customData['product'][$value] = $childRowData[$key];
                    }
                    if ($value == 'description' && empty($customData['product'][$value])) {
                        $customData['product'][$value] = $wholeData['product']['description'];
                    }
                }
                if (!empty($customData['product']['stock'])) {
                    $customData['product']['stock'] = $customData['product']['stock'];
                } else {
                    $customData['product']['stock'] = $data['product']['stock'];
                }
                $childRowData = $customData;
                $childRowData = $this->prepareAssociatedProductIfNotSet(
                    $childRowData,
                    $data
                );
                $superAttributeOptions = $this->getArrayFromString($childRowData['product']['_super_attribute_option']);
                $arributeCodeIndex = 0;
                foreach ($attributes as $attribute) {
                    if (!empty($superAttributeOptions[$arributeCodeIndex])) {
                        $attributeId = $attribute['attribute_id'];
                        $attributeOptions[$attributeId][] = $superAttributeOptions[$arributeCodeIndex];
                        $arributeCodeIndex++;
                    }
                }
                $wholeData['product']['configurable_attributes_data'] = [];
                $pos = 0;
                $allAttributeOptionsIdsArr = [];
                foreach ($attributes as $attribute) {
                    $attributeId = $attribute['attribute_id'];
                    $code = $attribute['attribute_code'];
                    $wholeData['product']['configurable_attributes_data'][$attributeId]['attribute_id'] = $attributeId;
                    $wholeData['product']['configurable_attributes_data'][$attributeId]['code'] = $code;
                    $wholeData['product']['configurable_attributes_data'][$attributeId]['label'] = $attribute['frontend_label'];
                    $wholeData['product']['configurable_attributes_data'][$attributeId]['position'] = $pos;
                    $wholeData['product']['configurable_attributes_data'][$attributeId]['values'] = [];
                    if (empty($attributeOptions[$attributeId])) {
                        $attributeOptions[$attributeId] = [];
                    }
                    foreach ($attributeOptions[$attributeId] as $key => $option) {
                        $attributeOptionsId = '';
                        $attributeOptionsByCode = $this->getAttributeOptions($code);
                        if (!in_array($option, $attributeOptionsByCode)) {
                            $result = [
                                'msg' => __('Skipped row %1. Super attribute value is not valid.', $row),
                                'error' => 1
                            ];
                            $wholeData['error'] = $result['error'];
                            $wholeData['msg'] = $result['msg'];
                        } else {
                            $attributeOptionsId = array_search($option, $attributeOptionsByCode);
                            $allAttributeOptionsIdsArr[$option]['id'] = $attributeOptionsId;
                            $allAttributeOptionsIdsArr[$option]['code'] = $code;
                        }
                        $wholeData['product']['configurable_attributes_data'][$attributeId]['values'][$attributeOptionsId]['include'] = 1;
                        $wholeData['product']['configurable_attributes_data'][$attributeId]['values'][$attributeOptionsId]['value_index'] = $attributeOptionsId;
                    }
                    $pos++;
                }

                // prepare variation matrix
                $variationMatrixArr = [];
                $variationMatrixConfAttribute = [];
                foreach ($superAttributeOptions as $key => $value) {
                    if (!empty($allAttributeOptionsIdsArr[$value])) {
                        $optionAttrCode = $allAttributeOptionsIdsArr[$value]['code'];
                        $optionId = $allAttributeOptionsIdsArr[$value]['id'];
                        array_push($variationMatrixArr, $optionId);
                        $variationMatrixConfAttribute[$optionAttrCode] = $optionId;
                    }
                }
                $variationMatrixIndex = implode('-', $variationMatrixArr);
                $configurableAttribute = $this->_jsonHelper->jsonEncode($variationMatrixConfAttribute);

                $assoImageData = $this->processImageData($childRowData, $childRowData, $profileId);

                $wholeData['variations-matrix'][$variationMatrixIndex]['image'] = '';

                if (!empty($assoImageData['product']['image'])) {
                    $wholeData['variations-matrix'][$variationMatrixIndex]['image'] = $assoImageData['product']['image'];
                    $wholeData['variations-matrix'][$variationMatrixIndex]['small_image'] = $assoImageData['product']['small_image'];
                    $wholeData['variations-matrix'][$variationMatrixIndex]['thumbnail'] = $assoImageData['product']['thumbnail'];
                    $wholeData['variations-matrix'][$variationMatrixIndex]['media_gallery'] = $assoImageData['product']['media_gallery'];
                }

                $wholeData['variations-matrix'][$variationMatrixIndex]['name'] = $childRowData['product']['name'];
                $wholeData['variations-matrix'][$variationMatrixIndex]['configurable_attribute'] = $configurableAttribute;
                $wholeData['variations-matrix'][$variationMatrixIndex]['status'] = 1;
                if (empty($childRowData['product']['sku'])) {
                    $childRowData['product']['sku'] = $wholeData['product']['sku'].'-'.implode('-', $superAttributeOptions);
                }
                $wholeData['variations-matrix'][$variationMatrixIndex]['sku'] = $childRowData['product']['sku'];
                $wholeData['variations-matrix'][$variationMatrixIndex]['sku'] = $childRowData['product']['sku'];
                $wholeData['variations-matrix'][$variationMatrixIndex]['price'] = $childRowData['product']['price'];
                $wholeData['variations-matrix'][$variationMatrixIndex]['quantity_and_stock_status']['qty'] = $childRowData['product']['stock'];
                $wholeData['variations-matrix'][$variationMatrixIndex]['quantity_and_stock_status']['qty'] = $childRowData['product']['stock'];
                $wholeData['variations-matrix'][$variationMatrixIndex]['weight'][] = $childRowData['product']['weight'];
            }
            $wholeData['affect_configurable_product_attributes'] = 1;
            return $wholeData;
        } catch (\Exception $e) {
            $this->_messageManager->addError("Something went wrong. Please check the data.");
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        }
    }

    /**
     * Upload Sample Files
     *
     * @param int $profileId
     * @param array $fileData
     * @param string $filePath
     * @param string $fileType
     *
     * @return array
     */
    public function copyFilesToDestinationFolder($profileId, $fileData, $filePath, $fileType)
    {
        $totalRows = $this->getCount($fileData);
        $skuIndex = '';
        $fileIndex = '';
        foreach ($fileData[0] as $key => $value) {
            if ($value == 'sku') {
                $skuIndex = $key;
            }
            if ($value == $fileType) {
                $fileIndex = $key;
            }
        }
        $fileTempPath = $filePath.'tempfiles/';
        for ($i=1; $i < $totalRows ; $i++) {
            if (!empty($fileData[$i][$skuIndex]) && !empty($fileData[$i][$fileIndex])) {
                $sku = $fileData[$i][$skuIndex];
                $destinationPath = $filePath.$sku;
                $isDestinationExist = 0;
                $files = explode(',', $fileData[$i][$fileIndex]);
                foreach ($files as $file) {
                    if (!empty(trim($file))) {
                        $sourcefilePath = $fileTempPath.$file;
                        if ($this->_fileDriver->isExists($sourcefilePath)) {
                            if ($isDestinationExist == 0) {
                                /* Create per product file folder if not exist */
                                if (!$this->_fileDriver->isExists($destinationPath)) {
                                    $this->_file->createDirectory($destinationPath);
                                    $isDestinationExist = 1;
                                }
                            }
                            $this->_file->copy($sourcefilePath, $destinationPath.'/'.$file);
                            /*$optimizerChain = OptimizerChain::create();
                            $optimizerChain->optimize($destinationPath.'/'.$file);*/
                        } else {
                            $error = __('Image %1 is not available in uploaded zip file.');
                            throw new \Magento\Framework\Validator\Exception(__($error, $file));
                        }
                    }
                }
            }
        }
        $this->_file->deleteDirectory($fileTempPath);
    }

    /**
     * Validate uploaded Csv File
     *
     * @return array
     */
    public function validateCsv()
    {
        try {
            $csvUploader = $this->_fileUploader->create(['fileId' => 'massupload_csv']);
            $csvUploader->setAllowedExtensions(['csv', 'xml', 'xls', 'json']);
            $validateData = $csvUploader->validateFile();
            $extension = $csvUploader->getFileExtension();
            $csvFilePath = $validateData['tmp_name'];
            $csvFile = $validateData['name'];
            $csvFile = $this->getValidName($csvFile);
            $result = [
                'error' => false,
                'path' => $csvFilePath,
                'csv' => $csvFile,
                'extension' => $extension
            ];
        } catch (\Exception $e) {
            $msg = 'There is some problem in uploading file.';
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Validate uploaded Images Zip File
     *
     * @return array
     */
    public function validateZip()
    {
        $this->_logger->info("####### Validating ZIP File #######");
        try {
            $this->_logger->info("####### Validating ZIP File #1 #######");
            $imageUploader = $this->_fileUploader->create(['fileId' => 'massupload_image']);
            $this->_logger->info("####### Validating ZIP File #2 #######");
            $imageUploader->setAllowedExtensions(['zip']);
            $this->_logger->info("####### Validating ZIP File #3 #######");
            $validateData = $imageUploader->validateFile();
            $this->_logger->info("####### Validating ZIP File #4 #######");
            $zipFilePath = $validateData['tmp_name'];
            $this->_logger->info("####### Validating ZIP File #5 #######");
            $allowedImages = ['png', 'jpg', 'jpeg', 'gif'];
            $this->_logger->info("####### Validating ZIP File #6 #######");
            $zip = zip_open($zipFilePath);
            $this->_logger->info("####### Validating ZIP File #7 #######");
            if ($zip) {
                $this->_logger->info("####### Validating ZIP File #8 #######");
                while ($zipEntry = zip_read($zip)) {
                    $this->_logger->info("####### Validating ZIP File #9 #######");
                    $fileName = zip_entry_name($zipEntry);
                    $this->_logger->info("####### Validating ZIP File #10 #######");
                    if (strpos($fileName, '.') !== false) {
                        $this->_logger->info("####### Validating ZIP File #11 #######");
                        $ext = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
                        $this->_logger->info("####### Validating ZIP File #12 #######");
                        if (!in_array($ext, $allowedImages)) {
                            $this->_logger->info("####### Validating ZIP File #13 #######");
                            $msg = 'There are some files in zip which are not image.';
                            $result = ['error' => true, 'msg' => $msg];
                            return $result;
                        }
                    }
                }
                zip_close($zip);
                $this->_logger->info("####### Validating ZIP File #14 #######");
            }
            $result = ['error' => false];
            $this->_logger->info("####### Validating ZIP File #15 #######");
        } catch (\Exception $e) {
            $this->_logger->info(" Exception : ".$e->getMessage());
            $this->_logger->info("####### Validating ZIP File #16 #######");
            $msg = __('There is some problem in uploading image zip file.');
            $this->_logger->info("####### Validating ZIP File #17 #######");
            $result = ['error' => true, 'msg' => $msg];
            $this->_logger->info("####### Validating ZIP File #18 #######");
        }
        return $result;
    }


    /**
     * Upload Csv File
     *
     * @param array $result
     * @param string $extension
     * @param string $csvFile
     *
     * @return array
     */
    public function uploadCsv($result, $extension, $csvFile)
    {
        $profileId = $result['id'];
        try {
            $csvUploadPath = $this->getBasePath($profileId);
            if ($extension == 'xls' || $extension == 'json') {
                $data = $this->_file->createDirectory($csvUploadPath);
                $sourcePath = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('/xlscoverted').$csvFile.'.csv';
                $this->_file->copy($sourcePath, $csvUploadPath.'/'.$result['name']);
                $this->_file->deleteFile($sourcePath);
            } else {
                $csvUploader = $this->_fileUploader->create(['fileId' => 'massupload_csv']);
                $extension = $csvUploader->getFileExtension();
                $csvUploader->setAllowedExtensions(['csv', 'xml', 'xls', 'json']);
                $csvUploader->setAllowRenameFiles(true);
                $csvUploader->setFilesDispersion(false);
                $csvUploader->save($csvUploadPath, $result['name']);
            }
            $result = ['error' => false];
        } catch (\Exception $e) {
            $this->flushData($profileId);
            $msg = 'There is some problem in uploading csv file.'.$e->getMessage();
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Upload Images Zip File
     *
     * @param array $result
     * @param array $fileData
     *
     * @return array
     */
    public function uploadZip($result, $fileData)
    {
        $this->_logger->info("###### uploadZip function start #######");
        $profileId = $result['id'];
        $this->_logger->info("###### profileId: ".$profileId." #######");
        try {
            $this->_logger->info("###### uploadZip #1 #######");
            $zipModel = $this->_zip;
            $this->_logger->info("###### uploadZip #2 #######");
            $basePath = $this->getBasePath($profileId);
            $this->_logger->info("###### uploadZip #3 #######");
            $imageUploadPath = $basePath.'zip/';
            $this->_logger->info("###### uploadZip #4 #######");
            $imageUploader = $this->_fileUploader->create(['fileId' => 'massupload_image']);
            $this->_logger->info("###### uploadZip #5 #######");
            $validateData = $imageUploader->validateFile();
            $this->_logger->info("###### uploadZip #6 #######");
            $imageUploader->setAllowedExtensions(['zip']);
            $this->_logger->info("###### uploadZip #7 #######");
            $imageUploader->setAllowRenameFiles(true);
            $this->_logger->info("###### uploadZip #8 #######");
            $imageUploader->setFilesDispersion(false);
            $this->_logger->info("###### uploadZip #9 #######");
            $imageUploader->save($imageUploadPath);
            $this->_logger->info("###### uploadZip #10 #######");
            $fileName = $imageUploader->getUploadedFileName();
            $this->_logger->info("###### uploadZip #11 #######");
            $source = $imageUploadPath.$fileName;
            $this->_logger->info("###### uploadZip #12 #######");
            $filePath = $this->getMediaPath().'tmp/catalog/product/'.$profileId.'/';
            $this->_logger->info("###### uploadZip #13 #######");
            $destination =  $filePath.'tempfiles/';
            $this->_logger->info("###### uploadZip #14 #######");
            $zipModel->unzipImages($source, $destination);
            $this->_logger->info("###### uploadZip #15 #######");
            $this->arrangeFiles($destination);
            $this->_logger->info("###### uploadZip #16 #######");
            $this->flushFilesCache($destination);
            $this->_logger->info("###### uploadZip #17 #######");
            $this->copyFilesToDestinationFolder($profileId, $fileData, $filePath, 'images');
            $this->_logger->info("###### uploadZip #18 #######");
            $result = ['error' => false];
            $this->_logger->info("###### uploadZip #19 #######");
        } catch (\Exception $e) {
            $this->flushData($profileId);
            $this->_logger->info("###### Problem in zip file #######");
            $this->_logger->info("###### ".$e->getMessage()." #######");
            $msg = 'There is some problem in uploading image zip file. '.$e->getMessage();
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * calculate Product Row Data
     *
     * @param int $sellerId
     * @param int $profileId
     * @param int $row
     * @param string $profileType
     *
     * @return array
     */
    public function calculateProductRowData($sellerId, $profileId, $row, $profileType) {
        $uploadedFileRowData = $this->getUploadedFileRowData($profileId);
        $mainRow = $row;
        $isConfigurableAllowed = $this->isProductTypeAllowed('configurable');
        if ($profileType == 'configurable' && $isConfigurableAllowed) {
            $rowIndexArr = $this->getConfigurableFormatCsv($uploadedFileRowData, 1);
            if (!empty($rowIndexArr[$row])) {
                $row = $rowIndexArr[$row];
            }
            $childRowIndexArr = $this->getConfigurableFormatCsv($uploadedFileRowData, 0);
            if (!empty($childRowIndexArr[$mainRow])) {
                $childRowArr = $childRowIndexArr[$mainRow];
            } else {
                $childRowArr = [];
            }
        }
        if (!array_key_exists($row, $uploadedFileRowData)) {
            $wholeData['error'] = 1;
            $wholeData['msg'] = __('Product data for row %1 does not exist', $mainRow);
        }
        // Prepare product row data
        $i=0;
        $j=0;
        $data = [];
        if (!empty($uploadedFileRowData[$row])) {
            $data = $uploadedFileRowData[$row];
        }
        $customData = [];
        $customData['product'] = [];
        foreach ($uploadedFileRowData[0] as $value) {
            if (!empty($data[$i])) {
                $customData['product'][$value] = $data[$i];
            } else {
                $customData['product'][$value] = '';
            }
            $i++;
        }
        $data = $customData;
        $validate = $this->validateFields(
            $data,
            $profileType,
            $mainRow
        );
        if ($validate['error']) {
            $wholeData['error'] = $validate['error'];
            $wholeData['msg'] = $validate['msg'];
        }
        $data = $validate['data'];
        /*Calculate product weight*/
        $hasWeight = 1;
        $isDownloadableAllowed = $this->isProductTypeAllowed('downloadable');
        $isVirtualAllowed = $this->isProductTypeAllowed('virtual');
        if (($profileType == 'virtual' && $isVirtualAllowed) || ($profileType == 'downloadable' && $isDownloadableAllowed)) {
            $weight = 0;
            $hasWeight = 0;
        } else {
            $weight = $data['product']['weight'];
        }
        /*Get Category ids by category name (set by comma seperated)*/
        $categoryIds = $this->getCategoryIds($data['product']['category']);
        /*Get $taxClassId by tax*/
        $taxClassId = $this->getAttributeOptionIdbyOptionText(
            "tax_class_id",
            trim($data['product']['tax_class_id'])
        );
        $isInStock = 1;
        if (!empty($data['product']['stock']) && !(int)$data['product']['stock']) {
            $isInStock = 0;
        } else if (empty($data['product']['stock'])) {
            $data['product']['stock'] = '';
        }
        $attributeSetId = $this->getAttributeSetId($profileId);
        $wholeData['form_key'] = $this->getFormKey();
        $wholeData['type'] = $profileType;
        $wholeData['set'] = $attributeSetId;
        if (!empty($data['id'])) {
            $wholeData['id'] = $data['id'];
            $wholeData['product_id'] = $data['product_id'];
            $wholeData['product']['website_ids'] = $data['product']['website_ids'];
            $wholeData['product']['url_key'] = $data['product']['url_key'];
        }
        $wholeData['product']['category_ids'] = $categoryIds;
        $wholeData['product']['name'] = trim($data['product']['name']);
        $wholeData['product']['short_description'] = $data['product']['short_description'];
        $wholeData['product']['description'] = $data['product']['description'];
        /* New SKU Code */
        $sku = preg_replace('/[^A-Za-z0-9\-]/',' ', $data['product']['sku']);
        $wholeData['product']['sku'] = strtolower(str_replace(" ","-", trim($sku)));
        /*$wholeData['product']['sku'] = trim($data['product']['sku']);*/
        /* New SKU Code */
        $wholeData['product']['price'] = $data['product']['price'];
        $wholeData['product']['visibility'] = 4;
        $wholeData['product']['tax_class_id'] = $taxClassId;
        $wholeData['product']['product_has_weight'] = $hasWeight;
        $wholeData['product']['weight'] = $weight;
        $wholeData['product']['stock_data']['manage_stock'] = 1;
        $wholeData['product']['stock_data']['use_config_manage_stock'] = 1;
        $wholeData['product']['quantity_and_stock_status']['qty'] = $data['product']['stock'];
        $wholeData['product']['quantity_and_stock_status']['is_in_stock'] = $isInStock;
        $wholeData['product']['meta_title'] = $data['product']['meta_title'];
        $wholeData['product']['meta_keyword'] = $data['product']['meta_keyword'];
        $wholeData['product']['meta_description'] = $data['product']['meta_description'];
        $wholeData['product']['shipping_price_to_mygmbh'] = $data['product']['shipping_price_to_mygmbh'];
        $wholeData['product']['mygmbh_shipping_product_length'] = $data['product']['mygmbh_shipping_product_length'];
        $wholeData['product']['mygmbh_shipping_product_width'] = $data['product']['mygmbh_shipping_product_width'];
        $wholeData['product']['mygmbh_shipping_product_height'] = $data['product']['mygmbh_shipping_product_height'];
        $scopeConfig = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $electronicTypeVal = $scopeConfig->getValue('marketplace/product_cat_type/electronics_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $nonElectronicTypeVal = $scopeConfig->getValue('marketplace/product_cat_type/non_electronics_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!empty($data['product']) && (isset($data['product']['product_cat_type']))) {
            if ($data['product']['product_cat_type'] == 'Electronics') {
                $wholeData['product']['product_cat_type'] = $electronicTypeVal;
            }elseif ($data['product']['product_cat_type'] == 'Non-Electronics') {
                $wholeData['product']['product_cat_type'] = $nonElectronicTypeVal;
            }
        }
        // $wholeData['product']['product_cat_type'] = $data['product']['product_cat_type'];
        $wholeData['product']['fsk_product_type'] = $data['product']['fsk_product_type'];
        $wholeData['product']['product_note'] = $data['product']['product_note'];
        $wholeData['product']['delivery_days_from'] = $data['product']['delivery_days_from'];
        $wholeData['product']['delivery_days_to'] = $data['product']['delivery_days_to'];
        /*START :: Set Special Price Info*/
        $wholeData = $this->processSpecialPriceData($wholeData, $data);
        /*Set Image Info*/
        $wholeData = $this->processImageData($wholeData, $data, $profileId);
        /*Set Downloadable Data*/
        $isDownloadableAllowed = $this->isProductTypeAllowed('downloadable');
        if ($profileType == 'downloadable' && $isDownloadableAllowed) {
            $wholeData = $this->processDownloadableData($wholeData, $data, $profileId);
        }
        /*Set Configurable Data*/
        $isConfigurableAllowed = $this->isProductTypeAllowed('configurable');
        if ($profileType == 'configurable' && $isConfigurableAllowed) {
            $wholeData = $this->processConfigurableData(
                $wholeData,
                $data,
                $mainRow,
                $childRowArr,
                $uploadedFileRowData,
                $profileId
            );
        }
        /*Set Custom Attributes Values*/
        if ($this->canSaveCustomAttribute()) {
            $wholeData = $this->processCustomAttributeData($wholeData, $data);
        }
        // $allowedAttributes = ['delivery_days_from', 'delivery_days_to'];
        $sensitiveAttrs = $this->_objectManager->create('Mangoit\Marketplace\Helper\Data')->getSensitiveAttributes();
        if (!empty($sensitiveAttrs)){
            foreach ($sensitiveAttrs as $sensAttrValues){
                $wholeData['product'][$sensAttrValues->getAttributeCode()] = $data['product'][$sensAttrValues->getAttributeCode()];
            }
        }
        /*Set Custom Options Values*/
        if ($this->canSaveCustomOption()) {
            $wholeData = $this->processCustomOptionData($wholeData, $data);
        }
        $wholeData = $this->utf8Converter($wholeData);
        return $wholeData;
    }

    /**
     * Process Custom Attribute Data
     *
     * @param array $wholeData
     * @param array $data
     *
     * @return array $wholeData
     */
    public function processCustomAttributeData($wholeData, $data)
    {
        foreach ($data['product'] as $code => $value) {
            $code = trim($code);
            $attribute = $this->getAttributeDataByCode($code);
            $notAllowedAttr = [
                'images',
                'price',
                'special_price',
                'special_from_date',
                'special_to_date',
                'attribute_set_id',
                'category_ids',
                'visibility',
                'tax_class_id',
                'product_has_weight',
                'weight'
            ];
            if ($this->isAttributeAllowed($attribute) && !in_array($code, $notAllowedAttr)) {
                if ($code == "tier_price") {
                    $value = $this->processTierPrice($value);
                    if (!empty($value)) {
                        foreach ($value as $key => $vl) {
                            if (empty($vl['website_id'])) {
                                $value[$key]['website_id'] = 0;
                            }
                        }
                        $wholeData['product'][$code] = $value;
                    }
                } else {
                    $wholeData['product'][$code] = $value;
                }
            }
        }
        return $wholeData;
    }

    public function csv_to_array($filename='', $delimiter=',')
    {
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;
        
        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }
    public function validateFields($data, $profileType, $row)
    {
        $data = $this->prepareProductDataIfNotSet($data, $profileType);
        if (empty($data['product'])) {
              $result['error'] = 1;
              $result['data'] = $data;
              $result['msg'] = __('Skipped row %1. product data can not be empty.', $row);
              return $result;
        } else {
            $name = $data['product']['name'];
            $sku = $data['product']['sku'];
            $description = $data['product']['description'];
            $imagesArr = $this->getArrayFromString($data['product']['images']);
            $weight = $data['product']['weight'];
            if (strlen($name) <= 0) {
                $result['error'] = 1;
                $result['data'] = $data;
                $result['msg'] = __('Skipped row %1. product name can not be empty.', $row);
                return $result;
            }
            if (strlen($description) <= 0) {
                $result['error'] = 1;
                $result['data'] = $data;
                $result['msg'] = __('Skipped row %1. product description can not be empty.', $row);
                return $result;
            }
            if (($profileType != 'virtual') && ($profileType != 'downloadable') && strlen($weight) <= 0) {
                $result['error'] = 1;
                $result['data'] = $data;
                $result['msg'] = __('Skipped row %1. product weight can not be empty.', $row);
                return $result;
            }
            if (strlen($sku) <= 0) {
                  $result['error'] = 1;
                  $result['data'] = $data;
                  $result['msg'] = __('Skipped row %1. product sku can not be empty.', $row);
                  return $result;
            }
            if (count($imagesArr) <= 0) {
                  $result['error'] = 1;
                  $result['data'] = $data;
                  $result['msg'] = __('Skipped row %1. product image can not be empty.', $row);
                  return $result;
            }
            $productId = $this->_product->create()->getIdBySku($sku);
            if ($productId) {
                $product = $this->_product->create()->load($productId);
                $data['id'] = $productId;
                $data['product_id'] = $productId;
                $data['product']['website_ids'][] = $product->getStore()->getWebsiteId();
                $data['product']['url_key'] = $product->getUrlKey();
            }
        }
        return ['error' => 0, 'data' => $data];
    }
}
