<?php

namespace Mangoit\VendorPayments\Controller\Adminhtml\Index;


class Exchangerates extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
	}
}
