<?php
namespace Webkul\MobikulMp\Controller\Chat\NotifyAdmin;

/**
 * Interceptor class for @see \Webkul\MobikulMp\Controller\Chat\NotifyAdmin
 */
class Interceptor extends \Webkul\MobikulMp\Controller\Chat\NotifyAdmin implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Webkul\Mobikul\Helper\Data $helper, \Webkul\Mobikul\Helper\Catalog $helperCatalog, \Magento\Store\Model\App\Emulation $emulate, \Webkul\Mobikul\Model\DeviceTokenFactory $deviceToken, \Magento\Customer\Model\CustomerFactory $customerFactory)
    {
        $this->___init();
        parent::__construct($context, $helper, $helperCatalog, $emulate, $deviceToken, $customerFactory);
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
