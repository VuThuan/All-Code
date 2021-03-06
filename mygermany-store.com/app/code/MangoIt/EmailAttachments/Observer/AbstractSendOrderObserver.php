<?php
/**
 * @author     MangoIt
 * @package    MangoIt_EmailAttachments
 * @copyright  Copyright (c) 2015 MangoIt Solutions (http://www.mangoitsolutions.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MangoIt\EmailAttachments\Observer;

class AbstractSendOrderObserver extends AbstractObserver
{
    const XML_PATH_ATTACH_PDF = 'sales_email/order/attachpdf';
    const XML_PATH_ATTACH_AGREEMENT = 'sales_email/order/attachagreement';

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        /**
         * @var $order \Magento\Sales\Api\Data\OrderInterface
         */
        $order = $observer->getOrder();
        if ($this->pdfRenderer->canRender()
            && $this->scopeConfig->getValue(
                static::XML_PATH_ATTACH_PDF,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $order->getStoreId()
            )
        ) {
            $this->attachPdf(
                $this->pdfRenderer->getPdfAsString([$order]),
                $this->pdfRenderer->getFileName(__('Order') . $order->getIncrementId()),
                $observer->getAttachmentContainer()
            );
        }

        if ($this->scopeConfig->getValue(
            static::XML_PATH_ATTACH_AGREEMENT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        )
        ) {
            $this->attachTermsAndConditions($order->getStoreId(), $observer->getAttachmentContainer());
        }
    }
}
