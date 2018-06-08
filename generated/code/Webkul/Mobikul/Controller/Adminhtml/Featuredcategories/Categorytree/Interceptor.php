<?php
namespace Webkul\Mobikul\Controller\Adminhtml\Featuredcategories\Categorytree;

/**
 * Interceptor class for @see \Webkul\Mobikul\Controller\Adminhtml\Featuredcategories\Categorytree
 */
class Interceptor extends \Webkul\Mobikul\Controller\Adminhtml\Featuredcategories\Categorytree implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel, \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository)
    {
        $this->___init();
        parent::__construct($context, $categoryResourceModel, $categoryRepository);
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
