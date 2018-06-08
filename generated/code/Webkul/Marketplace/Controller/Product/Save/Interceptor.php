<?php
namespace Webkul\Marketplace\Controller\Product\Save;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Product\Save
 */
class Interceptor extends \Webkul\Marketplace\Controller\Product\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, \Webkul\Marketplace\Controller\Product\SaveProduct $saveProduct, \Magento\Catalog\Model\ResourceModel\Product $productResourceModel)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $formKeyValidator, $saveProduct, $productResourceModel);
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
