<?php
namespace Webkul\Mpcashondelivery\Controller\Adminhtml\Products\Massupdate;

/**
 * Interceptor class for @see \Webkul\Mpcashondelivery\Controller\Adminhtml\Products\Massupdate
 */
class Interceptor extends \Webkul\Mpcashondelivery\Controller\Adminhtml\Products\Massupdate implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $collectionFactory, \Magento\Catalog\Model\ProductFactory $catalogProduct)
    {
        $this->___init();
        parent::__construct($context, $filter, $collectionFactory, $catalogProduct);
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
