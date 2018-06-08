<?php
namespace Webkul\Marketplace\Controller\Mui\Index\Render;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Mui\Index\Render
 */
class Interceptor extends \Webkul\Marketplace\Controller\Mui\Index\Render implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Element\UiComponentFactory $factory)
    {
        $this->___init();
        parent::__construct($context, $factory);
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
