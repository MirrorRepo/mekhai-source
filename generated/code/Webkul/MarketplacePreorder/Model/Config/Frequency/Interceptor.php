<?php
namespace Webkul\MarketplacePreorder\Model\Config\Frequency;

/**
 * Interceptor class for @see \Webkul\MarketplacePreorder\Model\Config\Frequency
 */
class Interceptor extends \Webkul\MarketplacePreorder\Model\Config\Frequency implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\App\Config\ValueFactory $configValueFactory, \Magento\Framework\Model\ResourceModel\AbstractResource $resource, \Magento\Framework\Data\Collection\AbstractDb $resourceCollection, \Magento\Framework\App\Config\ScopeConfigInterface $config, \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList, $runModelPath = '', array $data = array())
    {
        $this->___init();
        parent::__construct($context, $registry, $configValueFactory, $resource, $resourceCollection, $config, $cacheTypeList, $runModelPath, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'afterSave');
        if (!$pluginInfo) {
            return parent::afterSave();
        } else {
            return $this->___callPlugins('afterSave', func_get_args(), $pluginInfo);
        }
    }
}
