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

    namespace Webkul\Mobikul\Block\Adminhtml\Edit\Featuredcategories;

    use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
    use Webkul\Mobikul\Block\Adminhtml\Edit\GenericButton;

    /**
    * Class SaveButton.
    */
    class SaveButton extends GenericButton implements ButtonProviderInterface   {
        /**
        * @return array
        */
        public function getButtonData()     {
            $data = [
                "label"          => __("Save Featured Category"),
                "class"          => "save primary",
                "data_attribute" => [
                    "mage-init"  => ["button"=>["event"=>"save"]],
                    "form-role"  => "save"
                ],
                "sort_order"     => 90
            ];
            return $data;
        }

    }