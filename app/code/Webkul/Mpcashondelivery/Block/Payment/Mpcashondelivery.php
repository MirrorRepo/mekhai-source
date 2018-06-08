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

namespace Webkul\Mpcashondelivery\Block\Payment;

/**
 * Block for get message in mp cash on delivery payment method
 */
class Mpcashondelivery extends \Magento\OfflinePayments\Block\Form\AbstractInstruction
{
    /**
     * mp cash on delivery template
     *
     * @var string
     */
    protected $_template = 'payment/mpcashondelivery.phtml';
    
    // get cod message either price exists ir not
    public function getInstructions()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('Webkul\Mpcashondelivery\Helper\Data');
        $appliedRules = $helper->getAppliedPriceRules();
        return $appliedRules['cod_message'];
    }
}
