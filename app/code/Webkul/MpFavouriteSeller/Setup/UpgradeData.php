<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpFavouriteSeller
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpFavouriteSeller\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ControllersRepository
     */
    private $controllersRepository;

    /**
     * @param ControllersRepository $controllersRepository
     * @param EavSetupFactory       $eavSetupFactory
     */
    public function __construct(
        ControllersRepository $controllersRepository
    ) {
        $this->controllersRepository = $controllersRepository;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /**
         * insert sellerstorepickup controller's data
         */
        $data = [];
        
        if (!count($this->controllersRepository->getByPath('mpfavouriteseller/shopfollower/index'))) {
            $data[] = [
                'module_name' => 'Webkul_MpFavouriteSeller',
                'controller_path' => 'mpfavouriteseller/shopfollower/index',
                'label' => 'Shop Followers',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        $setup->getConnection()
            ->insertMultiple($setup->getTable('marketplace_controller_list'), $data);

        $setup->endSetup();
    }
}
