<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_VendorPayments
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit Software Private Limited
 */

namespace Mangoit\Dhlshipment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $setup2
    ){
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup2 = $setup2;
    }
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $setup->getConnection()->addColumn(
            $setup->getTable('marketplace_saleslist'),
            'dhl_fees',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '0',
                'comment' => 'DHL fees deducted from the vendor\'s payable amount.'
            ]
        );
            $installer->endSetup();

        /*
         * Create table 'marketplace_product'
         */
        // $table = $installer->getConnection()
        //     ->newTable($installer->getTable('mits_vendor_payment_fees'))
        //     ->addColumn(
        //         'id',
        //         \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        //         null,
        //         ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        //         'Entity ID'
        //     )
        //     ->addColumn(
        //         'counrty_group',
        //         \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        //         null,
        //         ['nullable' => true, 'default' => null],
        //         'Country Group'
        //     )
        //     ->addColumn(
        //         'cost_per_tans',
        //         \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        //         '12,4',
        //         ['nullable' => false, 'default' => '0.0000'],
        //         'Fixed Cost per Transaction in EUR'
        //     )
        //     ->addColumn(
        //         'percent_of_total_per_tans',
        //         \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
        //         '12,4',
        //         ['nullable' => false, 'default' => '0.00'],
        //         'Transaction Cost in percent of total amount.'
        //     )

        //     ->addColumn(
        //         'payment_method',
        //         \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        //         null,
        //         ['nullable' => true, 'default' => null],
        //         'Payment Method'
        //     )
        //     ->addColumn(
        //         'card_type',
        //         \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        //         null,
        //         ['nullable' => true, 'default' => null],
        //         'Cart Type'
        //     )
        //     ->addColumn(
        //         'effective_countries',
        //         \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        //         null,
        //         ['nullable' => true, 'default' => null],
        //         'Countries to which the fee will be effective.'
        //     )
        //     ->addColumn(
        //         'created_at',
        //         \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        //         null,
        //         [],
        //         'Creation Time'
        //     )
        //     ->addColumn(
        //         'updated_at',
        //         \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        //         null,
        //         [],
        //         'Update Time'
        //     )
        //     ->setComment('Payment Fees Table');
        // $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}