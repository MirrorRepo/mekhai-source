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

    namespace Webkul\Mobikul\Block\Sales\Order\Invoice;

    class Totals extends \Magento\Sales\Block\Order\Invoice\Totals     {

        public function _initTotals()   {
            parent::_initTotals();
            $this->removeTotal("base_grandtotal");
            return $this;
        }

    }