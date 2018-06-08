<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpsplitcart
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpsplitcart\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * Upgrade Data script
 *
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Customer setup factory
     *
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    private $attributeSetFactory;

    /**
     * @param ControllersRepository $controllersRepository
     */
    public function __construct(
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $customerSetup = $this->customerSetupFactory->create(
            ['setup' => $setup]
        );

        $customerSetup->removeAttribute(Customer::ENTITY, 'virtual_cart');

        $customerEntity = $customerSetup->getEavConfig()
            ->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /**
         * @var $attributeSet AttributeSet
         */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'virtual_cart',
            [
                'type'         => 'text',
                'label'        => 'Virtual Cart',
                'input'        => 'text',
                'required'     => false,
                'visible'      => false,
                'user_defined' => true,
                'sort_order'   => 1000,
                'position'     => 1000,
                'system'       => false,
                'global'       => true,
                'default'      => '0'
            ]
        );

        //add attribute to attribute set
        $attribute = $customerSetup->getEavConfig()
            ->getAttribute(Customer::ENTITY, 'virtual_cart')
            ->addData(
                [
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['adminhtml_customer'],
                ]
            );

        $attribute->save();
    }
}
