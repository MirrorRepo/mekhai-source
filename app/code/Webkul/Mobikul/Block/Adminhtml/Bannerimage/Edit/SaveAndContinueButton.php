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
    * Class SaveAndContinueButton.
    */
    class SaveAndContinueButton extends GenericButton implements ButtonProviderInterface    {
        /**
        * @return array
        */
        public function getButtonData()     {
            $data = [
                "label"          => __("Save and Continue Edit"),
                "class"          => "save",
                "data_attribute" => [
                    "mage-init"  => ["button"=>["event"=>"saveAndContinueEdit"]],
                ],
                "sort_order"     => 80,
            ];
            return $data;
        }

    }
