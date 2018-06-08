<?php
namespace Webkul\Mobikul\Controller\Extra\SearchSuggestion;

/**
 * Interceptor class for @see \Webkul\Mobikul\Controller\Extra\SearchSuggestion
 */
class Interceptor extends \Webkul\Mobikul\Controller\Extra\SearchSuggestion implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Webkul\Mobikul\Helper\Data $helper, \Magento\Store\Model\App\Emulation $emulate, \Psr\Log\LoggerInterface $logger, \Magento\Eav\Model\Config $eavConfig, \Webkul\Mobikul\Helper\Catalog $helperCatalog, \Magento\Cms\Model\BlockFactory $blockFactory, \Magento\Framework\Filesystem\DirectoryList $dir, \Magento\Framework\Pricing\Helper\Data $priceHelper, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Catalog\Model\CategoryFactory $categoryFactory, \Magento\Customer\Model\CustomerFactory $customerFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Cms\Model\Template\FilterProvider $filterProvider, \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar, \Webkul\Mobikul\Model\DeviceTokenFactory $deviceTokenFactory, \Magento\Catalog\Model\Product\Visibility $productVisibility, \Webkul\Mobikul\Model\NotificationFactory $mobikulNotification, \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus, \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes)
    {
        $this->___init();
        parent::__construct($context, $helper, $emulate, $logger, $eavConfig, $helperCatalog, $blockFactory, $dir, $priceHelper, $productFactory, $categoryFactory, $customerFactory, $storeManager, $filterProvider, $toolbar, $deviceTokenFactory, $productVisibility, $mobikulNotification, $productStatus, $filterableAttributes);
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
