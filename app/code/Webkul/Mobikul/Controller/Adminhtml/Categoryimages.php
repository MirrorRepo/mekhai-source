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
    use Magento\Framework\Filesystem;
    use Magento\Backend\App\Action\Context;
    use Magento\Framework\App\Filesystem\DirectoryList;

    abstract class Categoryimages extends \Magento\Backend\App\Action   {

        protected $_date;
        protected $_filter;
        protected $_jsonHelper;
        protected $_storeManager;
        protected $_mediaDirectory;
        protected $_resultJsonFactory;
        protected $_collectionFactory;
        protected $_resultPageFactory;
        protected $_categoryRepository;
        protected $_coreRegistry = null;
        protected $_fileUploaderFactory;
        protected $_resultForwardFactory;
        protected $_categoryResourceModel;
        protected $_categoryimagesRepository;
        protected $_categoryimagesDataFactory;

        public function __construct(
            Context $context,
            Filesystem $filesystem,
            \Magento\Framework\Registry $coreRegistry,
            \Magento\Ui\Component\MassAction\Filter $filter,
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Magento\Framework\Stdlib\DateTime\DateTime $date,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
            \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel,
            \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
            \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
            \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
            \Webkul\Mobikul\Api\CategoryimagesRepositoryInterface $categoryimagesRepository,
            \Webkul\Mobikul\Api\Data\CategoryimagesInterfaceFactory $categoryimagesDataFactory,
            \Webkul\Mobikul\Model\ResourceModel\Categoryimages\CollectionFactory $collectionFactory
        ) {
            parent::__construct($context);
            $this->_date                      = $date;
            $this->_filter                    = $filter;
            $this->_jsonHelper                = $jsonHelper;
            $this->_storeManager              = $storeManager;
            $this->_coreRegistry              = $coreRegistry;
            $this->_resultPageFactory         = $resultPageFactory;
            $this->_collectionFactory         = $collectionFactory;
            $this->_resultJsonFactory         = $resultJsonFactory;
            $this->_categoryRepository        = $categoryRepository;
            $this->_fileUploaderFactory       = $fileUploaderFactory;
            $this->_resultForwardFactory      = $resultForwardFactory;
            $this->_categoryResourceModel     = $categoryResourceModel;
            $this->_categoryimagesRepository  = $categoryimagesRepository;
            $this->_categoryimagesDataFactory = $categoryimagesDataFactory;
            $this->_mediaDirectory            = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }

        protected function _isAllowed()     {
            return $this->_authorization->isAllowed("Webkul_Mobikul::categoryimages");
        }

    }