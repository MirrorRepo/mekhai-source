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
    use Webkul\Mobikul\Controller\RegistryConstants;
    use Webkul\Mobikul\Api\Data\NotificationInterface;
    use Magento\Framework\Exception\NoSuchEntityException;

    class Edit extends \Webkul\Mobikul\Controller\Adminhtml\Notification    {

        public function execute()   {
            $notificationId         = $this->initCurrentNotification();
            $isExistingNotification = (bool)$notificationId;
            if ($isExistingNotification) {
                try {
                    $mobikulDirPath      = $this->_mediaDirectory->getAbsolutePath("mobikul");
                    $notificationDirPath = $this->_mediaDirectory->getAbsolutePath("mobikul/notification");
                    if (!file_exists($mobikulDirPath))
                        mkdir($mobikulDirPath, 0777, true);
                    if (!file_exists($notificationDirPath))
                        mkdir($notificationDirPath, 0777, true);
                    $baseTmpPath      = "mobikul/notification/";
                    $target           = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$baseTmpPath;
                    $notificationData = [];
                    $notificationData["mobikul_notification"] = [];
                    $notification     = null;
                    $notification     = $this->_notificationRepository->getById($notificationId);
                    $result           = $notification->getData();
                    if (count($result)) {
                        $notificationData["mobikul_notification"] = $result;
                        $notificationData["mobikul_notification"]["filename"] = [];
                        $notificationData["mobikul_notification"]["filename"][0] = [];
                        $notificationData["mobikul_notification"]["filename"][0]["name"] = $result["filename"];
                        $notificationData["mobikul_notification"]["filename"][0]["url"] =
                        $target.$result["filename"];
                        $filePath = $this->_mediaDirectory->getAbsolutePath($baseTmpPath).$result["filename"];
                        if (file_exists($filePath)) {
                            $notificationData["mobikul_notification"]["filename"][0]["size"] =
                            filesize($filePath);
                        } else
                            $notificationData["mobikul_notification"]["filename"][0]["size"] = 0;
                        $notificationData["mobikul_notification"][NotificationInterface::ID] = $notificationId;
                        $this->_getSession()->setNotificationFormData($notificationData);
                    } else {
                        $this->messageManager->addError(__("Requested notification doesn't exist"));
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath("mobikul/notification/index");
                        return $resultRedirect;
                    }
                } catch (NoSuchEntityException $e) {
                    $this->messageManager->addException($e, __("Something went wrong while editing the notification."));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath("mobikul/notification/index");
                    return $resultRedirect;
                }
            }
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->setActiveMenu("Webkul_Mobikul::notification");
            $this->prepareDefaultNotificationTitle($resultPage);
            $resultPage->setActiveMenu("Webkul_Mobikul::notification");
            if ($isExistingNotification)
                $resultPage->getConfig()->getTitle()->prepend(__("Edit Item with id %1", $notificationId));
            else
                $resultPage->getConfig()->getTitle()->prepend(__("New Notification"));
            return $resultPage;
        }

        protected function initCurrentNotification()    {
            $notificationId = (int)$this->getRequest()->getParam("id");
            if ($notificationId)
                $this->_coreRegistry->register(RegistryConstants::CURRENT_NOTIFICATION_ID, $notificationId);
            return $notificationId;
        }

        protected function prepareDefaultNotificationTitle(\Magento\Backend\Model\View\Result\Page $resultPage) {
            $resultPage->getConfig()->getTitle()->prepend(__("Notification"));
        }

    }