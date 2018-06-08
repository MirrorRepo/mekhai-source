<?php
namespace Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver;

/**
 * Interceptor class for @see \Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver
 */
class Interceptor extends \Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Review\Model\ReviewFactory $reviewFactory)
    {
        $this->___init();
        parent::__construct($reviewFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        if (!$pluginInfo) {
            return parent::execute($observer);
        } else {
            return $this->___callPlugins('execute', func_get_args(), $pluginInfo);
        }
    }
}
