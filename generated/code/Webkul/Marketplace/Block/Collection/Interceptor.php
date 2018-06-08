<?php
namespace Webkul\Marketplace\Block\Collection;

/**
 * Interceptor class for @see \Webkul\Marketplace\Block\Collection
 */
class Interceptor extends \Webkul\Marketplace\Block\Collection implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Block\Product\Context $context, \Magento\Framework\Data\Helper\PostHelper $postDataHelper, \Magento\Framework\Url\Helper\Data $urlHelper, \Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\Catalog\Model\Layer\Resolver $layerResolver, \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository, array $data = array())
    {
        $this->___init();
        parent::__construct($context, $postDataHelper, $urlHelper, $objectManager, $productCollectionFactory, $layerResolver, $categoryRepository, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function _getProductCollection()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, '_getProductCollection');
        if (!$pluginInfo) {
            return parent::_getProductCollection();
        } else {
            return $this->___callPlugins('_getProductCollection', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($product, $imageId, $attributes = array())
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getImage');
        if (!$pluginInfo) {
            return parent::getImage($product, $imageId, $attributes);
        } else {
            return $this->___callPlugins('getImage', func_get_args(), $pluginInfo);
        }
    }
}
