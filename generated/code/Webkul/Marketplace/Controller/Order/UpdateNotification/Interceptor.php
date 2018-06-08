<?php
namespace Webkul\Marketplace\Controller\Order\UpdateNotification;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Order\UpdateNotification
 */
class Interceptor extends \Webkul\Marketplace\Controller\Order\UpdateNotification implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory, \Magento\Framework\Json\Helper\Data $helper, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\Framework\Controller\Result\RawFactory $resultRawFactory, \Magento\Customer\Model\Session $customerSession)
    {
        $this->___init();
        parent::__construct($context, $storeManager, $customerFactory, $helper, $resultJsonFactory, $resultRawFactory, $customerSession);
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
