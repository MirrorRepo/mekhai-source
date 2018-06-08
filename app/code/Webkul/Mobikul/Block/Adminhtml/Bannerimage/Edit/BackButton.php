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

    namespace Webkul\Mobikul\Block\Adminhtml\Bannerimage\Edit;

    use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

    /**
    * Class BackButton
    */
    class BackButton extends GenericButton implements ButtonProviderInterface   {
        /**
        * @return array
        */
        public function getButtonData() {
            return [
                "label"      => __("Back"),
                "on_click"   => sprintf("location.href = '%s';", $this->getBackUrl()),
                "class"      => "back",
                "sort_order" => 10
            ];
        }

        /**
        * Get URL for back (reset) button
        *
        * @return string
        */
        public function getBackUrl()        {
            return $this->getUrl("*/*/");
        }

    }