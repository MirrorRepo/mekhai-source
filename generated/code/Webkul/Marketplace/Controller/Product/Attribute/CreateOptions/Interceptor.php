<?php
namespace Webkul\Marketplace\Controller\Product\Attribute\CreateOptions;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Product\Attribute\CreateOptions
 */
class Interceptor extends \Webkul\Marketplace\Controller\Product\Attribute\CreateOptions implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $eavAttribute)
    {
        $this->___init();
        parent::__construct($context, $eavAttribute);
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