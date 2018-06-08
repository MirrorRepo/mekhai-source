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

namespace Webkul\Mpcashondelivery\Observer;

use Magento\Framework\Event\ObserverInterface;

class Quotesubmitbefore implements ObserverInterface
{
    /**
     * quote submit before handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $codAmount = $observer->getQuote()->getMpcashondelivery();
        $order = $observer->getOrder();
        $baseCodAmount = $observer->getQuote()->getBaseMpcashondelivery();
        $order->setMpcashondelivery($codAmount);
        $order->setBaseMpcashondelivery($baseCodAmount);

        $orderShippingAddressData = $order->getShippingAddress();
        if (!empty($orderShippingAddressData)) {
            $orderAddress = $order->getShippingAddress();
            $orderAddress->setMpcashondelivery($codAmount);
            $orderAddress->setBaseMpcashondelivery($baseCodAmount);
        } else {
            $orderBillingAddress = $order->getBillingAddress();
            $orderBillingAddress->setMpcashondelivery($codAmount);
            $orderBillingAddress->setBaseMpcashondelivery($baseCodAmount);
        }
    }
}
