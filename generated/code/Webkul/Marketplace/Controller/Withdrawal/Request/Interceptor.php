<?php
namespace Webkul\Marketplace\Controller\Withdrawal\Request;

/**
 * Interceptor class for @see \Webkul\Marketplace\Controller\Withdrawal\Request
 */
class Interceptor extends \Webkul\Marketplace\Controller\Withdrawal\Request implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, \Webkul\Marketplace\Helper\Data $helper, \Webkul\Marketplace\Helper\Email $helperEmail, \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $saleslistColl, \Webkul\Marketplace\Model\ResourceModel\Saleperpartner\CollectionFactory $collectionFactory, \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository)
    {
        $this->___init();
        parent::__construct($context, $formKeyValidator, $helper, $helperEmail, $saleslistColl, $collectionFactory, $customerRepository);
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
