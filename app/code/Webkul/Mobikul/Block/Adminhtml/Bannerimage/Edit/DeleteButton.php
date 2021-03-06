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
    * Class DeleteButton.
    */
    class DeleteButton extends GenericButton implements ButtonProviderInterface     {
        /**
        * @return array
        */
        public function getButtonData()     {
            $bannnerimageId = $this->getBannnerimageId();
            $data = [];
            if ($bannnerimageId) {
                $data = [
                    "label"          => __("Delete Bannner"),
                    "class"          => "delete",
                    "id"             => "banner-edit-delete-button",
                    "data_attribute" => ["url"=>$this->getDeleteUrl()],
                    "on_click"       => "",
                    "sort_order"     => 20
                ];
            }
            return $data;
        }

        /**
        * @return string
        */
        public function getDeleteUrl()  {
            return $this->getUrl("*/*/delete", ["id"=>$this->getBannnerimageId()]);
        }

    }