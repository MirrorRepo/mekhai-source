<?php
namespace Webkul\Mobikul\Controller\Catalog\HomePageData;

/**
 * Interceptor class for @see \Webkul\Mobikul\Controller\Catalog\HomePageData
 */
class Interceptor extends \Webkul\Mobikul\Controller\Catalog\HomePageData implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Stdlib\DateTime\DateTime $date, \Magento\Framework\App\Action\Context $context, \Magento\Store\Model\App\Emulation $emulate, \Webkul\Mobikul\Helper\Data $helper, \Webkul\Mobikul\Helper\Catalog $helperCatalog, \Magento\Store\Model\Store $store, \Magento\Quote\Model\Quote $quote, \Magento\Framework\Escaper $escaper, \Magento\Eav\Model\Config $eavConfig, \Webkul\Mobikul\Model\Layer $mobikulLayer, \Magento\Framework\Registry $coreRegistry, \Magento\Customer\Model\Customer $customer, \Magento\Catalog\Model\Config $catalogConfig, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository, \Magento\Checkout\Helper\Data $checkoutHelper, \Webkul\Mobikul\Model\Bannerimage $bannerImage, \Magento\Framework\Locale\Format $localeFormat, \Webkul\Mobikul\Model\UserImage $customerImage, \Magento\CatalogSearch\Model\Fulltext $fullText, \Magento\Framework\Json\Helper\Data $jsonHelper, \Magento\Catalog\Model\CategoryFactory $category, \Magento\Search\Model\QueryFactory $queryFactory, \Magento\Framework\Filesystem\DirectoryList $dir, \Magento\Customer\Model\Visitor $customerVisitor, \Magento\Catalog\Helper\Product\Compare $compare, \Magento\Wishlist\Model\WishlistFactory $wishlist, \Webkul\Mobikul\Model\Category\Tree $categoryTree, \Magento\Store\Model\WebsiteFactory $websiteManager, \Magento\Framework\Pricing\Helper\Data $priceFormat, \Magento\CatalogInventory\Helper\Stock $stockFilter, \Magento\Catalog\Helper\Output $catalogHelperOutput, \Magento\Catalog\Model\Product\Option $productOption, \Magento\Framework\Pricing\Helper\Data $pricingHelper, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Framework\App\ResourceConnection $connection, \Magento\Catalog\Block\Product\ListProduct $listProduct, \Magento\Store\Model\StoreManagerInterface $storeInterface, \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar, \Magento\Catalog\Model\Product\Visibility $productVisibility, \Webkul\Mobikul\Model\Featuredcategories $featuredCategories, \Magento\CatalogSearch\Model\Advanced $advancedCatalogSearch, \Magento\Cms\Model\ResourceModel\Page\Collection $cmsCollection, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Webkul\Mobikul\Model\CategoryimagesFactory $categoryImageFactory, \Magento\Catalog\Model\ResourceModel\Product $productResourceModel, \Magento\Catalog\Block\Product\Compare\ListCompare $compareListBlock, \Magento\Catalog\Model\Layer\Filter\AttributeFactory $layerAttribute, \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel, \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus, \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory, \Webkul\Mobikul\Model\ResourceModel\Layer\Filter\Price $mobikulLayerPrice, \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection, \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes, \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $filterPriceDataprovider, \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory $layerFilterAttributeResource, \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $compareItemCollectionFactory)
    {
        $this->___init();
        parent::__construct($date, $context, $emulate, $helper, $helperCatalog, $store, $quote, $escaper, $eavConfig, $mobikulLayer, $coreRegistry, $customer, $catalogConfig, $productRepository, $checkoutHelper, $bannerImage, $localeFormat, $customerImage, $fullText, $jsonHelper, $category, $queryFactory, $dir, $customerVisitor, $compare, $wishlist, $categoryTree, $websiteManager, $priceFormat, $stockFilter, $catalogHelperOutput, $productOption, $pricingHelper, $productFactory, $connection, $listProduct, $storeInterface, $toolbar, $productVisibility, $featuredCategories, $advancedCatalogSearch, $cmsCollection, $localeDate, $categoryImageFactory, $productResourceModel, $compareListBlock, $layerAttribute, $categoryResourceModel, $productStatus, $compareItemFactory, $mobikulLayerPrice, $catalogProductCompareList, $productCollection, $filterableAttributes, $filterPriceDataprovider, $layerFilterAttributeResource, $compareItemCollectionFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        if (!$pluginInfo) {
            return parent::dispatch($request);
        } else {
            return $this->___callPlugins('dispatch', func_get_args(), $pluginInfo);
        }
    }
}
