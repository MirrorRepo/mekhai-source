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

    namespace Webkul\Mobikul\Controller\Extra;
    use Magento\Store\Model\App\Emulation;
    use Magento\Framework\App\Action\Action;
    use Magento\Framework\App\Action\Context;
    use Webkul\Mobikul\Helper\Data as HelperData;
    use Magento\Framework\Controller\ResultFactory;

    abstract class AbstractMobikul extends \Webkul\Mobikul\Controller\ApiController     {

        protected $_helper;
        protected $_logger;
        protected $_toolbar;
        protected $_baseDir;
        protected $_emulate;
        protected $_eavConfig;
        protected $_deviceToken;
        protected $_priceHelper;
        protected $_blockFactory;
        protected $_storeManager;
        protected $_productStatus;
        protected $_filterProvider;
        protected $_productFactory;
        protected $_categoryfactory;
        protected $_customerFactory;
        protected $_productVisibility;
        protected $_mobikulNotification;
        protected $_filterableAttributes;

        public function __construct(
            Context $context,
            HelperData $helper,
            Emulation $emulate,
            \Psr\Log\LoggerInterface $logger,
            \Magento\Eav\Model\Config $eavConfig,
            \Webkul\Mobikul\Helper\Catalog $helperCatalog,
            \Magento\Cms\Model\BlockFactory $blockFactory,
            \Magento\Framework\Filesystem\DirectoryList $dir,
            \Magento\Framework\Pricing\Helper\Data $priceHelper,
            \Magento\Catalog\Model\ProductFactory $productFactory,
            \Magento\Catalog\Model\CategoryFactory $categoryFactory,
            \Magento\Customer\Model\CustomerFactory $customerFactory,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Cms\Model\Template\FilterProvider $filterProvider,
            \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
            \Webkul\Mobikul\Model\DeviceTokenFactory $deviceTokenFactory,
            \Magento\Catalog\Model\Product\Visibility $productVisibility,
            \Webkul\Mobikul\Model\NotificationFactory $mobikulNotification,
            \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
            \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes
        ) {
            $this->_logger               = $logger;
            $this->_helper               = $helper;
            $this->_emulate              = $emulate;
            $this->_toolbar              = $toolbar;
            $this->_eavConfig            = $eavConfig;
            $this->_priceHelper          = $priceHelper;
            $this->_storeManager         = $storeManager;
            $this->_blockFactory         = $blockFactory;
            $this->_productStatus        = $productStatus;
            $this->_filterProvider       = $filterProvider;
            $this->_productFactory       = $productFactory;
            $this->_categoryFactory      = $categoryFactory;
            $this->_customerFactory      = $customerFactory;
            $this->_productVisibility    = $productVisibility;
            $this->_deviceToken          = $deviceTokenFactory;
            $this->_mobikulNotification  = $mobikulNotification;
            $this->_filterableAttributes = $filterableAttributes;
            $this->_baseDir              = $dir->getPath("media");
            parent::__construct($helper, $context);
        }

    }