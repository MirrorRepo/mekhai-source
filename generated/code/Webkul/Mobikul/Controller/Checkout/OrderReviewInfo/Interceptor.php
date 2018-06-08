<?php
namespace Webkul\Mobikul\Controller\Checkout\OrderReviewInfo;

/**
 * Interceptor class for @see \Webkul\Mobikul\Controller\Checkout\OrderReviewInfo
 */
class Interceptor extends \Webkul\Mobikul\Controller\Checkout\OrderReviewInfo implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Store\Model\App\Emulation $emulate, \Webkul\Mobikul\Helper\Data $helper, \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender, \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender, \Magento\Quote\Model\Quote\Address\ToOrder $quoteAddressToOrder, \Magento\Customer\Model\Url $customerUrl, \Magento\Directory\Model\Country $country, \Magento\Framework\Registry $coreRegistry, \Magento\Quote\Model\Quote\Item\ToOrderItem $quoteItemToOrderItem, \Magento\Customer\Api\AccountManagementInterface $accountManagement, \Webkul\Mobikul\Helper\Catalog $helperCatalog, \Webkul\Mobikul\Model\DeviceToken $deviceToken, \Magento\Quote\Model\QuoteFactory $quoteFactory, \Magento\Sales\Model\OrderFactory $orderFactory, \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Checkout\Model\CartFactory $cartFactory, \Magento\Customer\Model\Session $customerSession, \Magento\Quote\Model\QuoteValidator $quoteValidator, \Magento\Quote\Model\Quote\Address\ToOrderAddress $quoteAddressToOrderAddress, \Magento\Quote\Model\Quote\Payment\ToOrderPayment $quotePaymentToOrderPayment, \Magento\Framework\Pricing\Helper\Data $priceHelper, \Magento\SalesRule\Model\CouponFactory $couponFactory, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Framework\DataObject\Copy $objectCopyService, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Customer\Model\CustomerFactory $customerFactory, \Magento\Framework\Api\DataObjectHelper $dataObjectHelper, \Magento\Quote\Model\CustomerManagement $customerManagement, \Magento\Quote\Api\CartRepositoryInterface $quoteRepository, \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry, \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository, \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollection, \Magento\Downloadable\Helper\Catalog\Product\Configuration $downloadableConfiguration)
    {
        $this->___init();
        parent::__construct($context, $emulate, $helper, $orderSender, $invoiceSender, $quoteAddressToOrder, $customerUrl, $country, $coreRegistry, $quoteItemToOrderItem, $accountManagement, $helperCatalog, $deviceToken, $quoteFactory, $orderFactory, $transactionBuilder, $checkoutSession, $cartFactory, $customerSession, $quoteValidator, $quoteAddressToOrderAddress, $quotePaymentToOrderPayment, $priceHelper, $couponFactory, $productFactory, $objectCopyService, $storeManager, $customerFactory, $dataObjectHelper, $customerManagement, $quoteRepository, $stockRegistry, $customerRepository, $regionCollection, $downloadableConfiguration);
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
