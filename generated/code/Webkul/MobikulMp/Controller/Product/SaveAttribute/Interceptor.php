<?php
namespace Webkul\MobikulMp\Controller\Product\SaveAttribute;

/**
 * Interceptor class for @see \Webkul\MobikulMp\Controller\Product\SaveAttribute
 */
class Interceptor extends \Webkul\MobikulMp\Controller\Product\SaveAttribute implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Webkul\Mobikul\Helper\Data $helper, \Magento\Eav\Model\Entity $entityModel, \Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Category $category, \Magento\Catalog\Model\Product\Url $productUrl, \Webkul\Mobikul\Model\Category\Tree $categoryTree, \Webkul\Marketplace\Helper\Data $marketplaceHelper, \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeModel)
    {
        $this->___init();
        parent::__construct($context, $helper, $entityModel, $product, $category, $productUrl, $categoryTree, $marketplaceHelper, $attributeModel);
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
