<?php
namespace Webkul\Marketplace\Block\Page\Switcher;

/**
 * Interceptor class for @see \Webkul\Marketplace\Block\Page\Switcher
 */
class Interceptor extends \Webkul\Marketplace\Block\Page\Switcher implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Data\Helper\PostHelper $postDataHelper, \Magento\Directory\Model\CurrencyFactory $currencyFactory, \Magento\Framework\Locale\ResolverInterface $localeResolver, array $data = array())
    {
        $this->___init();
        parent::__construct($context, $postDataHelper, $currencyFactory, $localeResolver, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetStorePostData(\Magento\Store\Model\Store $store, $data = array())
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getTargetStorePostData');
        if (!$pluginInfo) {
            return parent::getTargetStorePostData($store, $data);
        } else {
            return $this->___callPlugins('getTargetStorePostData', func_get_args(), $pluginInfo);
        }
    }
}
