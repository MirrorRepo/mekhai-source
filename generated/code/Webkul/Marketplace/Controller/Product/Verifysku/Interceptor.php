<?php
namespace Webkul\Marketplace\Controller\Product\Verifysku;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Product\Verifysku
 */
class Interceptor extends \Webkul\Marketplace\Controller\Product\Verifysku implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Catalog\Model\ResourceModel\Product $productResourceModel)
    {
        $this->___init();
        parent::__construct($context, $productResourceModel);
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
