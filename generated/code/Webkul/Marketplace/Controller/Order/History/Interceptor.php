<?php
namespace Webkul\Marketplace\Controller\Order\History;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Order\History
 */
class Interceptor extends \Webkul\Marketplace\Controller\Order\History implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Customer\Model\Session $customerSession, \Webkul\Marketplace\Helper\Data $helperData, \Webkul\Marketplace\Helper\Notification $notificationHelper, \Webkul\Marketplace\Model\ResourceModel\Orders\CollectionFactory $collectionFactory)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $customerSession, $helperData, $notificationHelper, $collectionFactory);
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
