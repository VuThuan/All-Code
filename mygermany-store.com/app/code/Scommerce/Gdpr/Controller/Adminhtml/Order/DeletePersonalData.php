<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\Gdpr\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class DeletePersonalData
 * @package Scommerce\Gdpr\Controller\Adminhtml\Order
 */
class DeletePersonalData extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    const ADMIN_RESOURCE = 'Scommerce_Gdpr::config';

    /** @var \Scommerce\Gdpr\Model\Service\Account */
    private $account;

    /** @var \Magento\Framework\App\Response\Http\FileFactory */
    private $fileFactory;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface */
    private $customerRepository;

    /** @var \Scommerce\Gdpr\Helper\Data */
    private $helper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Scommerce\Gdpr\Model\Service\Account $account
     * @param \Scommerce\Gdpr\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Scommerce\Gdpr\Model\Service\Account $account,
        \Scommerce\Gdpr\Helper\Data $helper
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->customerRepository = $customerRepository;
        $this->account = $account;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    protected function massAction(AbstractCollection $collection)
    {
        if (! $this->helper->isEnabled()) {
            return $this->_redirect('admin/dashboard/index');
        }
        $ids = [];
        $emails = [];
        foreach ($collection->getItems() as $order) {
            /** @var \Magento\Sales\Model\Order $order */
            if ($order->getCustomerId()) {
                if (!in_array($order->getCustomerId(), $ids)) {
                    $ids[] = $order->getCustomerId();
                }
            } else {
                if (!in_array($order->getCustomerEmail(), $emails)) {
                    $emails[] = $order->getCustomerEmail();
                }
            }
        }

        foreach ($ids as $id) {
            $customer = $this->customerRepository->getById($id);
            if ($customer != null) {
                $this->account->anonymize($customer);
            }
        }

        foreach ($emails as $email) {
            $this->account->anonymize(null, $email);
        }

        return $this->_redirect($this->getComponentRefererUrl());
    }
}
