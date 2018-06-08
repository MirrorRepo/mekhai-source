<?php
namespace Webkul\Marketplace\Controller\Adminhtml\Order\Payseller;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Adminhtml\Order\Payseller
 */
class Interceptor extends \Webkul\Marketplace\Controller\Adminhtml\Order\Payseller implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Magento\Framework\Stdlib\DateTime\DateTime $date, \Magento\Framework\Stdlib\DateTime $dateTime, \Magento\Sales\Model\OrderRepository $orderRepository, \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $collectionFactory)
    {
        $this->___init();
        parent::__construct($context, $filter, $date, $dateTime, $orderRepository, $collectionFactory);
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
