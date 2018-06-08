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

    namespace Webkul\Mobikul\Block\Adminhtml\Edit\Notification;
    use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
    use Webkul\Mobikul\Block\Adminhtml\Edit\GenericButton;

    class DeleteButton extends GenericButton implements ButtonProviderInterface     {

        public function getButtonData()     {
            $bannnerimageId = $this->getNotificationId();
            $data = [];
            if ($bannnerimageId) {
                $data = [
                    "label"          => __("Delete Notification"),
                    "class"          => "delete",
                    "id"             => "notification-edit-delete-button",
                    "data_attribute" => ["url" => $this->getDeleteUrl()],
                    "on_click"       => "",
                    "sort_order"     => 20
                ];
            }
            return $data;
        }

        public function getDeleteUrl()  {
            return $this->getUrl("*/*/delete", ["id" => $this->getNotificationId()]);
        }

    }