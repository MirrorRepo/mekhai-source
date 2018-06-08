<?php
namespace Webkul\Mpcashondelivery\Controller\Pricerules\Save;

/**
 * Interceptor class for @see \Webkul\Mpcashondelivery\Controller\Pricerules\Save
 */
class Interceptor extends \Webkul\Mpcashondelivery\Controller\Pricerules\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, \Magento\Store\Model\StoreManagerInterface $storeManager, \Webkul\Marketplace\Helper\Data $mpHelper)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $formKeyValidator, $storeManager, $mpHelper);
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
