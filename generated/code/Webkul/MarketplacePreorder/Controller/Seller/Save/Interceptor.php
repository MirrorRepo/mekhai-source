<?php
namespace Webkul\MarketplacePreorder\Controller\Seller\Save;

/**
 * Interceptor class for @see \Webkul\MarketplacePreorder\Controller\Seller\Save
 */
class Interceptor extends \Webkul\MarketplacePreorder\Controller\Seller\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterfaceFactory $preorderSellerFactory, \Magento\Framework\Api\DataObjectHelper $dataObjectHelper, \Webkul\MarketplacePreorder\Api\PreorderSellerManagementInterface $sellerManagement, \Magento\Customer\Model\Session $customerSession)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $preorderSellerFactory, $dataObjectHelper, $sellerManagement, $customerSession);
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
