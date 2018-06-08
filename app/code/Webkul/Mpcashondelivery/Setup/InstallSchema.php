<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpcashondelivery
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Mpcashondelivery\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        /**
         * Create table 'marketplace_mpcashondelivery'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('marketplace_mpcashondelivery'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Website ID'
            )
            ->addColumn(
                'dest_country_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => ''],
                'Destination coutry ISO/2 or ISO/3 code'
            )
            ->addColumn(
                'dest_region_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => ''],
                'Destination Region Id'
            )
            ->addColumn(
                'dest_zip_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => ''],
                'Destination Post Code (Zip)'
            )
            ->addColumn(
                'dest_zip_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => ''],
                'Destination Post Code (Zip)'
            )
            ->addColumn(
                'fixed_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000'],
                'Price'
            )
            ->addColumn(
                'percentage_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000'],
                'Percentage Price'
            )
            ->addColumn(
                'price_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Price Type'
            )
            ->addColumn(
                'weight_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000'],
                'weight_from'
            )
            ->addColumn(
                'weight_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000'],
                'Weight to'
            )
            ->addColumn(
                'seller_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Seller ID'
            )
            ->addColumn(
                'is_range',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Is range'
            )
            ->addColumn(
                'zipcode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => ''],
                'zipcode'
            )
            
            ->setComment('Marketplace Cash on Delivery Payment Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'marketplace_mpcashondelivery_order'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('marketplace_mpcashondelivery_order'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Order ID'
            )
            ->addColumn(
                'item_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => ''],
                'Item Ids'
            )
            ->addColumn(
                'seller_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Seller ID'
            )
            ->addColumn(
                'shipment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Shipment ID'
            )
            ->addColumn(
                'invoice_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Invoice ID'
            )
            ->addColumn(
                'creditmemo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Credit memo ID'
            )
            ->addColumn(
                'is_canceled',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Is Canceled'
            )
            ->addColumn(
                'shipping_charges',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000'],
                'Shipping Charges'
            )
            ->addColumn(
                'cod_charges',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => true, 'nullable' => false, 'default' => '0.0000'],
                'Cod Charges'
            )
            ->addColumn(
                'carrier_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => ''],
                'Carrier Name'
            )
            ->addColumn(
                'tracking_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => ''],
                'Tracking Number'
            )
            
            ->setComment('Marketplace Cash on Delivery Payment Table');
        $installer->getConnection()->createTable($table);

        // add columns to existing tables
        $installer->getConnection()->addColumn(
            $setup->getTable('marketplace_saleslist'),
            'cod_charges',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length'=>'12,4',
                'nullable' => false,
                'default' => 0,
                'comment' => 'cod_charges'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('marketplace_saleslist'),
            'collect_cod_status',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => 0,
                'comment' => 'collect_cod_status'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('marketplace_saleslist'),
            'admin_pay_status',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => 0,
                'comment' => 'admin_pay_status'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('marketplace_userdata'),
            'others_info',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default' => '',
                'comment' => 'others_info'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('marketplace_orders'),
            'cod_charges',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'mpcod charges'
            ]
        );

        $installer->getConnection()->addColumn(
            $setup->getTable('quote_address'),
            'mpcashondelivery',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'mpcod charges'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('quote_address'),
            'base_mpcashondelivery',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'mpcod charges'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('quote'),
            'mpcashondelivery',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'mpcod charges'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('quote'),
            'base_mpcashondelivery',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'mpcod charges'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('sales_invoice'),
            'mpcashondelivery',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'mpcod charges'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('sales_invoice'),
            'base_mpcashondelivery',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'mpcod charges'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'mpcashondelivery',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'mpcod charges'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'base_mpcashondelivery',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'mpcod charges'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('sales_order_address'),
            'mpcashondelivery',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'mpcod charges'
            ]
        );
        $installer->getConnection()->addColumn(
            $setup->getTable('sales_order_address'),
            'base_mpcashondelivery',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'nullable' => false,
                'LENGTH' =>'12,4',
                'visible'   => false,
                'required'  => true,
                'comment' => 'mpcod charges'
            ]
        );
        $installer->endSetup();
    }
}
