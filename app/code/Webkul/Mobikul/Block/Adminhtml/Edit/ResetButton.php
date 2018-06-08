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

    namespace Webkul\Mobikul\Block\Adminhtml\Edit;

    use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

    /**
    * Class ResetButton
    */
    class ResetButton implements ButtonProviderInterface    {
        /**
        * @return array
        */
        public function getButtonData()     {
            return [
                "label"      => __("Reset"),
                "class"      => "reset",
                "on_click"   => "location.reload();",
                "sort_order" => 30
            ];
        }

    }