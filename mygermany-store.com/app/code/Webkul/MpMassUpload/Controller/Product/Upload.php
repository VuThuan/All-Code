<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

class Upload extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Webkul\MpMassUpload\Helper\Data
     */
    protected $_massUploadHelper;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper
    ) {
        $this->_url = $url;
        $this->_session = $session;
        $this->_massUploadHelper = $massUploadHelper;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $noValidate="";
        $files = $this->getRequest()->getFiles();
        if ($files['massupload_image']['name'] == "") {
            $noValidate = 'image';
        }
        $helper = $this->_massUploadHelper;
        $validateData = $helper->validateUploadedFiles($noValidate);
        if ($validateData['error']) {
            $this->messageManager->addError(__($validateData['msg']));
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        }
        $productType = $validateData['type'];
        $fileName = $validateData['csv'];
        $fileData = $validateData['csv_data'];
        $result = $helper->saveProfileData(
            $productType,
            $fileName,
            $fileData,
            $validateData['extension']
        );
        $uploadCsv = $helper->uploadCsv($result, $validateData['extension'], $fileName);
        if ($uploadCsv['error']) {
            $this->messageManager->addError(__($uploadCsv['msg']));
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        }
        if (empty($noValidate)) {
            $uploadZip = $helper->uploadZip($result, $fileData);
            if ($uploadZip['error']) {
                $this->messageManager->addError(__($uploadZip['msg']));
                return $this->resultRedirectFactory->create()->setPath('*/*/view');
            }
        }
        $isDownloadableAllowed = $helper->isProductTypeAllowed('downloadable');
        if ($productType == 'downloadable' && $isDownloadableAllowed) {
            $uploadLinks = $helper->uploadLinks($result, $fileData);
            if ($uploadLinks['error']) {
                $this->messageManager->addError(__($uploadLinks['msg']));
                return $this->resultRedirectFactory->create()->setPath('*/*/view');
            }
            if ($this->getRequest()->getParam('is_link_samples')) {
                $uploadLinkSamples = $helper->uploadLinkSamples($result, $fileData);
                if ($uploadLinkSamples['error']) {
                    $this->messageManager->addError(__($uploadLinkSamples['msg']));
                    return $this->resultRedirectFactory->create()->setPath('*/*/view');
                }
            }
            if ($this->getRequest()->getParam('is_samples')) {
                $uploadSamples = $helper->uploadSamples($result, $fileData);
                if ($uploadSamples['error']) {
                    $this->messageManager->addError(__($uploadSamples['msg']));
                    return $this->resultRedirectFactory->create()->setPath('*/*/view');
                }
            }
        }
        $message = __('Your file has uploaded successfully.');
        $this->messageManager->addSuccess($message);
        return $this->resultRedirectFactory->create()->setPath('*/*/view');
    }
}
