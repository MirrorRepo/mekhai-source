<?php

namespace Mbattain\PriceDecimal\Model\Plugin;
class PriceCurrency
{
//
//    public function __construct(
//        \Mbattain\PriceDecimal\Helper\Data $helper
//    ) {
//        $this->helper = $helper;
//    }
//    /**
//     * {@inheritdoc}
//     */
//    public function beforeFormat(
//        \Magento\Directory\Model\PriceCurrency $subject,
//        ...$args
//    ) {
//
//
//            // add the optional arg
//            if (!isset($args[1])) {
//                $args[1] = true;
//            }
//            // Precision argument
//            $args[2] = $this->helper->getPricePrecision();
//
//        return $args;
//    }
//
//    /**
//     * @param \Magento\Directory\Model\PriceCurrency $subject
//     * @param callable $proceed
//     * @param $price
//     * @param array ...$args
//     * @return float
//     */
//    public function aroundRound(
//        \Magento\Directory\Model\PriceCurrency $subject,
//        callable $proceed,
//        $price,
//        ...$args
//    ) {
//            return round($price, $this->helper->getPricePrecision());
//
//
//    }
//
//    /**
//     * @param \Magento\Directory\Model\PriceCurrency $subject
//     * @param array ...$args
//     * @return array
//     */
//    public function beforeConvertAndFormat(
//        \Magento\Directory\Model\PriceCurrency $subject,
//        ...$args
//    ) {
//            // add the optional args
//            $args[1] = isset($args[1])? $args[1] : null;
//            $args[2] = isset($args[2])? $args[2] : null;
//            $args[3] = $this->helper->getPricePrecision();
//
//        return $args;
//    }
//
//    /**
//     * @param \Magento\Directory\Model\PriceCurrency $subject
//     * @param array ...$args
//     * @return array
//     */
//    public function beforeConvertAndRound(
//        \Magento\Directory\Model\PriceCurrency $subject,
//        ...$args
//    ) {
//            //add optional args
//            $args[1] = isset($args[1])? $args[1] : null;
//            $args[2] = isset($args[2])? $args[2] : null;
//            $args[3] = $this->helper->getPricePrecision();
//
//        return $args;
//    }



}
