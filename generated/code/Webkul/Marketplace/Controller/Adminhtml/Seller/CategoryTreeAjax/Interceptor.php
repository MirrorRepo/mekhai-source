<?php
namespace Webkul\Marketplace\Controller\Adminhtml\Seller\CategoryTreeAjax;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Adminhtml\Seller\CategoryTreeAjax
 */
class Interceptor extends \Webkul\Marketplace\Controller\Adminhtml\Seller\CategoryTreeAjax implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository, \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel, \Magento\Framework\Json\Helper\Data $jsonHelper)
    {
        $this->___init();
        parent::__construct($context, $categoryRepository, $categoryResourceModel, $jsonHelper);
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
