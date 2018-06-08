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
    * Class DeleteButton.
    */
    class DeleteButton extends GenericButton implements ButtonProviderInterface     {
        /**
        * @return array
        */
        public function getButtonData()     {
            $bannnerimageId = $this->getFeaturedcategoriesId();
            $data = [];
            if ($bannnerimageId) {
                $data = [
                    "label"          => __("Delete Featured Category"),
                    "class"          => "delete",
                    "id"             => "featuredcategories-edit-delete-button",
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
            return $this->getUrl("*/*/delete", ["id" => $this->getFeaturedcategoriesId()]);
        }

    }