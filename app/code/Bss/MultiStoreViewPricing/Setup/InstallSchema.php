<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_MultiStoreViewPricing
* @author     Extension Team
* @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
namespace Bss\MultiStoreViewPricing\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'bss_catalogrule_product'
         */
        if ($installer->getConnection()->isTableExists($installer->getTable('bss_catalogrule_product')) != true) {
            $table = $installer->getConnection()
            ->newTable($installer->getTable('bss_catalogrule_product'))
            ->addColumn(
                'rule_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Product Id'
                )
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rule Id'
                )
            ->addColumn(
                'from_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'From Time'
                )
            ->addColumn(
                'to_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'To time'
                )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer Group Id'
                )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product Id'
                )
            ->addColumn(
                'action_operator',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['default' => 'to_fixed'],
                'Action Operator'
                )
            ->addColumn(
                'action_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000'],
                'Action Amount'
                )
            ->addColumn(
                'action_stop',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Action Stop'
                )
            ->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
                )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Website Id'
                )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
                )
            ->addColumn(
                'sub_simple_action',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                [],
                'Simple Action For Subitems'
                )
            ->addColumn(
                'sub_discount_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000'],
                'Discount Amount For Subitems'
                )
            ->addIndex(
                $installer->getIdxName(
                    'catalogrule_product',
                    ['rule_id', 'from_time', 'to_time', 'website_id', 'store_id', 'customer_group_id', 'product_id', 'sort_order'],
                    true
                    ),
                ['rule_id', 'from_time', 'to_time', 'website_id', 'store_id' , 'customer_group_id', 'product_id', 'sort_order'],
                ['type' => 'unique']
                )
            ->addIndex(
                $installer->getIdxName('catalogrule_product', ['customer_group_id']),
                ['customer_group_id']
                )
            ->addIndex(
                $installer->getIdxName('catalogrule_product', ['website_id']),
                ['website_id']
                )
            ->addIndex(
                $installer->getIdxName('catalogrule_product', ['store_id']),
                ['store_id']
                )
            ->addIndex(
                $installer->getIdxName('catalogrule_product', ['from_time']),
                ['from_time']
                )
            ->addIndex(
                $installer->getIdxName('catalogrule_product', ['to_time']),
                ['to_time']
                )
            ->addIndex(
                $installer->getIdxName('catalogrule_product', ['product_id']),
                ['product_id']
                )
            ->setComment('Bss CatalogRule Product');

            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'bss_catalogrule_product_price'
         */
        if ($installer->getConnection()->isTableExists($installer->getTable('bss_catalogrule_product_price')) != true) {
            $table = $installer->getConnection()
            ->newTable($installer->getTable('bss_catalogrule_product_price'))
            ->addColumn(
                'rule_product_price_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Product PriceId'
                )
            ->addColumn(
                'rule_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable' => false],
                'Rule Date'
                )
            ->addColumn(
                'customer_group_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Customer Group Id'
                )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product Id'
                )
            ->addColumn(
                'rule_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000'],
                'Rule Price'
                )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Website Id'
                )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
                )
            ->addColumn(
                'latest_start_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [],
                'Latest StartDate'
                )
            ->addColumn(
                'earliest_end_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                [],
                'Earliest EndDate'
                )
            ->addIndex(
                $installer->getIdxName(
                    'catalogrule_product_price_store',
                    ['rule_date', 'website_id', 'store_id' , 'customer_group_id', 'product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                ['rule_date', 'website_id', 'store_id' , 'customer_group_id', 'product_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )
            ->addIndex(
                $installer->getIdxName('catalogrule_product_price', ['customer_group_id']),
                ['customer_group_id']
                )
            ->addIndex(
                $installer->getIdxName('catalogrule_product_price', ['website_id']),
                ['website_id']
                )
            ->addIndex(
                $installer->getIdxName('catalogrule_product_price', ['store_id']),
                ['store_id']
                )
            ->addIndex(
                $installer->getIdxName('catalogrule_product_price', ['product_id']),
                ['product_id']
                )
            ->setComment('CatalogRule Product Price');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();

    }
}
