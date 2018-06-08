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

    class Save extends \Webkul\Mobikul\Controller\Adminhtml\Notification    {

        public function execute()   {
            $returnToEdit        = false;
            $originalRequestData = $this->getRequest()->getPostValue();
            $notificationId      = isset($originalRequestData["mobikul_notification"]["id"]) ? $originalRequestData["mobikul_notification"]["id"] : null;
            if ($originalRequestData) {
                try {
                    $notificationData = $originalRequestData["mobikul_notification"];
                    $notificationData["filename"] = $this->getNotificationImageName($notificationData);
                    $notificationData["store_id"] = $this->getNotificationStoreId($notificationData);
                    $request = $this->getRequest();
                    $isExistingNotification = (bool) $notificationId;
                    $notification = $this->notificationDataFactory->create();
                    if ($isExistingNotification) {
                        $currentNotification = $this->_notificationRepository->getById($notificationId);
                        $notificationData["id"] = $notificationId;
                    }
                    $notificationData["updated_at"] = $this->_date->gmtDate();
                    if (!$isExistingNotification)
                        $notificationData["created_at"] = $this->_date->gmtDate();
                    $notification->setData($notificationData);
// Save notification ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    if ($isExistingNotification)
                        $this->_notificationRepository->save($notification);
                    else {
                        $notification = $this->_notificationRepository->save($notification);
                        $notificationId = $notification->getId();
                    }
                    $this->_getSession()->unsNotificationFormData();
// Done Saving notification, finish save action /////////////////////////////////////////////////////////////////////////////////
                    $this->_coreRegistry->register(RegistryConstants::CURRENT_NOTIFICATION_ID, $notificationId);
                    $this->messageManager->addSuccess(__("You saved the notification."));
                    $returnToEdit = (bool) $this->getRequest()->getParam("back", false);
                } catch (\Magento\Framework\Validator\Exception $exception) {
                    $messages = $exception->getMessages();
                    if (empty($messages))
                        $messages = $exception->getMessage();
                    $this->_addSessionErrorMessages($messages);
                    $this->_getSession()->setNotificationFormData($originalRequestData);
                    $returnToEdit = true;
                } catch (\Exception $exception) {
                    $this->messageManager->addException($exception, __("Something went wrong while saving the notification. %1", $exception->getMessage()));
                    $this->_getSession()->setNotificationFormData($originalRequestData);
                    $returnToEdit = true;
                }
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            if ($returnToEdit) {
                if ($notificationId)
                    $resultRedirect->setPath("mobikul/notification/edit", ["id"=>$notificationId, "_current"=>true]);
                else
                    $resultRedirect->setPath("mobikul/notification/new", ["_current"=>true]);
            } else
                $resultRedirect->setPath("mobikul/notification/index");
            return $resultRedirect;
        }

        private function getNotificationImageName($notificationData)    {
            if (isset($notificationData["filename"][0]["name"])) {
                if (isset($notificationData["filename"][0]["name"]))
                    return $notificationData["filename"] = $notificationData["filename"][0]["name"];
                else
                    throw new \Magento\Framework\Exception\LocalizedException(__("Please upload notification image."));
            } else
                throw new \Magento\Framework\Exception\LocalizedException(__("Please upload notification image."));
        }

        private function getNotificationStoreId($notificationData)  {
            if (isset($notificationData["store_id"]))
                return $notificationData["store_id"] = implode(",", $notificationData["store_id"]);
            else
                return $notificationData["store_id"] = 0;
        }

    }