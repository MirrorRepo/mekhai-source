<?php
namespace Webkul\Mpcashondelivery\Controller\Adminhtml\Pricerules\Save;

/**
 * Interceptor class for @see \Webkul\Mpcashondelivery\Controller\Adminhtml\Pricerules\Save
 */
class Interceptor extends \Webkul\Mpcashondelivery\Controller\Adminhtml\Pricerules\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Webkul\Mpcashondelivery\Model\PricerulesFactory $priceruleFactory, \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory)
    {
        $this->___init();
        parent::__construct($context, $countryCollectionFactory, $storeManager, $priceruleFactory, $fileUploaderFactory);
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
