<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpfreeshipping
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpfreeshipping\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var ControllersRepository
     */
    private $controllersRepository;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory  $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        ControllersRepository $controllersRepository,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->controllersRepository = $controllersRepository;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $attributeCodes = [
            ['value' => 'mp_freeshipping_amount', 'label' => __('Free Shipping Amount')],
        ];
        foreach ($attributeCodes as $code) {
            $frontendClass = '';
            if ($code['value'] === 'account_id') {
                $frontendClass = 'validate-number validate-zero-or-greater';
            }
            $customerSetup->addAttribute(
                Customer::ENTITY,
                $code['value'],
                [
                    'type' => 'varchar',
                    'label' => $code['label'],
                    'input' => 'text',
                    'frontend_class' => $frontendClass,
                    'required' => false,
                    'visible' => false,
                    'user_defined' => true,
                    'sort_order' => 1000,
                    'position' => 1000,
                    'system' => 0,
                ]
            );

            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $code['value'])
            ->addData(
                [
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => [],
                ]
            );

            $attribute->save();
        }

        /**
         * insert sellerstorepickup controller's data
         */
        $data = [];
        
        if (!count($this->controllersRepository->getByPath('freeshipping/shipping/view'))) {
            $data[] = [
                'module_name' => 'Webkul_Mpfreeshipping',
                'controller_path' => 'freeshipping/shipping/view',
                'label' => 'Manage Free Shipping',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        $setup->getConnection()
            ->insertMultiple($setup->getTable('marketplace_controller_list'), $data);
        
        $setup->endSetup();
    }
}
