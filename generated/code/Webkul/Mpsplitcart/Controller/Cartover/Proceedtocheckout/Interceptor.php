<?php
namespace Webkul\Mpsplitcart\Controller\Cartover\Proceedtocheckout;

/**
 * Interceptor class for @see \Webkul\Mpsplitcart\Controller\Cartover\Proceedtocheckout
 */
class Interceptor extends \Webkul\Mpsplitcart\Controller\Cartover\Proceedtocheckout implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, \Webkul\Mpsplitcart\Helper\Data $helper)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $formKeyValidator, $helper);
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
