<?php
    /**
    * Webkul Software.
    *
    * @category  Webkul
    * @package   Webkul_Mobikul
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Block\Sales\Order\Creditmemo;

    class Totals extends \Magento\Sales\Block\Order\Creditmemo\Totals     {

        public function _initTotals()    {
            parent::_initTotals();
            $this->removeTotal("base_grandtotal");
            if ((double)$this->getSource()->getAdjustmentPositive()) {
                $total = new \Magento\Framework\DataObject(
                    [
                        "code"  => "adjustment_positive",
                        "value" => $this->getSource()->getAdjustmentPositive(),
                        "label" => __("Adjustment Refund")
                    ]
                );
                $this->addTotal($total);
            }
            if ((double)$this->getSource()->getAdjustmentNegative()) {
                $total = new \Magento\Framework\DataObject(
                    [
                        "code"  => "adjustment_negative",
                        "value" => $this->getSource()->getAdjustmentNegative(),
                        "label" => __("Adjustment Fee")
                    ]
                );
                $this->addTotal($total);
            }
            return $this;
        }

    }