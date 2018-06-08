<?php
    /**
     * Webkul Software.
     *
     * @category  Webkul
     * @package   Webkul_MobikulMp
     * @author    Webkul
     * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
     * @license   https://store.webkul.com/license.html
     */

    namespace Webkul\MobikulMp\Controller\Marketplace;
    use Magento\Framework\Registry;
    use Magento\Framework\Filesystem;
    use Magento\Store\Model\App\Emulation;
    use Magento\Sales\Model\OrderRepository;
    use Magento\Framework\App\Action\Context;
    use Magento\Framework\App\Filesystem\DirectoryList;
    use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;
    use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as SellerProduct;
    use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as MageProductCollection;
    use Webkul\Marketplace\Model\ResourceModel\Sellertransaction\CollectionFactory as TransactionCollectionFactory;

    abstract class AbstractMarketplace extends \Webkul\Mobikul\Controller\ApiController     {

        protected $_date;
        protected $_order;
        protected $_helper;
        protected $_seller;
        protected $_emulate;
        protected $_escaper;
        protected $_baseDir;
        protected $_category;
        protected $_customer;
        protected $_dateTime;
        protected $_invoicePdf;
        protected $_jsonHelper;
        protected $_reviewModel;
        protected $_imageHelper;
        protected $_stockHelper;
        protected $_fileFactory;
        protected $_shipmentPdf;
        protected $_coreRegistry;
        protected $_viewTemplate;
        protected $_eavAttribute;
        protected $_productModel;
        protected $_feedBackModel;
        protected $_helperCatalog;
        protected $_orderViewBlock;
        protected $_checkoutHelper;
        protected $_productFactory;
        protected $_mediaDirectory;
        protected $_saleperPartner;
        protected $_orderRepository;
        protected $_dashboardHelper;
        protected $_customerSession;
        protected $_marketplaceBlock;
        protected $_sellerCollection;
        protected $_invoiceCollection;
        protected $_marketplaceOrders;
        protected $_sellerTransaction;
        protected $_marketplaceHelper;
        protected $_productRepository;
        protected $_shipmentCollection;
        protected $_marketplaceProduct;
        protected $_fileUploaderFactory;
        protected $_orderItemRepository;
        protected $_marketplaceSaleList;
        protected $_marketplaceEmailHelper;
        protected $_marketplaceOrderhelper;
        protected $_orderCollectionFactory;
        protected $_productCollectionFactory;
        protected $_countryCollectionFactory;
        protected $_marketplaceProductResource;
        protected $_sellerlistCollectionFactory;
        protected $_transactionCollectionFactory;
        protected $_sellerProductCollectionFactory;
        protected $_marketplaceOrderResourceCollection;

        public function __construct(
            Context $context,
            Emulation $emulate,
            Registry $coreRegistry,
            Filesystem $filesystem,
            OrderRepository $orderRepository,
            \Magento\Sales\Model\Order $order,
            \Webkul\Mobikul\Helper\Data $helper,
            \Magento\Framework\Escaper $escaper,
            \Webkul\Marketplace\Model\Seller $seller,
            CollectionFactory $orderCollectionFactory,
            \Magento\Catalog\Model\Category $category,
            \Magento\Customer\Model\Customer $customer,
            \Magento\Catalog\Model\Product $productModel,
            SellerProduct $sellerProductCollectionFactory,
            \Webkul\Mobikul\Helper\Catalog $helperCatalog,
            \Magento\Checkout\Helper\Data $checkoutHelper,
            \Webkul\Marketplace\Model\Feedback $reviewModel,
            MageProductCollection $productCollectionFactory,
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Framework\Stdlib\DateTime\DateTime $date,
            \Webkul\Marketplace\Helper\Data $marketplaceHelper,
            \Magento\CatalogInventory\Helper\Stock $stockHelper,
            \Webkul\Marketplace\Model\Orders $marketplaceOrders,
            \Webkul\MobikulMp\Helper\Dashboard $dashboardHelper,
            \Webkul\Marketplace\Block\Order\View $orderViewBlock,
            \Magento\Framework\Filesystem\DirectoryList $baseDir,
            \Magento\Catalog\Model\ProductFactory $productFactory,
            \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
            \Webkul\Marketplace\Model\Product $marketplaceProduct,
            \Webkul\Marketplace\Model\Feedbackcount $feedBackModel,
            \Magento\Catalog\Block\Product\Context $productContext,
            \Magento\Framework\View\Element\Template $viewTemplate,
            \Webkul\Marketplace\Model\Order\Pdf\Invoice $invoicePdf,
            \Webkul\Marketplace\Block\Marketplace $marketplaceBlock,
            \Webkul\Marketplace\Helper\Email $marketplaceEmailHelper,
            \Webkul\Marketplace\Model\Saleperpartner $saleperPartner,
            \Webkul\Marketplace\Model\Saleslist $marketplaceSaleList,
            \Webkul\Marketplace\Model\Order\Pdf\Shipment $shipmentPdf,
            \Webkul\Marketplace\Helper\Orders $marketplaceOrderhelper,
            TransactionCollectionFactory $transactionCollectionFactory,
            \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
            \Webkul\Marketplace\Model\Sellertransaction $sellerTransaction,
            \Magento\Sales\Model\Order\ItemRepository $orderItemRepository,
            \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
            \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
            \Webkul\Marketplace\Model\ResourceModel\Seller\Collection $sellerCollection,
            \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoiceCollection,
            \Magento\Sales\Model\ResourceModel\Order\Shipment\Collection $shipmentCollection,
            \Webkul\Marketplace\Model\ResourceModel\Product\Collection $marketplaceProductResource,
            \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
            \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerlistCollectionFactory,
            \Webkul\Marketplace\Model\ResourceModel\Orders\Collection $marketplaceOrderResourceCollection
        ) {
            $this->_date                               = $date;
            $this->_order                              = $order;
            $this->_helper                             = $helper;
            $this->_seller                             = $seller;
            $this->_emulate                            = $emulate;
            $this->_escaper                            = $escaper;
            $this->_baseDir                            = $baseDir;
            $this->_category                           = $category;
            $this->_dateTime                           = $dateTime;
            $this->_customer                           = $customer;
            $this->_jsonHelper                         = $jsonHelper;
            $this->_invoicePdf                         = $invoicePdf;
            $this->_stockHelper                        = $stockHelper;
            $this->_shipmentPdf                        = $shipmentPdf;
            $this->_reviewModel                        = $reviewModel;
            $this->_fileFactory                        = $fileFactory;
            $this->_coreRegistry                       = $coreRegistry;
            $this->_productModel                       = $productModel;
            $this->_eavAttribute                       = $eavAttribute;
            $this->_viewTemplate                       = $viewTemplate;
            $this->_feedBackModel                      = $feedBackModel;
            $this->_helperCatalog                      = $helperCatalog;
            $this->_checkoutHelper                     = $checkoutHelper;
            $this->_productFactory                     = $productFactory;
            $this->_saleperPartner                     = $saleperPartner;
            $this->_orderViewBlock                     = $orderViewBlock;
            $this->_orderRepository                    = $orderRepository;
            $this->_customerSession                    = $customerSession;
            $this->_dashboardHelper                    = $dashboardHelper;
            $this->_marketplaceBlock                   = $marketplaceBlock;
            $this->_sellerCollection                   = $sellerCollection;
            $this->_marketplaceOrders                  = $marketplaceOrders;
            $this->_productRepository                  = $productRepository;
            $this->_sellerTransaction                  = $sellerTransaction;
            $this->_invoiceCollection                  = $invoiceCollection;
            $this->_marketplaceHelper                  = $marketplaceHelper;
            $this->_marketplaceProduct                 = $marketplaceProduct;
            $this->_shipmentCollection                 = $shipmentCollection;
            $this->_marketplaceSaleList                = $marketplaceSaleList;
            $this->_fileUploaderFactory                = $fileUploaderFactory;
            $this->_orderItemRepository                = $orderItemRepository;
            $this->_marketplaceEmailHelper             = $marketplaceEmailHelper;
            $this->_marketplaceOrderhelper             = $marketplaceOrderhelper;
            $this->_orderCollectionFactory             = $orderCollectionFactory;
            $this->_countryCollectionFactory           = $countryCollectionFactory;
            $this->_productCollectionFactory           = $productCollectionFactory;
            $this->_marketplaceProductResource         = $marketplaceProductResource;
            $this->_imageHelper                        = $productContext->getImageHelper();
            $this->_sellerlistCollectionFactory        = $sellerlistCollectionFactory;
            $this->_transactionCollectionFactory       = $transactionCollectionFactory;
            $this->_sellerProductCollectionFactory     = $sellerProductCollectionFactory;
            $this->_marketplaceOrderResourceCollection = $marketplaceOrderResourceCollection;
            $this->_mediaDirectory                     = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            parent::__construct($helper, $context);
        }

        public function getProductData($id)    {
            return $this->_objectManager->create("Magento\Catalog\Model\Product")->load($id);
        }

        public function getSalesdetail($productId="")     {
            $data = [
                "clearedat"             => 0,
                "amountearned"          => 0,
                "quantitysold"          => 0,
                "quantitysoldpending"   => 0,
                "quantitysoldconfirmed" => 0
            ];
            $arr = [];
            $quantity = $this->_marketplaceSaleList
                ->getCollection()
                ->addFieldToFilter("mageproduct_id", $productId);
            foreach ($quantity as $rec) {
                $status = $rec->getCpprostatus();
                $data["quantitysold"] = $data["quantitysold"] + $rec->getMagequantity();
                if ($status == 1)
                    $data["quantitysoldconfirmed"] = $data["quantitysoldconfirmed"] + $rec->getMagequantity();
                else
                    $data["quantitysoldpending"] = $data["quantitysoldpending"] + $rec->getMagequantity();
            }
            $amountearned = $this->_marketplaceSaleList
                ->getCollection()
                ->addFieldToFilter("cpprostatus", \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_PENDING)
                ->addFieldToFilter("mageproduct_id", $productId);
            foreach ($amountearned as $rec) {
                $data["amountearned"] = $data["amountearned"] + $rec["actual_seller_amount"];
                $arr[] = $rec["created_at"];
            }
            $data["created_at"] = $arr;
            return $data;
        }

    }