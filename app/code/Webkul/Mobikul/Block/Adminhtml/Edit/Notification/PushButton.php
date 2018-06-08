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

    class PushButton extends GenericButton implements ButtonProviderInterface   {

        public function getButtonData()     {
            $notificationId = $this->getNotificationId();
            $data = [];
            if ($notificationId) {
                $data = [
                    "label"          => __("Push Notification"),
                    "class"          => "save primary",
                    "id"             => "notification-push-button",
                    "data_attribute" => ["url"=>$this->getPushUrl()],
                    "on_click"       => "location.href = '".$this->getPushUrl()."'",
                    "sort_order"     => 90
                ];
            }
            return $data;
        }

        public function getPushUrl()  {
            return $this->getUrl("*/*/push", ["id"=>$this->getNotificationId()]);
        }

    }