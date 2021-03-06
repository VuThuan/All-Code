<?php
/**
 * @author     MangoIt
 * @package    MangoIt_EmailAttachments
 * @copyright  Copyright (c) 2015 MangoIt Solutions (http://www.mangoitsolutions.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MangoIt\EmailAttachments\Model\Email\Sender;

class InvoiceSender extends \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
{
    /**
     * @var \MangoIt\EmailAttachments\Model\AttachmentContainerInterface
     */
    protected $attachmentContainer;

    public function __construct(
        \Magento\Sales\Model\Order\Email\Container\Template $templateContainer,
        \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Sales\Model\ResourceModel\Order\Invoice $invoiceResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \MangoIt\EmailAttachments\Model\AttachmentContainer $attachmentContainer
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $senderBuilderFactory,
            $logger,
            $addressRenderer,
            $paymentHelper,
            $invoiceResource,
            $globalConfig,
            $eventManager
        );
        $this->attachmentContainer = $attachmentContainer;
    }


    public function send(\Magento\Sales\Model\Order\Invoice $invoice, $forceSyncMode = false)
    {
        $this->eventManager->dispatch(
            'mangoit_emailattachments_before_send_invoice',
            [

                'attachment_container' => $this->attachmentContainer,
                'invoice'             => $invoice
            ]
        );
        return parent::send($invoice, $forceSyncMode);
    }
}
