<?php
namespace Webkul\Mpfreeshipping\Controller\Shipping\View;

/**
 * Interceptor class for @see \Webkul\Mpfreeshipping\Controller\Shipping\View
 */
class Interceptor extends \Webkul\Mpfreeshipping\Controller\Shipping\View implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Webkul\Marketplace\Helper\Data $marketplaceHelper, \Magento\Customer\Model\Session $customerSession)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $marketplaceHelper, $customerSession);
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
