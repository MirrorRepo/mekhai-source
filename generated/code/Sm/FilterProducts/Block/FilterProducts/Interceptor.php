<?php
namespace Sm\FilterProducts\Block\FilterProducts;

/**
 * Interceptor class for @see \Sm\FilterProducts\Block\FilterProducts
 */
class Interceptor extends \Sm\FilterProducts\Block\FilterProducts implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection, \Magento\Framework\App\ResourceConnection $resource, \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility, \Magento\Review\Model\Review $review, \Magento\Catalog\Block\Product\Context $context, array $data = array(), $attr = null)
    {
        $this->___init();
        parent::__construct($collection, $resource, $catalogProductVisibility, $review, $context, $data, $attr);
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
