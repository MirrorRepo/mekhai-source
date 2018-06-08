<?php
namespace Webkul\Marketplace\Helper\Data;

/**
 * Interceptor class for @see \Webkul\Marketplace\Helper\Data
 */
class Interceptor extends \Webkul\Marketplace\Helper\Data implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Customer\Model\Session $customerSession, \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $collectionFactory, \Magento\Framework\App\Http\Context $httpContext, \Magento\Catalog\Model\ResourceModel\Product $product, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Directory\Model\Currency $currency, \Magento\Framework\Locale\CurrencyInterface $localeCurrency)
    {
        $this->___init();
        parent::__construct($context, $objectManager, $customerSession, $collectionFactory, $httpContext, $product, $storeManager, $currency, $localeCurrency);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollectionUrl()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getCollectionUrl');
        if (!$pluginInfo) {
            return parent::getCollectionUrl();
        } else {
            return $this->___callPlugins('getCollectionUrl', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLocationUrl()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getLocationUrl');
        if (!$pluginInfo) {
            return parent::getLocationUrl();
        } else {
            return $this->___callPlugins('getLocationUrl', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFeedbackUrl()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getFeedbackUrl');
        if (!$pluginInfo) {
            return parent::getFeedbackUrl();
        } else {
            return $this->___callPlugins('getFeedbackUrl', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRewriteUrl($targetUrl)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getRewriteUrl');
        if (!$pluginInfo) {
            return parent::getRewriteUrl($targetUrl);
        } else {
            return $this->___callPlugins('getRewriteUrl', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetUrlPath()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getTargetUrlPath');
        if (!$pluginInfo) {
            return parent::getTargetUrlPath();
        } else {
            return $this->___callPlugins('getTargetUrlPath', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerMappedPermissions()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getControllerMappedPermissions');
        if (!$pluginInfo) {
            return parent::getControllerMappedPermissions();
        } else {
            return $this->___callPlugins('getControllerMappedPermissions', func_get_args(), $pluginInfo);
        }
    }
}
