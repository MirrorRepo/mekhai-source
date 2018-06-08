<?php
namespace Webkul\Marketplace\Controller\Account\SavePaymentInfo;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Account\SavePaymentInfo
 */
class Interceptor extends \Webkul\Marketplace\Controller\Account\SavePaymentInfo implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, \Magento\Framework\Stdlib\DateTime\DateTime $date)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $formKeyValidator, $date);
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
