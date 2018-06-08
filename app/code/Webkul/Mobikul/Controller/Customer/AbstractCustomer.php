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

    namespace Webkul\Mobikul\Controller\Customer;
    use Magento\Store\Model\App\Emulation;
    use Magento\Framework\App\Action\Context;
    use Magento\Customer\Model\CustomerExtractor;
    use Webkul\Mobikul\Helper\Data as HelperData;
    use Webkul\Mobikul\Helper\Catalog as HelperCatalog;
    use Magento\Customer\Api\AccountManagementInterface;

    abstract class AbstractCustomer extends \Webkul\Mobikul\Controller\ApiController    {

        protected $_cart;
        protected $_quote;
        protected $visitor;
        protected $_country;
        protected $_baseDir;
        protected $_userImage;
        protected $_encryptor;
        protected $_tokenHelper;
        protected $_compareItem;
        protected $_quoteFactory;
        protected $_storeManager;
        protected $_stockRegistry;
        protected $_catalogConfig;
        protected $_authentication;
        protected $_customerMapper;
        protected $_stockRepository;
        protected $_customerFactory;
        protected $_wishlistProvider;
        protected $_dataObjectHelper;
        protected $_productVisibility;
        protected $_productRepository;
        protected $_accountManagement;
        protected $_emailNotification;
        protected $_customerExtractor;
        protected $_addressRepository;
        protected $_productCollectionFactory;
        protected $_customerRepositoryInterface;

        public function __construct(
            Context $context,
            HelperData $helper,
            Emulation $emulate,
            HelperCatalog $helperCatalog,
            \Magento\Quote\Model\Quote $quote,
            \Magento\Checkout\Model\Cart $cart,
            CustomerExtractor $customerExtractor,
            \Magento\Customer\Model\Visitor $visitor,
            \Webkul\Mobikul\Helper\Token $tokenHelper,
            \Webkul\Mobikul\Model\UserImage $userImage,
            \Magento\Catalog\Model\Config $catalogConfig,
            AccountManagementInterface $accountManagement,
            \Magento\Quote\Model\QuoteFactory $quoteFactory,
            \Magento\Framework\Filesystem\DirectoryList $dir,
            \Magento\Wishlist\Model\Wishlist $wishlistProvider,
            \Magento\Catalog\Helper\Product\Compare $productCompare,
            \Magento\Customer\Model\CustomerFactory $customerFactory,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
            \Magento\Framework\Encryption\EncryptorInterface $encryptor,
            \Magento\Catalog\Model\Product\Visibility $productVisibility,
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
            \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
            \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
            \Magento\Catalog\Model\ResourceModel\Product\Compare\Item $compareItem,
            \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockRepository,
            \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
            \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $productCollectionFactory
        ) {
            $this->_cart                        = $cart;
            $this->_quote                       = $quote;
            $this->_emulate                     = $emulate;
            $this->_visitor                     = $visitor;
            $this->_baseDir                     = $dir->getPath("media");
            $this->_userImage                   = $userImage;
            $this->_encryptor                   = $encryptor;
            $this->_tokenHelper                 = $tokenHelper;
            $this->_compareItem                 = $compareItem;
            $this->_storeManager                = $storeManager;
            $this->_quoteFactory                = $quoteFactory;
            $this->_catalogConfig               = $catalogConfig;
            $this->_helperCatalog               = $helperCatalog;
            $this->_stockRegistry               = $stockRegistry;
            $this->_productCompare              = $productCompare;
            $this->_stockRepository             = $stockRepository;
            $this->_customerFactory             = $customerFactory;
            $this->_wishlistProvider            = $wishlistProvider;
            $this->_dataObjectHelper            = $dataObjectHelper;
            $this->_productVisibility           = $productVisibility;
            $this->_productRepository           = $productRepository;
            $this->_accountManagement           = $accountManagement;
            $this->_addressRepository           = $addressRepository;
            $this->_customerExtractor           = $customerExtractor;
            $this->_productCollectionFactory    = $productCollectionFactory;
            $this->_customerRepositoryInterface = $customerRepositoryInterface;
            parent::__construct($helper, $context);
        }

    }