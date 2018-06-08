<?php
namespace Webkul\Mpcashondelivery\Controller\Products\Massupdate;

/**
 * Interceptor class for @see \Webkul\Mpcashondelivery\Controller\Products\Massupdate
 */
class Interceptor extends \Webkul\Mpcashondelivery\Controller\Products\Massupdate implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, \Magento\Catalog\Model\ProductFactory $magentoProduct)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $formKeyValidator, $magentoProduct);
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
