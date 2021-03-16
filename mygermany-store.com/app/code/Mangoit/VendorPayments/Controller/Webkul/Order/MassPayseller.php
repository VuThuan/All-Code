<?php

namespace Mangoit\VendorPayments\Controller\Webkul\Order;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Helper\Email as MpEmailHelper;
use Webkul\Marketplace\Model\SellertransactionFactory;
use Webkul\Marketplace\Model\SaleperpartnerFactory;
use Webkul\Marketplace\Model\OrdersFactory;
use Webkul\Marketplace\Helper\Notification as NotificationHelper;

/**
 * Class MassPayseller.
 */
class MassPayseller extends \Webkul\Marketplace\Controller\Adminhtml\Order\MassPayseller
{
    private $vendorPayHelper;

     /**
     * @var Filter
     */
    public $filter;
    /**
     * @var CollectionFactory
     */
    public $collectionFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    public $dateTime;
    /** @var \Magento\Sales\Model\OrderRepository */
    public $orderRepository;
    /**
     * @var MpHelper
     */
    protected $mpHelper;
    /**
     * @var MpEmailHelper
     */
    protected $mpEmailHelper;
    /**
     * @var SellertransactionFactory
     */
    protected $sellertransaction;
    /**
     * @var SaleperpartnerFactory
     */
    protected $saleperpartner;
    /**
     * @var OrdersFactory
     */
    protected $ordersModel;
    /**
     * @var NotificationHelper
     */
    protected $notificationHelper;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;

    /**
     * @param Context                                     $context
     * @param Filter                                      $filter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime          $dateTime
     * @param \Magento\Sales\Model\OrderRepository        $orderRepository
     * @param CollectionFactory                           $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        CollectionFactory $collectionFactory,
        MpHelper $mpHelper,
        MpEmailHelper $mpEmailHelper,
        SellertransactionFactory $sellertransaction,
        SaleperpartnerFactory $saleperpartner,
        OrdersFactory $ordersModel,
        NotificationHelper $notificationHelper,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        \Mangoit\VendorPayments\Helper\Data $vendorPayHelper
    ) {
        $this->filter = $filter;
        $this->date = $date;
        $this->dateTime = $dateTime;
        $this->orderRepository = $orderRepository;
        $this->collectionFactory = $collectionFactory;
        $this->vendorPayHelper = $vendorPayHelper;
        $this->mpHelper = $mpHelper;
        $this->mpEmailHelper = $mpEmailHelper;
        $this->sellertransaction = $sellertransaction;
        $this->saleperpartner = $saleperpartner;
        $this->ordersModel = $ordersModel;
        $this->notificationHelper = $notificationHelper;
        $this->customerModel = $customerModel;
        parent::__construct($context, $filter, $date, $dateTime, $orderRepository, $collectionFactory, $mpHelper, $mpEmailHelper, $sellertransaction, $saleperpartner, $ordersModel, $notificationHelper, $customerModel);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            $wholedata = $this->getRequest()->getParams();
            $actparterprocost = 0;
            $totalamount = 0;
            $sellerId = $wholedata['seller_id'];
            $wksellerorderids = explode(',', $wholedata['wksellerorderids']);

            $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
            $taxToSeller = $helper->getConfigTaxManage();

            $orderinfo = '';

            $collection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleslist'
            )->getCollection()
            ->addFieldToFilter('entity_id', ['in' => $wksellerorderids])
            ->addFieldToFilter('order_id', ['neq' => 0])
            ->addFieldToFilter('paid_status', 0)
            ->addFieldToFilter('return_request_by_customer', ['neq' => 1])
            ->addFieldToFilter('cpprostatus', ['neq' => 0]);

            $totalAmountArray= [];
            $totalFeeArray= [];
            $sellerPriceArray = $this->vendorPayHelper->getSellerPriceArray($collection->getData());
            foreach ($collection as $row) {
                $sellerId = $row->getSellerId();
                $order = $this->orderRepository->get($row['order_id']);
                $taxAmount = $row['total_tax'];
                $marketplaceOrders = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Orders'
                )->getCollection()
                ->addFieldToFilter('order_id', $row['order_id'])
                ->addFieldToFilter('seller_id', $sellerId);
                foreach ($marketplaceOrders as $tracking) {
                    $taxToSeller = $tracking['tax_to_seller'];
                }
                $vendorTaxAmount = 0;
                if ($taxToSeller) {
                    $vendorTaxAmount = $taxAmount;
                }
                $codCharges = 0;
                $shippingCharges = 0;
                if (!empty($row['cod_charges'])) {
                    $codCharges = $row->getCodCharges();
                }
                if ($row->getIsShipping() == 1) {
                    foreach ($marketplaceOrders as $tracking) {
                        $shippingamount = $tracking->getShippingCharges();
                        $refundedShippingAmount = $tracking->getRefundedShippingCharges();
                        $shippingCharges = $shippingamount - $refundedShippingAmount;
                    }
                }
                $actparterprocost = $actparterprocost +
                    $row->getActualSellerAmount() +
                    $vendorTaxAmount +
                    $codCharges +
                    $shippingCharges;
                $totalamount = $totalamount +
                    $row->getTotalAmount() +
                    $taxAmount +
                    $codCharges +
                    $shippingCharges;

                $totalShipping = $sellerPriceArray[$row->getMagerealorderId()];
                $sellerPriceArray[$row->getMagerealorderId()] = 0;

                $totalAmountArray[] = number_format($row->getTotalAmount(), 2, '.', '');
                $totalAmountArray[] = $totalShipping;
                $totalFeeArray[] = number_format($row->getTotalCommission(), 2, '.', '');
                $totalFeeArray[] = number_format($row->getMitsPaymentFeeAmount(), 2, '.', '');
                $totalFeeArray[] = number_format($row->getMitsExchangeRateAmount(), 2, '.', '');

                $itemTotalAmount = $row->getTotalAmount() + $totalShipping;
                $totalItemFee = ($row->getTotalCommission() +
                        $row->getMitsPaymentFeeAmount() +
                        $row->getMitsExchangeRateAmount());

                $itemSubtotal = $itemTotalAmount - $totalItemFee;

                $orderinfo = $orderinfo."<tr>
                    <td class='item-info'>".$row['magerealorder_id']."</td>
                    <td class='item-info'>".$row['magepro_name']."</td>
                    <td class='item-qty'>".$row['magequantity']."</td>
                    <td class='item-price'>".$order->formatBasePrice($row['magepro_price'])."</td>
                    <td class='item-price'>".$order->formatBasePrice($row['total_commission'])."</td>
                    <td class='item-price'>".$order->formatBasePrice($itemSubtotal).'</td>
                </tr>';
            }
            $netTotal = (array_sum($totalAmountArray) - array_sum($totalFeeArray));
            $totalInclVat = number_format(((array_sum($totalAmountArray)-array_sum($totalFeeArray))*19)/100, 2, '.', '');
            $totalToBePaid = number_format($netTotal + $totalInclVat, 2, '.', '');
            $actparterprocost = $totalToBePaid;

            if ($actparterprocost) {
                $collectionverifyread = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Saleperpartner'
                )->getCollection()
                ->addFieldToFilter('seller_id', $sellerId);
                if (count($collectionverifyread) >= 1) {
                    $id = 0;
                    $totalremain = 0;
                    $amountpaid = 0;
                    foreach ($collectionverifyread as $verifyrow) {
                        $id = $verifyrow->getId();
                        if ($verifyrow->getAmountRemain() >= $actparterprocost) {
                            $totalremain = $verifyrow->getAmountRemain() - $actparterprocost;
                        }
                        $amountpaid = $verifyrow->getAmountReceived();
                    }
                    $verifyrow = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Saleperpartner'
                    )->load($id);
                    $totalrecived = $actparterprocost + $amountpaid;
                    $verifyrow->setLastAmountPaid($actparterprocost);
                    $verifyrow->setAmountReceived($totalrecived);
                    $verifyrow->setAmountRemain($totalremain);
                    $verifyrow->setUpdatedAt($this->date->gmtDate());
                    $verifyrow->save();
                } else {
                    $percent = $helper->getConfigCommissionRate();
                    $collectionf = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Saleperpartner'
                    );
                    $collectionf->setSellerId($sellerId);
                    $collectionf->setTotalSale($totalamount);
                    $collectionf->setLastAmountPaid($actparterprocost);
                    $collectionf->setAmountReceived($actparterprocost);
                    $collectionf->setAmountRemain(0);
                    $collectionf->setCommissionRate($percent);
                    $collectionf->setTotalCommission($totalamount - $actparterprocost);
                    $collectionf->setCreatedAt($this->date->gmtDate());
                    $collectionf->setUpdatedAt($this->date->gmtDate());
                    $collectionf->save();
                }

                $uniqueId = $this->checktransid();
                $transid = '';
                $transactionNumber = '';
                if ($uniqueId != '') {
                    $sellerTrans = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Sellertransaction'
                    )->getCollection()
                    ->addFieldToFilter('transaction_id', $uniqueId);
                    if (count($sellerTrans)) {
                        $id = 0;
                        foreach ($sellerTrans as $value) {
                            $id = $value->getId();
                        }
                        if ($id) {
                            $this->_objectManager->create(
                                'Webkul\Marketplace\Model\Sellertransaction'
                            )->load($id)->delete();
                        }
                    }
                    $sellerTrans = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Sellertransaction'
                    );
                    $sellerTrans->setTransactionId($uniqueId);
                    $sellerTrans->setTransactionAmount($actparterprocost);
                    $sellerTrans->setType('Manual');
                    $sellerTrans->setMethod('Manual');
                    $sellerTrans->setSellerId($sellerId);
                    $sellerTrans->setCustomNote($wholedata['seller_pay_reason']);
                    $sellerTrans->setCreatedAt($this->date->gmtDate());
                    $sellerTrans->setUpdatedAt($this->date->gmtDate());
                    $sellerTrans->setSellerPendingNotification(1);

                    $sellerTrans = $sellerTrans->save();
                    $transid = $sellerTrans->getId();
                    $transactionNumber = $sellerTrans->getTransactionId();
                    $this->_objectManager->create(
                        'Webkul\Marketplace\Helper\Notification'
                    )->saveNotification(
                        \Webkul\Marketplace\Model\Notification::TYPE_TRANSACTION,
                        $transid,
                        $transid
                    );
                }

                foreach ($collection as $collectionData) {
                    $collection->setSalesListData(
                        $collectionData->getId(),
                        ['paid_status' => 1, 'trans_id' => $transid]
                    );
                    $data['trans_id'] = $transactionNumber;
                    $data['mp_trans_row_id'] = $transid;
                    $data['mp_saleslist_row_id'] = $collectionData->getId();
                    $data['id'] = $collectionData->getOrderId();
                    $data['seller_id'] = $collectionData->getSellerId();
                    $this->_eventManager->dispatch(
                        'mp_pay_seller',
                        [$data]
                    );
                }

                $seller = $this->_objectManager->create(
                    'Magento\Customer\Model\Customer'
                )->load($sellerId);

                $emailTempVariables = [];

                $adminStoreEmail = $helper->getAdminEmailId();
                $adminEmail = $adminStoreEmail ? $adminStoreEmail : $helper->getDefaultTransEmailId();
                
                $adminUsername = $this->_objectManager->get(
                    'Mangoit\Marketplace\Helper\Corehelper'
                )->adminEmailName();
                /*$adminUsername = 'Admin';*/

                $senderInfo = [];
                $receiverInfo = [];

                $receiverInfo = [
                    'name' => $seller->getName(),
                    'email' => $seller->getEmail()
                ];
                $senderInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail
                ];

                $emailTempVariables['myvar1'] = $seller->getName();
                $emailTempVariables['myvar2'] = $transactionNumber;
                $emailTempVariables['myvar3'] = $this->date->gmtDate();
                $emailTempVariables['myvar4'] = $order->formatBasePrice($actparterprocost);
                $emailTempVariables['myvar5'] = $orderinfo;
                $emailTempVariables['myvar6'] = $wholedata['seller_pay_reason'];

                $sellerStoreId = $seller->getStoreId();
                
                $this->_objectManager->get('Webkul\Marketplace\Helper\Email')->sendSellerPaymentEmail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo,
                    $sellerStoreId
                );

                $this->messageManager->addSuccess(__('Payment has been successfully done for this seller'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t pay the seller right now. %1', $e->getMessage()));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('marketplace/order/index', ['seller_id' => $sellerId]);
    }
}
