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

    namespace Webkul\Mobikul\Block\Adminhtml\Bannerimage\Edit\Tab\View;

    use Webkul\Mobikul\Controller\RegistryConstants;

    /**
    * Adminhtml Banner image view information.
    *
    * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
    */
    class Info extends \Magento\Backend\Block\Template  {
        /**
        * Bannerimage
        *
        * @var \Webkul\Mobikul\Api\Data\BannerimageInterface
        */
        protected $bannerimage;

        /**
        * Bannerimage registry
        *
        * @var \Webkul\Mobikul\Model\Bannerimage
        */
        protected $bannerimageRegistry;

        /**
        * Bannerimage data factory
        *
        * @var \Webkul\Mobikul\Api\Data\BannerimageInterfaceFactory
        */
        protected $bannerDataFactory;

        /**
        * Core registry
        *
        * @var \Magento\Framework\Registry
        */
        protected $coreRegistry;

        /**
        * Data object helper
        *
        * @var \Magento\Framework\Api\DataObjectHelper
        */
        protected $dataObjectHelper;

        /**
        * @param \Magento\Backend\Block\Template\Context              $context
        * @param \Webkul\Mobikul\Api\Data\BannerimageInterfaceFactory $bannerDataFactory
        * @param \Magento\Framework\Registry                          $registry
        * @param \Magento\Framework\Api\DataObjectHelper              $dataObjectHelper
        * @param array                                                $data
        * @SuppressWarnings(PHPMD.ExcessiveParameterList)
        */
        public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            \Webkul\Mobikul\Api\Data\BannerimageInterfaceFactory $bannerDataFactory,
            \Magento\Framework\Registry $registry,
            \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
            array $data=[]) {
            $this->coreRegistry = $registry;
            $this->bannerDataFactory = $bannerDataFactory;
            $this->dataObjectHelper = $dataObjectHelper;
            parent::__construct($context, $data);
        }

        /**
        * Set Bannerimage registry
        *
        * @param      \Magento\Framework\Registry $coreRegistry
        * @return     void
        * @deprecated
        */
        public function setBannerimageRegistry(\Webkul\Mobikul\Model\BannerimageRegistry $bannerimageRegistry){
            $this->bannerimageRegistry = $bannerimageRegistry;
        }

        /**
        * Get banner registry
        *
        * @return     \Webkul\Mobikul\Model\BannerimageRegistry
        * @deprecated
        */
        public function getBannerimageRegistry()    {
            if (!($this->bannerimageRegistry instanceof \Webkul\Mobikul\Model\BannerimageRegistry)) {
                return \Magento\Framework\App\ObjectManager::getInstance()->get("Webkul\Mobikul\Model\BannerimageRegistry");
            } else {
                return $this->bannerimageRegistry;
            }
        }

        /**
        * Retrieve banner object
        *
        * @return \Webkul\Mobikul\Api\Data\BannerimageInterface
        */
        public function getBannerimage()    {
            if (!$this->bannerimage) {
                $this->bannerimage = $this->bannerDataFactory->create();
                $this->dataObjectHelper->populateWithArray($this->bannerimage, $this->_backendSession->getBannerimageData()["general"], "\Webkul\Mobikul\Api\Data\BannerimageInterface");
            }
            return $this->bannerimage;
        }

        /**
        * Retrieve banner id
        *
        * @return string|null
        */
        public function getBannerimageId() {
            return $this->coreRegistry->registry(RegistryConstants::CURRENT_BANNER_ID);
        }

        /**
        * Get banner creation date
        *
        * @return string
        */
        public function getCreateDate()     {
            return $this->formatDate($this->getBannerimage()->getCreatedTime(), \IntlDateFormatter::MEDIUM, true);
        }

        /**
        * Get banner"s current status.
        *
        * @return string
        */
        public function getCurrentStatus()  {
            return __("Online");
        }

    }