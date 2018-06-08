<?php
namespace Sm\RecentlyViewed\Block\RecentlyViewed;

/**
 * Interceptor class for @see \Sm\RecentlyViewed\Block\RecentlyViewed
 */
class Interceptor extends \Sm\RecentlyViewed\Block\RecentlyViewed implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection, \Magento\Framework\App\ResourceConnection $resource, \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility, \Magento\Catalog\Helper\Image $productImageHelper, \Magento\Catalog\Block\Product\Context $context, array $data = array(), $attr = null)
    {
        $this->___init();
        parent::__construct($collection, $resource, $catalogProductVisibility, $productImageHelper, $context, $data, $attr);
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
