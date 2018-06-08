<?php
namespace Webkul\Marketplace\Controller\Adminhtml\Seller\MassDisapprove;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Adminhtml\Seller\MassDisapprove
 */
class Interceptor extends \Webkul\Marketplace\Controller\Adminhtml\Seller\MassDisapprove implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Magento\Framework\Stdlib\DateTime\DateTime $date, \Magento\Framework\Stdlib\DateTime $dateTime, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository, \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $collectionFactory, \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor)
    {
        $this->___init();
        parent::__construct($context, $filter, $date, $dateTime, $storeManager, $productRepository, $collectionFactory, $productPriceIndexerProcessor);
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
