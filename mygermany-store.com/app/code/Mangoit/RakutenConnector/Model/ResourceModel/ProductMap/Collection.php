<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Model\ResourceModel\ProductMap;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */

    protected $_idFieldName = 'entity_id';
    
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            'Mangoit\RakutenConnector\Model\ProductMap',
            'Mangoit\RakutenConnector\Model\ResourceModel\ProductMap'
        );
    }
}
