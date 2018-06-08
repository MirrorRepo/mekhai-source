<?php
    /**
    * Webkul Software.
    *
    * @category  Webkul
    * @package   Webkul_Mobikul
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Setup;
    use Magento\Framework\Setup\InstallSchemaInterface;
    use Magento\Framework\Setup\ModuleContextInterface;
    use Magento\Framework\Setup\SchemaSetupInterface;

    class InstallSchema implements InstallSchemaInterface   {

        public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)   {
            $installer           = $setup;
            $installer->startSetup();
            $timestampType       = \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP;
            $timestampInit       = \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT;
            $timestampInitUpdate = \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE;

// Mobikul Banner Image Table ///////////////////////////////////////////////////////////////////////////////////////////////////
            $table = $installer->getConnection()
                ->newTable($installer->getTable("mobikul_bannerimage"))
                ->addColumn("id", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true], "Id")
                ->addColumn("filename", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ["nullable"=>true, "default"=>null], "File Name")
                ->addColumn("status", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Status")
                ->addColumn("type", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ["nullable"=>true, "default"=>null], "Type")
                ->addColumn("pro_cat_id", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Product Category Id")
                ->addColumn("store_id", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Store ID")
                ->addColumn("sort_order", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Sort Order")
                ->setComment("Mobikul Banner Table");
            $installer->getConnection()->createTable($table);

// Mobikul Notification Table ///////////////////////////////////////////////////////////////////////////////////////////////////
            $table = $installer->getConnection()
                ->newTable($installer->getTable("mobikul_notification"))
                ->addColumn("id", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true], "Id")
                ->addColumn("title", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ["nullable"=>true, "default"=>null], "Title")
                ->addColumn("content", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ["nullable"=>true, "default"=>null], "Content")
                ->addColumn("type", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ["nullable"=>true, "default"=>null], "Type")
                ->addColumn("filename", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ["nullable"=>true, "default"=>null], "File Name")
                ->addColumn("collection_type", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ["nullable"=>true, "default"=>null], "Collection Type")
                ->addColumn("filter_data", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ["nullable"=>true, "default"=>null], "Filter Data")
                ->addColumn("pro_cat_id", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Product Category Id")
                ->addColumn("store_id", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Store ID")
                ->addColumn("status", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Status")
                ->addColumn("created_at", $timestampType, null, ["nullable"=>false, "default"=>$timestampInit], "Creation Time")
                ->addColumn("updated_at", $timestampType, null, ["nullable"=>false, "default"=>$timestampInitUpdate], "Update Time")
                ->setComment("Mobikul Notification Table");
            $installer->getConnection()->createTable($table);

// Mobikul Featured Category Table //////////////////////////////////////////////////////////////////////////////////////////////
            $table = $installer->getConnection()
                ->newTable($installer->getTable("mobikul_featuredcategories"))
                ->addColumn("id", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true], "ID")
                ->addColumn("filename", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ["nullable"=>true, "default"=>null], "File Name")
                ->addColumn("category_id", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Product Category Id")
                ->addColumn("store_id", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Store Id")
                ->addColumn("sort_order", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Sort Order")
                ->addColumn("status", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Status")
                ->setComment("Mobikul Featured Category Table");
            $installer->getConnection()->createTable($table);

// Mobikul User Image Table /////////////////////////////////////////////////////////////////////////////////////////////////////
            $table = $installer->getConnection()
                ->newTable($installer->getTable("mobikul_userimage"))
                ->addColumn("id", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true], "Id")
                ->addColumn("profile", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ["nullable"=>true, "default"=>null], "Profile")
                ->addColumn("banner", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ["nullable"=>true, "default"=>null], "Banner")
                ->addColumn("customer_id", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Customer Id")
                ->addColumn("is_social", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Is Social")
                ->setComment("Mobikul User Image Table");
            $installer->getConnection()->createTable($table);

// Mobikul Category Images Table ////////////////////////////////////////////////////////////////////////////////////////////////
            $table = $installer->getConnection()
                ->newTable($installer->getTable("mobikul_categoryimages"))
                ->addColumn("id", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true], "Id")
                ->addColumn("icon", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ["nullable"=>true, "default"=>null], "Icon")
                ->addColumn("banner", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ["nullable"=>true, "default"=>null], "Banner")
                ->addColumn("category_id", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Category Id")
                ->addColumn("category_name", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ["nullable"=>true, "default"=>null], "Category Name")
                ->setComment("Mobikul Category Images Table");
            $installer->getConnection()->createTable($table);

// Mobikul Category Images Table ////////////////////////////////////////////////////////////////////////////////////////////////
            $table = $installer->getConnection()
                ->newTable($installer->getTable("mobikul_devicetoken"))
                ->addColumn("id", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary" => true], "Id")
                ->addColumn("customer_id", \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Customer Id")
                ->addColumn("token", \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ["nullable"=>true, "default"=>null], "Token")
                ->setComment("Mobikul Device Token Table");
            $installer->getConnection()->createTable($table);

            $installer->endSetup();
        }

    }