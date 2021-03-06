<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Mangoit\Dhlshipment\Controller\Dhl;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Filesystem\DirectoryList;


/**
 * Webkul Marketplace Product Add Controller.
 */
class Index extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    protected $_helper;

    protected $_dhlRetoure;

    protected $_messageManager;
    protected $_packagingSlip;
    protected $_downloader;

    /**
     * @param Context                                       $context
     * @param Webkul\Marketplace\Controller\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory    $resultPageFactory
     * @param \Magento\Customer\Model\Session               $customerSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Mangoit\Dhlshipment\Helper\DhlRetoure $dhlRetoure,
        \Mangoit\Dhlshipment\Helper\Data $helper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Mangoit\Orderdispatch\Helper\PackagingSlip $packagingSlip,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
        $this->_dhlRetoure = $dhlRetoure;
        $this->_messageManager = $messageManager;
        $this->_packagingSlip = $packagingSlip;
        $this->_downloader =  $fileFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
        $currentUrl = $urlInterface->getCurrentUrl();
        $customerData = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($_SESSION['customer']['customer_id']);
        // $customerData = $this->_customerSession->getCustomerData();
       
        /*$id = $customerData->getId();*/
        $id = $_SESSION['customer']['customer_id'];
        $parameter = $this->getRequest()->getParams();

        /* Set product id for ajax and non-ajax request. */
        if (isset($parameter['product_ids'])) {
            $product_ids = explode(',', $parameter['product_ids']);
            unset($product_ids[0]);
            $product_id = $product_ids[1];
        } else {
            $product_id = $parameter['product_id'];
        }

        if (isset($parameter['mis_order_id'])) {
            $order_id = $parameter['mis_order_id'];
        }
        
        $helper = $this->_objectManager->create('Mangoit\Dhlshipment\Helper\Data');
        $dhl_fees = $helper->getDhlFees();
        if (strlen($dhl_fees) < 1) {
            return ['status'=> 'dhl_fees_error', 'message'=> __('Default DHL fees has not been added. Ask admin to add default DHL Fees.')];
            /*echo "2";*/
            
            /*echo "3";
            exit();*/
        }

        $saleListModel = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist');
        $collection = $saleListModel->getCollection()->addFieldToFilter(
            'seller_id', array('eq'=> $id)
        )->addFieldToFilter(
            'order_id', array('eq'=> $order_id)
        )->addFieldToFilter(
            'mageproduct_id', array('eq'=> $product_id)
        );

        foreach ($collection as $item) {
            $item->setDhlFees($dhl_fees);
            $item->save();
        }

        $streetNumber = 5;
        $street = "";
        $address = $this->_objectManager->create('Magento\Customer\Model\Address');

        $email = $customerData->getEmail();

        if (!is_null($customerData->getDefaultBilling()) ) {
            $addressId = $customerData->getDefaultBilling();
        } else {
            $addressId = $customerData->getDefaultShipping();
        }

        $defaultAddress = $address->load($addressId);

        if (empty($defaultAddress->getData())) {
            $this->_messageManager->addError(__("Please add an address with your account."));
            return ['status'=> 'address_error', 'message'=> __('Please add an address with your account.')];
            /*echo "2";*/
           
        }

        if (!empty($defaultAddress->getData()) && count($defaultAddress->getData()) > 1) {
            # code...
            $company = $defaultAddress->getCompany();
            if (count($defaultAddress->getStreet()) > 1) {
                foreach ($defaultAddress->getStreet() as $key => $value) {
                    $street = $street.$value;

                }

            } else {
                $street = $defaultAddress->getStreet()[0];
            }

            $city = $defaultAddress->getCity();
            $state = $defaultAddress->getRegion();
            $country = $defaultAddress->getCountryId();
            $postCode = $defaultAddress->getPostcode();
            $telephone = $defaultAddress->getTelephone();
            
            $first_name = $defaultAddress->getFirstname();
            $last_name = $defaultAddress->getLastname();
        }

        /*$pdf = $this->_dhlRetoure->getRetourePdf("Praveen","verma","Deutsche Post IT BRIEF", "4700","58093", "Hagen");*/

        $pdfData = $this->_dhlRetoure->getRetourePdf($first_name, $last_name, $street, $streetNumber, $postCode, $city);
        $pdf = $pdfData['pdf']; 
        $trackingId = $pdfData['trackingId'];

        /*if (!isset($parameter['is_ajax'])) {
            if($pdf){
                $this->_dhlRetoure->displayPdf($pdf);
                
            }
        }*/
        /*} else {*/
            /* For creating and email packaging slip */
            $dhlPackagingSlip = $this->_packagingSlip->getPdfHtmlContent($parameter['mis_order_id'], $pdfData['trackingId'], 'DHL');
            /* For creating and email packaging slip ends */

            $this->_packagingSlip->sendDhlLabel($id, $first_name, $parameter['order_increment_id'], $pdfData['trackingId'], $pdf, $dhlPackagingSlip);

            $filename = 'DHL_Label_'.$first_name.'_'.$parameter['order_increment_id'].'.pdf';
            
            try {
                $om = \Magento\Framework\App\ObjectManager::getInstance();
                $filesystem = $om->get('Magento\Framework\Filesystem');
                $directoryList = $om->get('Magento\Framework\App\Filesystem\DirectoryList');
                $media = $filesystem->getDirectoryWrite($directoryList::MEDIA);
                $media->writeFile("packageSlip/DHL/".$filename, $pdf);                
            } catch(Exception $e) {
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/DHL-Label-index.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info('Exception: '.$e->getMessage());
            }     

            return ['status'=> 'success', 'filename'=> $filename, 'mis_tracking_id'=> $pdfData['trackingId']];  
           
            /*echo json_encode(
                    ['pdf'=> 
                    "".$currentUrl."order_id/".$parameter['order_id']."/product_id/".$product_ids[1], 
                    'trackingId'=> $pdfData['trackingId'],
                    'filename'=> $filename,
                ]
            );
            exit();*/
       /* }*/
        
    }

}