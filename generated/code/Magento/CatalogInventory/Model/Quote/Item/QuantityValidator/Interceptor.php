<?php
namespace Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;

/**
 * Interceptor class for @see \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator
 */
class Interceptor extends \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option $optionInitializer, \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem $stockItemInitializer, \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry, \Magento\CatalogInventory\Api\StockStateInterface $stockState)
    {
        $this->___init();
        parent::__construct($optionInitializer, $stockItemInitializer, $stockRegistry, $stockState);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(\Magento\Framework\Event\Observer $observer)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'validate');
        if (!$pluginInfo) {
            return parent::validate($observer);
        } else {
            return $this->___callPlugins('validate', func_get_args(), $pluginInfo);
        }
    }
}
