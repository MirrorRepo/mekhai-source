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

    namespace Webkul\Mobikul\Observer;
    use Magento\Framework\Event\ObserverInterface;

    class OrderStatusNotification implements ObserverInterface     {

        protected $_helper;
        protected $_jsonHelper;
        protected $_deviceToken;

        public function __construct(
            \Webkul\Mobikul\Helper\Data $helper,
            \Webkul\Mobikul\Model\DeviceToken $deviceToken,
            \Magento\Framework\Json\Helper\Data $jsonHelper
        ) {
            $this->_helper      = $helper;
            $this->_jsonHelper  = $jsonHelper;
            $this->_deviceToken = $deviceToken;
        }

        public function execute(\Magento\Framework\Event\Observer $observer)    {
            $order = $observer->getOrder();
            if ($order->getState() != "") {
                $canReorder = 0;
                if($this->_helper->canReorder($order) == 1)
                    $canReorder = $this->_helper->canReorder($order);
                $message = [
                    "id"               => $order->getId(),
                    "body"             => __("Your order status changed to ").$order->getStatusLabel(),
                    "title"            => __("Order Status Changed!!"),
                    "sound"            => "default",
                    "message"          => __("Your order status changed to ").$order->getStatusLabel(),
                    "canReorder"       => $canReorder,
                    "incrementId"      => $order->getIncrementId(),
                    "notificationType" => "order"
                ];
                if($order->getState() == "new") {
                    $message["title"]   = __("Order Placed Successfully!!");
                    $message["message"] = __("Your order status is ").$order->getStatusLabel();
                }
                $url     = "https://fcm.googleapis.com/fcm/send";
                $authKey = $this->_helper->getConfigData("mobikul/notification/apikey");
                $headers = [
                    "Authorization: key=".$authKey,
                    "Content-Type: application/json",
                ];
                if($authKey != "")  {
                    $customerId          = 0;
                    if(!$order->getCustomerIsGuest())
                        $tokenCollection = $this->_deviceToken->getCollection()->addFieldToFilter("customer_id", $order->getCustomerId());
                    else
                        $tokenCollection = $this->_deviceToken->getCollection()->addFieldToFilter("email", $order->getCustomerEmail());
                    foreach ($tokenCollection as $eachToken) {
                        $fields = [
                            "to"                => $eachToken->getToken(),
                            "data"              => $message,
                            "priority"          => "high",
                            "notification"      => $message,
                            "time_to_live"      => 30,
                            "delay_while_idle"  => true,
                            "content_available" => true
                        ];
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_jsonHelper->jsonEncode($fields));
                        $result = curl_exec($ch);
                        curl_close($ch);
                        if($this->isJson($result)){
                            $result = $this->_jsonHelper->jsonDecode($result);
                            if($result["success"] == 0 && $result["failure"] == 1)
                                $eachToken->delete();
                        }
                    }
                }
            }
        }

        public function isJson($string) {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }

    }