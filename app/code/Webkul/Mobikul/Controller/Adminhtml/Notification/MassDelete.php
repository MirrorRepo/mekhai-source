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

    class MassDelete extends \Magento\Backend\App\Action    {

        protected $filter;
        protected $collectionFactory;
        protected $_notificationRepository;

        public function __construct(
            Filter $filter,
            Context $context,
            CollectionFactory $collectionFactory,
            NotificationRepositoryInterface $notificationRepository
        ) {
            $this->filter                  = $filter;
            $this->collectionFactory       = $collectionFactory;
            $this->_notificationRepository = $notificationRepository;
            parent::__construct($context);
        }

        public function execute()   {
            $resultRedirect = $this->resultRedirectFactory->create();
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $notificationsDeleted = 0;
            foreach ($collection->getAllIds() as $notificationId) {
                if (!empty($notificationId)) {
                    try {
                        $this->_notificationRepository->deleteById($notificationId);
                        $this->messageManager->addSuccess(__("Notification has been deleted."));
                        $notificationsDeleted++;
                    } catch (\Exception $exception) {
                        $this->messageManager->addError($exception->getMessage());
                    }
                }
            }
            if ($notificationsDeleted)
                $this->messageManager->addSuccess(__("A total of %1 record(s) were deleted.", $notificationsDeleted));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath("mobikul/notification/index");
        }

        protected function _isAllowed()     {
            return $this->_authorization->isAllowed("Webkul_Mobikul::notification");
        }

    }