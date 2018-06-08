<?php
namespace Webkul\MobikulMp\Controller\Marketplace\BecomeSeller;

/**
 * Interceptor class for @see \Webkul\MobikulMp\Controller\Marketplace\BecomeSeller
 */
class Interceptor extends \Webkul\MobikulMp\Controller\Marketplace\BecomeSeller implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Store\Model\App\Emulation $emulate, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\Filesystem $filesystem, \Magento\Sales\Model\OrderRepository $orderRepository, \Magento\Sales\Model\Order $order, \Webkul\Mobikul\Helper\Data $helper, \Magento\Framework\Escaper $escaper, \Webkul\Marketplace\Model\Seller $seller, \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $orderCollectionFactory, \Magento\Catalog\Model\Category $category, \Magento\Customer\Model\Customer $customer, \Magento\Catalog\Model\Product $productModel, \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $sellerProductCollectionFactory, \Webkul\Mobikul\Helper\Catalog $helperCatalog, \Magento\Checkout\Helper\Data $checkoutHelper, \Webkul\Marketplace\Model\Feedback $reviewModel, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\Framework\Json\Helper\Data $jsonHelper, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Stdlib\DateTime\DateTime $date, \Webkul\Marketplace\Helper\Data $marketplaceHelper, \Magento\CatalogInventory\Helper\Stock $stockHelper, \Webkul\Marketplace\Model\Orders $marketplaceOrders, \Webkul\MobikulMp\Helper\Dashboard $dashboardHelper, \Webkul\Marketplace\Block\Order\View $orderViewBlock, \Magento\Framework\Filesystem\DirectoryList $baseDir, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Framework\Stdlib\DateTime\DateTime $dateTime, \Webkul\Marketplace\Model\Product $marketplaceProduct, \Webkul\Marketplace\Model\Feedbackcount $feedBackModel, \Magento\Catalog\Block\Product\Context $productContext, \Magento\Framework\View\Element\Template $viewTemplate, \Webkul\Marketplace\Model\Order\Pdf\Invoice $invoicePdf, \Webkul\Marketplace\Block\Marketplace $marketplaceBlock, \Webkul\Marketplace\Helper\Email $marketplaceEmailHelper, \Webkul\Marketplace\Model\Saleperpartner $saleperPartner, \Webkul\Marketplace\Model\Saleslist $marketplaceSaleList, \Webkul\Marketplace\Model\Order\Pdf\Shipment $shipmentPdf, \Webkul\Marketplace\Helper\Orders $marketplaceOrderhelper, \Webkul\Marketplace\Model\ResourceModel\Sellertransaction\CollectionFactory $transactionCollectionFactory, \Magento\Framework\App\Response\Http\FileFactory $fileFactory, \Webkul\Marketplace\Model\Sellertransaction $sellerTransaction, \Magento\Sales\Model\Order\ItemRepository $orderItemRepository, \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository, \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory, \Webkul\Marketplace\Model\ResourceModel\Seller\Collection $sellerCollection, \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoiceCollection, \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection $shipmentCollection, \Webkul\Marketplace\Model\ResourceModel\Product\Collection $marketplaceProductResource, \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory, \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerlistCollectionFactory, \Webkul\Marketplace\Model\ResourceModel\Orders\Collection $marketplaceOrderResourceCollection)
    {
        $this->___init();
        parent::__construct($context, $emulate, $coreRegistry, $filesystem, $orderRepository, $order, $helper, $escaper, $seller, $orderCollectionFactory, $category, $customer, $productModel, $sellerProductCollectionFactory, $helperCatalog, $checkoutHelper, $reviewModel, $productCollectionFactory, $jsonHelper, $customerSession, $date, $marketplaceHelper, $stockHelper, $marketplaceOrders, $dashboardHelper, $orderViewBlock, $baseDir, $productFactory, $dateTime, $marketplaceProduct, $feedBackModel, $productContext, $viewTemplate, $invoicePdf, $marketplaceBlock, $marketplaceEmailHelper, $saleperPartner, $marketplaceSaleList, $shipmentPdf, $marketplaceOrderhelper, $transactionCollectionFactory, $fileFactory, $sellerTransaction, $orderItemRepository, $eavAttribute, $productRepository, $fileUploaderFactory, $sellerCollection, $invoiceCollection, $shipmentCollection, $marketplaceProductResource, $countryCollectionFactory, $sellerlistCollectionFactory, $marketplaceOrderResourceCollection);
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
