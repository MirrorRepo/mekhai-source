<?php

namespace Mbattain\PriceDecimal\Model\Plugin;

class OrderPlugin
{

    public function __construct(
        \Mbattain\PriceDecimal\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    /**
     * @param \Magento\Sales\Model\Order $subject
     * @param array ...$args
     * @return array
     */
    public function beforeFormatPricePrecision(
        \Magento\Sales\Model\Order $subject,
        ...$args
    ) {
        //is enabled


            //change the precision
            $args[1] = $this->helper->getPricePrecision($subject->getOrderCurrencyCode());

        return $args;
    }
}
