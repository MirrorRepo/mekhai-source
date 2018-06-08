<?php
    /**
    * Webkul Software.
    *
    * @category  Webkul
    * @package   Webkul_Pos
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Setup;
    use Magento\Framework\Setup\ModuleContextInterface;
    use Magento\Framework\Setup\UpgradeSchemaInterface;
    use Magento\Framework\Setup\SchemaSetupInterface;

    class UpgradeSchema implements UpgradeSchemaInterface   {

        public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)   {
            $textType = \Magento\Framework\DB\Ddl\Table::TYPE_TEXT;
            $setup->startSetup();
            $setup->getConnection()->addColumn(
                $setup->getTable("mobikul_devicetoken"),
                "email", [
                    "type"     => $textType,
                    "unsigned" => true,
                    "nullable" => true,
                    "default"  => null,
                    "comment"  => "Email for guest user"
                ]
            );
            $setup->endSetup();
        }

    }