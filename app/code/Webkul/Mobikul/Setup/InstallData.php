<?php
    /**
    * @category   Webkul
    * @package    Webkul_MobileLogin
    * @author     Webkul Software Private Limited
    * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license    https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Setup;
    use Magento\Eav\Setup\EavSetupFactory;
    use Magento\Framework\Setup\InstallDataInterface;
    use Magento\Framework\Setup\ModuleContextInterface;
    use Magento\Framework\Setup\ModuleDataSetupInterface;
    use Magento\Eav\Model\Config;
    use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
    use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;

    class InstallData implements InstallDataInterface   {

        protected $_eavConfig;
        protected $_attributeSet;
        protected $_eavSetupFactory;
        protected $_resourceProduct;

        public function __construct(
            Config $eavConfig,
            AttributeSet $attributeSet,
            ResourceProduct $resourceProduct,
            EavSetupFactory $eavSetupFactory
        ) {
            $this->_eavConfig       = $eavConfig;
            $this->_attributeSet    = $attributeSet;
            $this->_eavSetupFactory = $eavSetupFactory;
            $this->_resourceProduct = $resourceProduct;
        }

        public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
            $eavSetup = $this->_eavSetupFactory->create(["setup"=>$setup]);
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "as_featured");
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, "as_featured", [
                "group"                      => "Product Details",
                "used_in_product_listing"    => true,
                "filterable"                 => false,
                "input"                      => "boolean",
                "label"                      => "Is featured for Mobikul ?",
                "global"                     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                "comparable"                 => false,
                "searchable"                 => false,
                "user_defined"               => true,
                "visible_on_front"           => false,
                "visible_in_advanced_search" => false,
                "is_html_allowed_on_front"   => false,
                "required"                   => false,
                "unique"                     => false,
                "is_configurable"            => false
            ]);
            $entityType = $this->_resourceProduct->getEntityType();
            $attributeSetCollection = $this->_attributeSet->setEntityTypeFilter($entityType);
            foreach ($attributeSetCollection as $attributeSet)
                $eavSetup->addAttributeToSet("catalog_product", $attributeSet->getAttributeSetName(), "General", "as_featured");



            // $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
            // $eavSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, "mobilenumber");
            // $eavSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, "mobilenumber",[
            //     "type"     => "varchar",
            //     "backend"  => "",
            //     "label"    => "Mobile Number",
            //     "input"    => "text",
            //     "source"   => "",
            //     "visible"  => true,
            //     "required" => true,
            //     "default"  => "",
            //     "frontend" => "",
            //     "unique"   => true,
            //     "note"     => ""
            // ]);
            // $customAttribute = $this->_eavConfig->getAttribute(\Magento\Customer\Model\Customer::ENTITY, "mobilenumber");
            // $customAttribute->setData(
            //     "used_in_forms",
            //     ["adminhtml_customer_address", "customer_address_edit", "customer_register_address"]
            // );
            // $customAttribute->save();
        }

    }