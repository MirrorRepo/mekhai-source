<?php
namespace Webkul\Mobikul\Controller\Adminhtml\Featuredcategories\MassDisable;

/**
 * Interceptor class for @see \Webkul\Mobikul\Controller\Adminhtml\Featuredcategories\MassDisable
 */
class Interceptor extends \Webkul\Mobikul\Controller\Adminhtml\Featuredcategories\MassDisable implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Ui\Component\MassAction\Filter $filter, \Magento\Backend\App\Action\Context $context, \Webkul\Mobikul\Model\ResourceModel\Featuredcategories\CollectionFactory $collectionFactory, \Magento\Framework\Stdlib\DateTime\DateTime $date, \Webkul\Mobikul\Api\FeaturedcategoriesRepositoryInterface $featuredcategoriesRepository)
    {
        $this->___init();
        parent::__construct($filter, $context, $collectionFactory, $date, $featuredcategoriesRepository);
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
