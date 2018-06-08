<?php
namespace Magento\CatalogSearch\Model\Advanced;

/**
 * Interceptor class for @see \Magento\CatalogSearch\Model\Advanced
 */
class Interceptor extends \Magento\CatalogSearch\Model\Advanced implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory, \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility, \Magento\Catalog\Model\Config $catalogConfig, \Magento\Directory\Model\CurrencyFactory $currencyFactory, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\CatalogSearch\Model\ResourceModel\AdvancedFactory $advancedFactory, array $data = array())
    {
        $this->___init();
        parent::__construct($context, $registry, $attributeCollectionFactory, $catalogProductVisibility, $catalogConfig, $currencyFactory, $productFactory, $storeManager, $productCollectionFactory, $advancedFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCollection()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getProductCollection');
        if (!$pluginInfo) {
            return parent::getProductCollection();
        } else {
            return $this->___callPlugins('getProductCollection', func_get_args(), $pluginInfo);
        }
    }
}
