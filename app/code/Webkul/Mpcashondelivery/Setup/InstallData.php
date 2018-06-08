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
 
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;
 
    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->_eavSetupFactory = $eavSetupFactory;
    }
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'cod_available',
            [
                'label' => 'Cash On Delivery',
                'input' => 'select',
                'group' => 'Product Details',
                'source'    => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'backend'   => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'global'    => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                'visible'   => true,
                'required'  => false,
                'user_defined' => false,
                'apply_to'     => 'simple,configurable,bundle,grouped',
                'visible_on_front' => false,
                'is_configurable'  => false,
                'searchable'        => true,
                'filterable'        => true,
                'comparable'        => true,
                'visible_in_advanced_search' => true,
                'apply_to'     => 'simple,configurable,bundle',
                'note' => 'Cash On Delivery for this product'
            ]
        );
    }
}
