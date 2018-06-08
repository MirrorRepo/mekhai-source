<?php
namespace Webkul\Mobikul\Controller\Contact\Post;

/**
 * Interceptor class for @see \Webkul\Mobikul\Controller\Contact\Post
 */
class Interceptor extends \Webkul\Mobikul\Controller\Contact\Post implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Webkul\Mobikul\Helper\Data $helper, \Magento\Store\Model\App\Emulation $emulate, \Webkul\Mobikul\Helper\Catalog $helperCatalog)
    {
        $this->___init();
        parent::__construct($context, $helper, $emulate, $helperCatalog);
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
