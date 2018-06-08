<?php
namespace Webkul\Mpcashondelivery\Controller\Availability\Codamount;

/**
 * Interceptor class for @see \Webkul\Mpcashondelivery\Controller\Availability\Codamount
 */
class Interceptor extends \Webkul\Mpcashondelivery\Controller\Availability\Codamount implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Webkul\Mpcashondelivery\Helper\Data $helper, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Checkout\Model\Cart $cart, \Magento\Quote\Api\CartRepositoryInterface $quoteRepository)
    {
        $this->___init();
        parent::__construct($context, $helper, $checkoutSession, $resultPageFactory, $cart, $quoteRepository);
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
