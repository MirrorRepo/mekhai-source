<?php
namespace Webkul\Mpcashondelivery\Controller\Order\Payadmin;

/**
 * Interceptor class for @see \Webkul\Mpcashondelivery\Controller\Order\Payadmin
 */
class Interceptor extends \Webkul\Mpcashondelivery\Controller\Order\Payadmin implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder, \Webkul\Marketplace\Helper\Data $mpHelper)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $inlineTranslation, $storeManager, $transportBuilder, $mpHelper);
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
