<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\AmazonMagentoConnect\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory.
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init.
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepositoryInterface
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeRepository = $attributeRepositoryInterface;
    }
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        try {
            $attribute = $this->attributeRepository
                    ->get(\Magento\Catalog\Model\Product::ENTITY, 'identification_label');
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'identification_label',
                [
                  'label' => 'Unique Identifier',
                  'input' => 'select',
                  'group' => 'Amazon Product Identifier',
                  'source' => '',
                  'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                  'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                  'option'     =>  [
                      'values' => [
                          'asin' => 'ASIN',
                          'ean' => 'EAN',
                          'upc' => 'UPC',
                          'isbn' => 'ISBN',
                          'gtin' => 'GTIN',
                          'jan' => 'JAN',
                      ]
                  ],
                  'visible' => true,
                  'required' => false,
                  'user_defined' => true,
                  'visible_on_front' => false,
                  'is_configurable' => false,
                  'searchable' => true,
                  'default' => '',
                  'filterable' => true,
                  'comparable' => true,
                  'visible_in_advanced_search' => true,
                  'apply_to' => 'simple,configurable',
                ]
            );
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'identification_value',
                [
                  'label' => 'Unique Identification Code',
                  'input' => 'text',
                  'group' => 'Amazon Product Identifier',
                  'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                  'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                  'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                  'visible' => true,
                  'required' => false,
                  'user_defined' => true,
                  'visible_on_front' => false,
                  'is_configurable' => false,
                  'searchable' => true,
                  'default' => '',
                  'filterable' => true,
                  'comparable' => true,
                  'visible_in_advanced_search' => true,
                  'note' => ' Enter Unique Identification Code as per selected unique identifier. ',
                  'apply_to' => 'simple,configurable',
                ]
            );
        }
    }
}
