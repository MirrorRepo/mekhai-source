<?php
namespace Webkul\Customattribute\Controller\Adminhtml\Index\Massenable;

/**
 * Interceptor class for @see \Webkul\Customattribute\Controller\Adminhtml\Index\Massenable
 */
class Interceptor extends \Webkul\Customattribute\Controller\Adminhtml\Index\Massenable implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Magento\Framework\Stdlib\DateTime\DateTime $date, \Magento\Framework\Stdlib\DateTime $dateTime, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository, \Webkul\Customattribute\Model\ResourceModel\Systemattribute\CollectionFactory $collectionFactory)
    {
        $this->___init();
        parent::__construct($context, $filter, $date, $dateTime, $storeManager, $productRepository, $collectionFactory);
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
