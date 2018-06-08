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
    use Magento\Ui\Component\MassAction\Filter;
    use Magento\Framework\Controller\ResultFactory;
    use Webkul\Mobikul\Api\NotificationRepositoryInterface;
    use Webkul\Mobikul\Model\ResourceModel\Notification\CollectionFactory;

    class MassEnable extends \Magento\Backend\App\Action    {

        protected $_date;
        protected $filter;
        protected $collectionFactory;
        protected $_notificationRepository;

        public function __construct(
            Filter $filter,
            Context $context,
            CollectionFactory $collectionFactory,
            \Magento\Framework\Stdlib\DateTime\DateTime $date,
            NotificationRepositoryInterface $notificationRepository
        ) {
            $this->_date                   = $date;
            $this->filter                  = $filter;
            $this->collectionFactory       = $collectionFactory;
            $this->_notificationRepository = $notificationRepository;
            parent::__construct($context);
        }

        public function execute()   {
            $resultRedirect       = $this->resultRedirectFactory->create();
            $collection           = $this->filter->getCollection($this->collectionFactory->create());
            $notificationsUpdated = 0;
            $coditionArr          = [];
            foreach ($collection->getAllIds() as $key => $notificationId) {
                $currentNotification = $this->_notificationRepository->getById($notificationId);
                $notificationData = $currentNotification->getData();
                if (count($notificationData)) {
                    $condition = "`id`=".$notificationId;
                    array_push($coditionArr, $condition);
                    $notificationsUpdated++;
                }
            }
            $coditionData = implode(" OR ", $coditionArr);
            $collection->setNotificationData($coditionData, ["status"=>1, "updated_at"=>$this->_date->gmtDate()]);
            if ($notificationsUpdated)
                $this->messageManager->addSuccess(__("A total of %1 record(s) were enabled.", $notificationsUpdated));
            return $resultRedirect->setPath("mobikul/notification/index");
        }

        protected function _isAllowed()     {
            return $this->_authorization->isAllowed("Webkul_Mobikul::notification");
        }

    }