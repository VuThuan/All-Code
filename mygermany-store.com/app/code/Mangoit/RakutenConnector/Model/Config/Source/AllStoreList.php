<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Model\Config\Source;

class AllStoreList implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManger;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManger
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManger
    ) {
        $this->_storeManger = $storeManger;
    }
    /**
     * Return options array.
     *
     * @param int $store
     *
     * @return array
     */
    public function toOptionArray($store = null)
    {
        $stores = $this->_storeManger->getStores();
        foreach ($stores as $store) {
            $optionArray[$store->getId()] = $store->getName();
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->toOptionArray();
    }
}
