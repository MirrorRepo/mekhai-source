<?php
namespace Webkul\Mpcashondelivery\Controller\Pricerules\Masssave;

/**
 * Interceptor class for @see \Webkul\Mpcashondelivery\Controller\Pricerules\Masssave
 */
class Interceptor extends \Webkul\Mpcashondelivery\Controller\Pricerules\Masssave implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Webkul\Mpcashondelivery\Model\PricerulesFactory $priceruleFactory, \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory, \Webkul\Marketplace\Helper\Data $mpHelper)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $formKeyValidator, $countryCollectionFactory, $storeManager, $priceruleFactory, $fileUploaderFactory, $mpHelper);
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
