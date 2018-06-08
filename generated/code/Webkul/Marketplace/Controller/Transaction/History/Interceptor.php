<?php
namespace Webkul\Marketplace\Controller\Transaction\History;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Transaction\History
 */
class Interceptor extends \Webkul\Marketplace\Controller\Transaction\History implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Customer\Model\Session $customerSession, \Webkul\Marketplace\Model\ResourceModel\Sellertransaction\CollectionFactory $collectionFactory, \Webkul\Marketplace\Helper\Data $helper, \Webkul\Marketplace\Helper\Notification $notificationHelper)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $customerSession, $collectionFactory, $helper, $notificationHelper);
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
