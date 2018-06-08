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
    use Magento\Framework\Controller\ResultFactory;
    use Webkul\Mobikul\Controller\RegistryConstants;
    use Webkul\Mobikul\Api\Data\NotificationInterface;

    class Delete extends \Webkul\Mobikul\Controller\Adminhtml\Notification      {

        public function execute()   {
            $resultRedirect = $this->resultRedirectFactory->create();
            $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
            $isPost = $this->getRequest()->isPost();
            if (!$formKeyIsValid || !$isPost) {
                $this->messageManager->addError(__("Notification could not be deleted."));
                return $resultRedirect->setPath("mobikul/notification/index");
            }
            $notificationId = $this->initCurrentNotification();
            if (!empty($notificationId)) {
                try {
                    $this->_notificationRepository->deleteById($notificationId);
                    $this->messageManager->addSuccess(__("Notification has been deleted."));
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
            return $resultRedirect->setPath("mobikul/notification/index");
        }

        protected function initCurrentNotification()    {
            $notificationId = (int)$this->getRequest()->getParam("id");
            if ($notificationId)
                $this->_coreRegistry->register(RegistryConstants::CURRENT_NOTIFICATION_ID, $notificationId);
            return $notificationId;
        }

    }