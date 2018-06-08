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

    namespace Webkul\Mobikul\Controller\Adminhtml;
    use Magento\Backend\App\Action;
    use Magento\Framework\Filesystem;
    use Magento\Backend\App\Action\Context;
    use Magento\Framework\App\RequestInterface;
    use Magento\Framework\View\Result\PageFactory;
    use Magento\Catalog\Api\ProductRepositoryInterface;
    use Magento\Framework\App\Filesystem\DirectoryList;
    use Magento\Catalog\Api\CategoryRepositoryInterface;
    use Magento\Backend\Model\View\Result\ForwardFactory;
    use Magento\Framework\Exception\NoSuchEntityException;
    use Webkul\Mobikul\Api\NotificationRepositoryInterface;
    use Webkul\Mobikul\Api\Data\NotificationInterfaceFactory;

    abstract class Notification extends \Magento\Backend\App\Action     {

        protected $_date;
        protected $_resultPage;
        protected $storeManager;
        protected $_mediaDirectory;
        protected $resultJsonFactory;
        protected $_resultPageFactory;
        protected $resultForwardFactory;
        protected $_coreRegistry = null;
        protected $_fileUploaderFactory;
        protected $notificationDataFactory;
        protected $_notificationRepository;
        protected $productRepositoryInterface;
        protected $categoryRepositoryInterface;

        public function __construct(
            Context $context,
            Filesystem $filesystem,
            PageFactory $resultPageFactory,
            ForwardFactory $resultForwardFactory,
            \Magento\Framework\Registry $coreRegistry,
            \Magento\Framework\Stdlib\DateTime\DateTime $date,
            NotificationInterfaceFactory $notificationDataFactory,
            ProductRepositoryInterface $productRepositoryInterface,
            NotificationRepositoryInterface $notificationRepository,
            CategoryRepositoryInterface $categoryRepositoryInterface,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
            \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
        ) {
            parent::__construct($context);
            $this->_date                       = $date;
            $this->storeManager                = $storeManager;
            $this->_coreRegistry               = $coreRegistry;
            $this->resultJsonFactory           = $resultJsonFactory;
            $this->_resultPageFactory          = $resultPageFactory;
            $this->_fileUploaderFactory        = $fileUploaderFactory;
            $this->resultForwardFactory        = $resultForwardFactory;
            $this->_notificationRepository     = $notificationRepository;
            $this->notificationDataFactory     = $notificationDataFactory;
            $this->productRepositoryInterface  = $productRepositoryInterface;
            $this->categoryRepositoryInterface = $categoryRepositoryInterface;
            $this->_mediaDirectory             = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }

        protected function _isAllowed()     {
            return $this->_authorization->isAllowed("Webkul_Mobikul::notification");
        }

    }