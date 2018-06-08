<?php
namespace Webkul\Mobikul\Controller\Customer\OrderList;

/**
 * Interceptor class for @see \Webkul\Mobikul\Controller\Customer\OrderList
 */
class Interceptor extends \Webkul\Mobikul\Controller\Customer\OrderList implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Webkul\Mobikul\Helper\Data $helper, \Magento\Store\Model\App\Emulation $emulate, \Webkul\Mobikul\Helper\Catalog $helperCatalog, \Magento\Quote\Model\Quote $quote, \Magento\Checkout\Model\Cart $cart, \Magento\Customer\Model\CustomerExtractor $customerExtractor, \Magento\Customer\Model\Visitor $visitor, \Webkul\Mobikul\Helper\Token $tokenHelper, \Webkul\Mobikul\Model\UserImage $userImage, \Magento\Catalog\Model\Config $catalogConfig, \Magento\Customer\Api\AccountManagementInterface $accountManagement, \Magento\Quote\Model\QuoteFactory $quoteFactory, \Magento\Framework\Filesystem\DirectoryList $dir, \Magento\Wishlist\Model\Wishlist $wishlistProvider, \Magento\Catalog\Helper\Product\Compare $productCompare, \Magento\Customer\Model\CustomerFactory $customerFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Api\DataObjectHelper $dataObjectHelper, \Magento\Framework\Encryption\EncryptorInterface $encryptor, \Magento\Catalog\Model\Product\Visibility $productVisibility, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository, \Magento\Customer\Api\AddressRepositoryInterface $addressRepository, \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry, \Magento\Catalog\Model\ResourceModel\Product\Compare\Item $compareItem, \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockRepository, \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface, \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $productCollectionFactory)
    {
        $this->___init();
        parent::__construct($context, $helper, $emulate, $helperCatalog, $quote, $cart, $customerExtractor, $visitor, $tokenHelper, $userImage, $catalogConfig, $accountManagement, $quoteFactory, $dir, $wishlistProvider, $productCompare, $customerFactory, $storeManager, $dataObjectHelper, $encryptor, $productVisibility, $productRepository, $addressRepository, $stockRegistry, $compareItem, $stockRepository, $customerRepositoryInterface, $productCollectionFactory);
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
