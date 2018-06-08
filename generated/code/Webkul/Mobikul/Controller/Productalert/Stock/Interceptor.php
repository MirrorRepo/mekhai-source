<?php
namespace Webkul\Mobikul\Controller\Productalert\Stock;

/**
 * Interceptor class for @see \Webkul\Mobikul\Controller\Productalert\Stock
 */
class Interceptor extends \Webkul\Mobikul\Controller\Productalert\Stock implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Store\Model\App\Emulation $emulate, \Webkul\Mobikul\Helper\Data $helper, \Webkul\Mobikul\Helper\Catalog $helperCatalog, \Magento\Catalog\Model\ProductFactory $productLoader)
    {
        $this->___init();
        parent::__construct($context, $emulate, $helper, $helperCatalog, $productLoader);
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
