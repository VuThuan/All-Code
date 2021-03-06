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

class AbstractSendShipmentObserver extends AbstractObserver
{
    const XML_PATH_ATTACH_PDF = 'sales_email/shipment/attachpdf';
    const XML_PATH_ATTACH_AGREEMENT = 'sales_email/shipment/attachagreement';

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        /**
         * @var $shipment \Magento\Sales\Api\Data\ShipmentInterface
         */
        $shipment = $observer->getShipment();
        if ($this->pdfRenderer->canRender()
            && $this->scopeConfig->getValue(
                static::XML_PATH_ATTACH_PDF,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $shipment->getStoreId()
            )
        ) {
            $this->attachPdf(
                $this->pdfRenderer->getPdfAsString([$shipment]),
                $this->pdfRenderer->getFileName(__('Packing Slip') . $shipment->getIncrementId()),
                $observer->getAttachmentContainer()
            );
        }

        if ($this->scopeConfig->getValue(
            static::XML_PATH_ATTACH_AGREEMENT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $shipment->getStoreId()
        )
        ) {
            $this->attachTermsAndConditions($shipment->getStoreId(), $observer->getAttachmentContainer());
        }
    }
}
