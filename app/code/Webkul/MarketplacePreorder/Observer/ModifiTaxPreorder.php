<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Observer;


use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;

class ModifiTaxPreorder implements ObserverInterface
{
    public $additionalTaxAmt = 20;

    public function execute(Observer $observer)
    {
        /** @var Magento\Quote\Model\Quote\Address\Total */
        $total = $observer->getData('total');
        // echo "<pre>";
        // print_r($total->getData());
        // die;
        //make sure tax value exist
        // if (count($total->getAppliedTaxes()) > 0) {
            $total->addTotalAmount('tax', $this->additionalTaxAmt);
        // }

        return $this;
    }
}