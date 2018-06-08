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

    namespace Webkul\Mobikul\Controller\Catalog;
    use Magento\Store\Model\App\Emulation;
    use Magento\Framework\App\Action\Context;
    use Webkul\Mobikul\Helper\Data as HelperData;
    use Magento\Framework\Stdlib\DateTime\DateTime;
    use Magento\Catalog\Api\ProductRepositoryInterface;
    use Webkul\Mobikul\Helper\Catalog as HelperCatalog;

    abstract class AbstractCatalog extends \Webkul\Mobikul\Controller\ApiController    {

        protected $_dir;
        protected $_date;
        protected $_items;
        protected $_quote;
        protected $_store;
        protected $_helper;
        protected $_emulate;
        protected $_escaper;
        protected $_toolbar;
        protected $_compare;
        protected $_baseDir;
        protected $_headers;
        protected $_customer;
        protected $_fullText;
        protected $_wishlist;
        protected $_category;
        protected $_eavConfig;
        protected $_localeDate;
        protected $_jsonHelper;
        protected $_connection;
        protected $_priceFormat;
        protected $_bannerImage;
        protected $_listProduct;
        protected $_coreRegistry;
        protected $_localeFormat;
        protected $_queryFactory;
        protected $_mobikulLayer;
        protected $_categoryTree;
        protected $_productOption;
        protected $_helperCatalog;
        protected $_pricingHelper;
        protected $_catalogConfig;
        protected $_cmsCollection;
        protected $_productStatus;
        protected $_customerImage;
        protected $_checkoutHelper;
        protected $_layerAttribute;
        protected $_websiteManager;
        protected $_storeInterface;
        protected $_productFactory;
        protected $_customerVisitor;
        protected $_compareListBlock;
        protected $_productRepository;
        protected $_productVisibility;
        protected $_mobikulLayerPrice;
        protected $_featuredCategories;
        protected $_compareItemFactory;
        protected $_catalogHelperOutput;
        protected $_filterableAttributes;
        protected $_categoryImageFactory;
        protected $_productResourceModel;
        protected $_advancedCatalogSearch;
        protected $_categoryResourceModel;
        protected $_filterPriceDataprovider;
        protected $_catalogProductCompareList;
        protected $_layerFilterAttributeResource;
        protected $_compareItemCollectionFactory;
        
        public function __construct(
            DateTime $date,
            Context $context,
            Emulation $emulate,
            HelperData $helper,
            HelperCatalog $helperCatalog,
            \Magento\Store\Model\Store $store,
            \Magento\Quote\Model\Quote $quote,
            \Magento\Framework\Escaper $escaper,
            \Magento\Eav\Model\Config $eavConfig,
            \Webkul\Mobikul\Model\Layer $mobikulLayer,
            \Magento\Framework\Registry $coreRegistry,
            \Magento\Customer\Model\Customer $customer,
            \Magento\Catalog\Model\Config $catalogConfig,
            ProductRepositoryInterface $productRepository,
            \Magento\Checkout\Helper\Data $checkoutHelper,
            \Webkul\Mobikul\Model\Bannerimage $bannerImage,
            \Magento\Framework\Locale\Format $localeFormat,
            \Webkul\Mobikul\Model\UserImage $customerImage,
            \Magento\CatalogSearch\Model\Fulltext $fullText,
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Magento\Catalog\Model\CategoryFactory $category,
            \Magento\Search\Model\QueryFactory $queryFactory,
            \Magento\Framework\Filesystem\DirectoryList $dir,
            \Magento\Customer\Model\Visitor $customerVisitor,
            \Magento\Catalog\Helper\Product\Compare $compare,
            \Magento\Wishlist\Model\WishlistFactory $wishlist,
            \Webkul\Mobikul\Model\Category\Tree $categoryTree,
            \Magento\Store\Model\WebsiteFactory $websiteManager,
            \Magento\Framework\Pricing\Helper\Data $priceFormat,
            \Magento\CatalogInventory\Helper\Stock $stockFilter,
            \Magento\Catalog\Helper\Output $catalogHelperOutput,
            \Magento\Catalog\Model\Product\Option $productOption,
            \Magento\Framework\Pricing\Helper\Data $pricingHelper,
            \Magento\Catalog\Model\ProductFactory $productFactory,
            \Magento\Framework\App\ResourceConnection $connection,
            \Magento\Catalog\Block\Product\ListProduct $listProduct,
            \Magento\Store\Model\StoreManagerInterface $storeInterface,
            \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
            \Magento\Catalog\Model\Product\Visibility $productVisibility,
            \Webkul\Mobikul\Model\Featuredcategories $featuredCategories,
            \Magento\CatalogSearch\Model\Advanced $advancedCatalogSearch,
            \Magento\Cms\Model\ResourceModel\Page\Collection $cmsCollection,
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
            \Webkul\Mobikul\Model\CategoryimagesFactory $categoryImageFactory,
            \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
            \Magento\Catalog\Block\Product\Compare\ListCompare $compareListBlock,
            \Magento\Catalog\Model\Layer\Filter\AttributeFactory $layerAttribute,
            \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel,
            \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
            \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory,
            \Webkul\Mobikul\Model\ResourceModel\Layer\Filter\Price $mobikulLayerPrice,
            \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList,
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
            \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes,
            \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $filterPriceDataprovider,
            \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory $layerFilterAttributeResource,
            \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $compareItemCollectionFactory
        ) {
            $this->_dir                          = $dir;
            $this->_date                         = $date;
            $this->_store                        = $store;
            $this->_quote                        = $quote;
            $this->_helper                       = $helper;
            $this->_escaper                      = $escaper;
            $this->_compare                      = $compare;
            $this->_baseDir                      = $dir->getPath("media");
            $this->_toolbar                      = $toolbar;
            $this->_emulate                      = $emulate;
            $this->_category                     = $category;
            $this->_wishlist                     = $wishlist;
            $this->_customer                     = $customer;
            $this->_fullText                     = $fullText;
            $this->_eavConfig                    = $eavConfig;
            $this->_connection                   = $connection;
            $this->_localeDate                   = $localeDate;
            $this->_jsonHelper                   = $jsonHelper;
            $this->_priceFormat                  = $priceFormat;
            $this->_listProduct                  = $listProduct;
            $this->_bannerImage                  = $bannerImage;
            $this->_stockFilter                  = $stockFilter;
            $this->_categoryTree                 = $categoryTree;
            $this->_localeFormat                 = $localeFormat;
            $this->_queryFactory                 = $queryFactory;
            $this->_mobikulLayer                 = $mobikulLayer;
            $this->_coreRegistry                 = $coreRegistry;
            $this->_helperCatalog                = $helperCatalog;
            $this->_customerImage                = $customerImage;
            $this->_productStatus                = $productStatus;
            $this->_pricingHelper                = $pricingHelper;
            $this->_catalogConfig                = $catalogConfig;
            $this->_cmsCollection                = $cmsCollection;
            $this->_productOption                = $productOption;
            $this->_layerAttribute               = $layerAttribute;
            $this->_storeInterface               = $storeInterface;
            $this->_websiteManager               = $websiteManager;
            $this->_checkoutHelper               = $checkoutHelper;
            $this->_productFactory               = $productFactory;
            $this->_customerVisitor              = $customerVisitor;
            $this->_compareListBlock             = $compareListBlock;
            $this->_mobikulLayerPrice            = $mobikulLayerPrice;
            $this->_productRepository            = $productRepository;
            $this->_productVisibility            = $productVisibility;
            $this->_productCollection            = $productCollection;
            $this->_featuredCategories           = $featuredCategories;
            $this->_compareItemFactory           = $compareItemFactory;
            $this->_catalogHelperOutput          = $catalogHelperOutput;
            $this->_categoryImageFactory         = $categoryImageFactory;
            $this->_filterableAttributes         = $filterableAttributes;
            $this->_productResourceModel         = $productResourceModel;
            $this->_advancedCatalogSearch        = $advancedCatalogSearch;
            $this->_categoryResourceModel        = $categoryResourceModel;
            $this->_filterPriceDataprovider      = $filterPriceDataprovider;
            $this->_catalogProductCompareList    = $catalogProductCompareList;
            $this->_layerFilterAttributeResource = $layerFilterAttributeResource;
            $this->_compareItemCollectionFactory = $compareItemCollectionFactory;
            parent::__construct($helper, $context);
        }

    }