<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Block;

/*
 * Webkul Marketplace Sellerblock Block
 */
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Catalog\Model\Product;

class Sellerblock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $session;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param Customer                                         $customer
     * @param \Magento\Customer\Model\Session                  $session
     * @param \Webkul\Marketplace\Helper\Data                  $mpHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        Customer $customer,
        \Magento\Customer\Model\Session $session,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        array $data = []
    ) {
        $this->Customer = $customer;
        $this->Session = $session;
        $this->_coreRegistry = $registry;
        $this->mpHelper = $mpHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }

        return $this->_product;
    }
}
