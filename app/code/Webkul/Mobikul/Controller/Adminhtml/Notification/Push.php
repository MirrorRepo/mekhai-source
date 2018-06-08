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

    namespace Webkul\Mobikul\Controller\Adminhtml\Notification;
    use Magento\Backend\App\Action\Context;
    use Webkul\Mobikul\Api\NotificationRepositoryInterface;

    class Push extends \Webkul\Mobikul\Controller\Adminhtml\Notification  {

        protected $_notificationRepository;

        public function execute()   {
            $resultRedirect = $this->resultRedirectFactory->create();
            $notificationId = $this->initCurrentNotification();
            if (!empty($notificationId)) {
                $model        = $this->_notificationRepository->getById($notificationId);
                $baseTmpPath  = "mobikul/notification/";
                $storeManager = $this->_objectManager->create("\Magento\Store\Model\StoreManagerInterface");
                $target       = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$baseTmpPath;
                $bannerUrl    = "";
                if ($model->getFilename() != "")
                    $bannerUrl = $target.$model->getFilename();
                $message = [
                    "id"               => $model->getId(),
                    "body"             => $model->getContent(),
                    "title"            => $model->getTitle(),
                    "sound"            => "default",
                    "message"          => $model->getContent(),
                    "store_id"         => $model->getStoreId(),
                    "banner_url"       => $bannerUrl,
                    "notificationType" => $model->getType()
                ];
                if ($model->getType() == "category" && $model->getProCatId() != "") {
// for category /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    $message["categoryName"] = $this->_objectManager->get("Magento\Catalog\Model\ResourceModel\Category")->getAttributeRawValue($model->getProCatId(), "name", 1);
                    $message["categoryId"] = $model->getProCatId();
                } elseif ($model->getType() == "product" && $model->getProCatId() != "") {
// for product //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    $message["productName"] = $this->_objectManager->get("Magento\Catalog\Model\ResourceModel\Product")->getAttributeRawValue($model->getProCatId(), "name", 1);
                    $message["productId"] = $model->getProCatId();
                }
                $url = "https://fcm.googleapis.com/fcm/send";
                $authKey = $this->_objectManager->get("\Webkul\Mobikul\Helper\Data")->getConfigData("mobikul/notification/apikey");
                $headers = [
                    "Authorization : key=".$authKey,
                    "Content-Type  : application/json"
                ];
                $jsonHelper = $this->_objectManager->create("\Magento\Framework\Json\Helper\Data");
                $topic      = $this->_objectManager->get("\Webkul\Mobikul\Helper\Data")->getConfigData("mobikul/notification/topic");
                $fields     = [
                    "to"                => "/topics/".$topic,
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
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonHelper->jsonEncode($fields));
                $result = curl_exec($ch);
                curl_close($ch);
                if($this->isJson($result))
                    $this->messageManager->addSuccess(__("Notification pushed successfully."));
                else
                    $this->messageManager->addError(__("Sorry something went wrong."));
            }
            else{
                $this->messageManager->addSuccess(__("Invalid Notification."));
            }
            return $resultRedirect->setPath("mobikul/notification/edit", ["id"=>$this->initCurrentNotification()]);
        }

        public function isJson($string) {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }

        protected function initCurrentNotification()    {
            return (int)$this->getRequest()->getParam("id");
        }

    }