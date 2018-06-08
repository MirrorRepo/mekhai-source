<?php
namespace Webkul\Mpfreeshipping\Controller\Shipping\Index;

/**
 * Interceptor class for @see \Webkul\Mpfreeshipping\Controller\Shipping\Index
 */
class Interceptor extends \Webkul\Mpfreeshipping\Controller\Shipping\Index implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository, \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory, \Magento\Framework\Api\DataObjectHelper $dataObjectHelper, \Magento\Customer\Model\Customer\Mapper $customerMapper, \Magento\Customer\Model\Session $customerSession)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $customerRepository, $customerDataFactory, $dataObjectHelper, $customerMapper, $customerSession);
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
