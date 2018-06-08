<?php
namespace Webkul\MarketplacePreorder\Controller\Adminhtml\PreorderList\SendMail;

/**
 * Interceptor class for @see \Webkul\MarketplacePreorder\Controller\Adminhtml\PreorderList\SendMail
 */
class Interceptor extends \Webkul\MarketplacePreorder\Controller\Adminhtml\PreorderList\SendMail implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\CollectionFactory $preorderItemCollection, \Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterfaceFactory $preorderItemsFactory, \Webkul\MarketplacePreorder\Model\PreorderItemsRepository $itemsRepository, \Magento\Framework\Api\DataObjectHelper $dataObjectHelper, \Magento\Customer\Model\Session $customerSession, \Webkul\MarketplacePreorder\Helper\Data $preorderHelper, \Webkul\MarketplacePreorder\Helper\Email $emailHelper, \Magento\Ui\Component\MassAction\Filter $filter)
    {
        $this->___init();
        parent::__construct($context, $preorderItemCollection, $preorderItemsFactory, $itemsRepository, $dataObjectHelper, $customerSession, $preorderHelper, $emailHelper, $filter);
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
