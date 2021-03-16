<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Import\Item\Barcode;

/**
 * Class Collection
 * @package Plenty\Item\Model\ResourceModel\Import\Item\Barcode
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\Import\Item\Barcode::class, \Plenty\Item\Model\ResourceModel\Import\Item\Barcode::class);
    }
}