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
    use Magento\Framework\App\Filesystem\DirectoryList;

    abstract class Bannerimage extends \Magento\Backend\App\Action      {

        protected $_filter;
        protected $_storeManager;
        protected $_mediaDirectory;
        protected $_resultJsonFactory;
        protected $_collectionFactory;
        protected $_resultPageFactory;
        protected $_fileUploaderFactory;
        protected $_coreRegistry = null;
        protected $_resultForwardFactory;
        protected $_bannerimageRepository;
        protected $_bannerimageDataFactory;
        protected $_productRepositoryInterface;
        protected $_categoryRepositoryInterface;

        public function __construct(
            \Magento\Framework\Registry $coreRegistry,
            \Magento\Framework\Filesystem $filesystem,
            \Magento\Backend\App\Action\Context $context,
            \Magento\Ui\Component\MassAction\Filter $filter,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
            \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
            \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
            \Webkul\Mobikul\Api\BannerimageRepositoryInterface $bannerimageRepository,
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
            \Webkul\Mobikul\Api\Data\BannerimageInterfaceFactory $bannerimageDataFactory,
            \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface,
            \Webkul\Mobikul\Model\ResourceModel\Bannerimage\CollectionFactory $collectionFactory
        ) {
            parent::__construct($context);
            $this->_filter                      = $filter;
            $this->_storeManager                = $storeManager;
            $this->_coreRegistry                = $coreRegistry;
            $this->_collectionFactory           = $collectionFactory;
            $this->_resultPageFactory           = $resultPageFactory;
            $this->_resultJsonFactory           = $resultJsonFactory;
            $this->_fileUploaderFactory         = $fileUploaderFactory;
            $this->_resultForwardFactory        = $resultForwardFactory;
            $this->_bannerimageRepository       = $bannerimageRepository;
            $this->_bannerimageDataFactory      = $bannerimageDataFactory;
            $this->_productRepositoryInterface  = $productRepositoryInterface;
            $this->_categoryRepositoryInterface = $categoryRepositoryInterface;
            $this->_mediaDirectory              = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }

        protected function _isAllowed()     {
            return $this->_authorization->isAllowed("Webkul_Mobikul::bannerimage");
        }

    }