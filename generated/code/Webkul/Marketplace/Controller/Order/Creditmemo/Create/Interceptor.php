<?php
namespace Webkul\Marketplace\Controller\Order\Creditmemo\Create;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Order\Creditmemo\Create
 */
class Interceptor extends \Webkul\Marketplace\Controller\Order\Creditmemo\Create implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Sales\Api\OrderRepositoryInterface $orderRepository, \Magento\Framework\Registry $coreRegistry, \Magento\Customer\Model\Session $customerSession)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $orderRepository, $coreRegistry, $customerSession);
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
