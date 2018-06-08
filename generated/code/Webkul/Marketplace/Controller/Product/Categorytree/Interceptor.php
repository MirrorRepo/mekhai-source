<?php
namespace Webkul\Marketplace\Controller\Product\Categorytree;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Product\Categorytree
 */
class Interceptor extends \Webkul\Marketplace\Controller\Product\Categorytree implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository, \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel)
    {
        $this->___init();
        parent::__construct($context, $categoryRepository, $categoryResourceModel);
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
