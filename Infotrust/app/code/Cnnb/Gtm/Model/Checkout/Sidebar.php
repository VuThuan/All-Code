<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cnnb\Gtm\Model\Checkout;

use Magento\Checkout\Helper\Data as HelperData;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Address\Total;

class Sidebar extends \Magento\Checkout\Model\Sidebar
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var int
     */
    protected $summaryQty;

    /**
     * @var int
     */
    protected $_dataLayerBlock;

    /**
     * @var int
     */
    protected $_jsonHelper;

    /**
     * @var int
     */
    protected $_session;

    /**
     * @var int
     */
    protected $_logger;

    /**
     * @param Cart $cart
     * @param HelperData $helperData
     * @param ResolverInterface $resolver
     * @codeCoverageIgnore
     */
    public function __construct(
        Cart $cart,
        HelperData $helperData,
        ResolverInterface $resolver,
        \Cnnb\Gtm\Block\DataLayer $dataLayerBlock,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Session\SessionManagerInterface $session
    ) {
        $this->cart = $cart;
        $this->helperData = $helperData;
        $this->resolver = $resolver;
        $this->_dataLayerBlock = $dataLayerBlock;
        $this->_jsonHelper = $jsonHelper;
        $this->_session = $session;
        $this->_logger = $this->getLogger();
        parent::__construct($cart, $helperData, $resolver);
    }

    /**
     * Compile response data
     *
     * @param string $error
     * @return array
     */
    public function getResponseData($error = '')
    {
        if (empty($error)) {
            $response = [
                'success' => true,
            ];
        } else {
            $response = [
                'success' => false,
                'error_message' => $error,
            ];
        }
        return $response;
    }

    /**
     * Check if required quote item exist
     *
     * @param int $itemId
     * @throws LocalizedException
     * @return $this
     */
    public function checkQuoteItem($itemId)
    {
        $item = $this->cart->getQuote()->getItemById($itemId);
        if (!$item instanceof CartItemInterface) {
            throw new LocalizedException(__("The quote item isn't found. Verify the item and try again."));
        }
        return $this;
    }

    /**
     * Remove quote item
     *
     * @param int $itemId
     * @return $this
     */
    public function removeQuoteItem($itemId)
    {
        /* ------- DataLayer Code ------- */
        $product_id = $this->cart->getQuote()->getItemById($itemId)->getProductId();
        $return_data = $this->_dataLayerBlock->getCartDetails(1, null, $product_id);        
        $this->_logger->info(' ');
        $this->_logger->info('---------- Remove Items | Override File --------');
        $this->_logger->info(' Item ID will be removed:  '.$product_id);
        $this->_logger->info($this->_jsonHelper->jsonEncode($return_data));
        $this->_session->setRemoveCartDataLayer($return_data);
        /* ------- DataLayer Code Ends ------- */
        $this->cart->removeItem($itemId);
        $this->cart->save();
        return $this;
    }

    /**
     * Update quote item
     *
     * @param int $itemId
     * @param int $itemQty
     * @throws LocalizedException
     * @return $this
     */
    public function updateQuoteItem($itemId, $itemQty)
    {
        $itemData = [$itemId => ['qty' => $this->normalize($itemQty)]];
        $this->cart->updateItems($itemData)->save();
        /* ------- DataLayer Code ------- */
        $product_id = $this->cart->getQuote()->getItemById($itemId)->getProductId();
        $return_data = $this->_dataLayerBlock->getCartDetails(2, null, $product_id);        
        $this->_logger->info(' ');
        $this->_logger->info('---------- Update Items | Override File --------');
        $this->_logger->info(' Item ID will be update:  '.$product_id);
        $this->_logger->info(print_r($return_data, true));
        $this->_session->setUpdatedCartDataLayer($return_data);
        /* ------- DataLayer Code Ends ------- */
        return $this;
    }

    /**
     * Apply normalization filter to item qty value
     *
     * @param int $itemQty
     * @return int|array
     */
    protected function normalize($itemQty)
    {
        if ($itemQty) {
            $filter = new \Zend_Filter_LocalizedToNormalized(
                ['locale' => $this->resolver->getLocale()]
            );
            return $filter->filter((string)$itemQty);
        }
        return $itemQty;
    }

    /**
     * Retrieve summary qty
     *
     * @return int
     */
    protected function getSummaryQty()
    {
        if (!$this->summaryQty) {
            $this->summaryQty = $this->cart->getSummaryQty();
        }
        return $this->summaryQty;
    }

    /**
     * Retrieve summary qty text
     *
     * @return string
     */
    protected function getSummaryText()
    {
        return ($this->getSummaryQty() == 1) ? __(' item') : __(' items');
    }

    /**
     * Retrieve subtotal block html
     *
     * @return string
     */
    protected function getSubtotalHtml()
    {
        $totals = $this->cart->getQuote()->getTotals();
        $subtotal = isset($totals['subtotal']) && $totals['subtotal'] instanceof Total
            ? $totals['subtotal']->getValue()
            : 0;
        return $this->helperData->formatPrice($subtotal);
    }

    public function getLogger()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/remove-cart.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        return $logger;
    }
}
