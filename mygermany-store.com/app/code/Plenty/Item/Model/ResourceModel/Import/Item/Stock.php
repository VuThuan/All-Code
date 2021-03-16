<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Import\Item;

use Plenty\Item\Model\ResourceModel\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\Item\StockInterface;
use Plenty\Item\Setup\SchemaInterface;

/**
 * Class Stock
 * @package Plenty\Item\Model\ResourceModel\Import\Item
 */
class Stock extends ImportExportAbstract
{
    protected function _construct()
    {
        $this->_init(SchemaInterface::ITEM_IMPORT_ITEM_STOCK, StockInterface::ENTITY_ID);
    }
}