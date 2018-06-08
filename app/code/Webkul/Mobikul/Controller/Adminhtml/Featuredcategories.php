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
    use Magento\Framework\App\Filesystem\DirectoryList;
    use Magento\Catalog\Api\CategoryRepositoryInterface;
    use Magento\Backend\Model\View\Result\ForwardFactory;
    use Magento\Framework\Exception\NoSuchEntityException;
    use Webkul\Mobikul\Api\FeaturedcategoriesRepositoryInterface;
    use Webkul\Mobikul\Api\Data\FeaturedcategoriesInterfaceFactory;

    abstract class Featuredcategories extends \Magento\Backend\App\Action   {
        protected $_date;
        protected $_resultPage;
        protected $storeManager;
        protected $_mediaDirectory;
        protected $resultJsonFactory;
        protected $_resultPageFactory;
        protected $_fileUploaderFactory;
        protected $_coreRegistry = null;
        protected $resultForwardFactory;
        protected $categoryRepositoryInterface;
        protected $featuredcategoriesDataFactory;
        protected $_featuredcategoriesRepository;

        public function __construct(
            Context $context,
            Filesystem $filesystem,
            PageFactory $resultPageFactory,
            ForwardFactory $resultForwardFactory,
            \Magento\Framework\Registry $coreRegistry,
            \Magento\Framework\Stdlib\DateTime\DateTime $date,
            CategoryRepositoryInterface $categoryRepositoryInterface,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            FeaturedcategoriesInterfaceFactory $featuredcategoriesDataFactory,
            FeaturedcategoriesRepositoryInterface $featuredcategoriesRepository,
            \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
            \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
        ) {
            parent::__construct($context);
            $this->_date                         = $date;
            $this->storeManager                  = $storeManager;
            $this->_coreRegistry                 = $coreRegistry;
            $this->_resultPageFactory            = $resultPageFactory;
            $this->resultJsonFactory             = $resultJsonFactory;
            $this->_fileUploaderFactory          = $fileUploaderFactory;
            $this->resultForwardFactory          = $resultForwardFactory;
            $this->categoryRepositoryInterface   = $categoryRepositoryInterface;
            $this->_featuredcategoriesRepository = $featuredcategoriesRepository;
            $this->featuredcategoriesDataFactory = $featuredcategoriesDataFactory;
            $this->_mediaDirectory               = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }

        protected function _isAllowed()     {
            return $this->_authorization->isAllowed("Webkul_Mobikul::featuredcategories");
        }

    }