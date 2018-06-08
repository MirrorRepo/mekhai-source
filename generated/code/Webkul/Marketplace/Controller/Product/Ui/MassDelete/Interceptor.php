<?php
namespace Webkul\Marketplace\Controller\Product\Ui\MassDelete;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Product\Ui\MassDelete
 */
class Interceptor extends \Webkul\Marketplace\Controller\Product\Ui\MassDelete implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Registry $coreRegistry, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $sellerProductCollectionFactory)
    {
        $this->___init();
        parent::__construct($context, $filter, $customerSession, $coreRegistry, $productCollectionFactory, $sellerProductCollectionFactory);
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
