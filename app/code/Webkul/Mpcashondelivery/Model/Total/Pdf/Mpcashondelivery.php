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
/**
 * mpcashondelivery amount modification block.
 */
namespace Webkul\Mpcashondelivery\Model\Total\Pdf;

class Mpcashondelivery extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    /**
     * Initialize all order totals.
     */
    public function getTotalsForDisplay()
    {
        $this->_order = $this->getOrder();
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $title = 'Cash On delivery';
        $totals = [];
        $info = [];
        if ($this->_order->getPayment()->getMethod() == 'mpcashondelivery') {
            $info[] = [
                'amount' => $this->getAmountPrefix().$this->getOrder()->formatPriceTxt($this->getAmount()),
                'label' => __($title).': ',
                'font_size' => $fontSize,
            ];
            $totals = $info;
        }

        return $totals;
    }
}
