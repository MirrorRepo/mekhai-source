<?php
    /**
    * Webkul Software.
    *
    * @category  Webkul
    * @package   Webkul_Mobikul
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Controller\Checkout;
    use Magento\Store\Model\App\Emulation;
    use Magento\Framework\App\Action\Action;
    use Magento\Framework\App\Action\Context;
    use Webkul\Mobikul\Helper\Data as HelperData;
    use Magento\Checkout\Model\Cart as CustomerCart;
    use Magento\Sales\Model\Order\Payment\Transaction;
    use Magento\Customer\Api\AccountManagementInterface;
    use Magento\Sales\Model\Order\Email\Sender\OrderSender;
    use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
    use Magento\Quote\Model\Quote\Address\ToOrder as ToOrderConverter;
    use Magento\Quote\Model\Quote\Item\ToOrderItem as ToOrderItemConverter;
    use Magento\Quote\Model\Quote\Payment\ToOrderPayment as ToOrderPaymentConverter;
    use Magento\Quote\Model\Quote\Address\ToOrderAddress as ToOrderAddressConverter;

    abstract class AbstractCheckout extends \Webkul\Mobikul\Controller\ApiController    {

        protected $_country;
        protected $_emulate;
        protected $_orderSender;
        protected $_priceHelper;
        protected $_deviceToken;
        protected $_cartFactory;
        protected $_customerUrl;
        protected $_orderFactory;
        protected $_quoteFactory;
        protected $_coreRegistry;
        protected $_storeManager;
        protected $_helperCatalog;
        protected $_invoiceSender;
        protected $_couponFactory;
        protected $_productFactory;
        protected $_quoteValidator;
        protected $_customerFactory;
        protected $_quoteRepository;
        protected $_checkoutSession;
        protected $_regionCollection;
        protected $_dataObjectHelper;
        protected $_requestInfoFilter;
        protected $_accountManagement;
        protected $_objectCopyService;
        protected $_customerRepository;
        protected $_transactionBuilder;
        protected $_downloadableConfiguration;

        public function __construct(
            Context $context,
            Emulation $emulate,
            HelperData $helper,
            OrderSender $orderSender,
            InvoiceSender $invoiceSender,
            ToOrderConverter $quoteAddressToOrder,
            \Magento\Customer\Model\Url $customerUrl,
            \Magento\Directory\Model\Country $country,
            \Magento\Framework\Registry $coreRegistry,
            ToOrderItemConverter $quoteItemToOrderItem,
            AccountManagementInterface $accountManagement,
            \Webkul\Mobikul\Helper\Catalog $helperCatalog,
            \Webkul\Mobikul\Model\DeviceToken $deviceToken,
            \Magento\Quote\Model\QuoteFactory $quoteFactory,
            \Magento\Sales\Model\OrderFactory $orderFactory,
            Transaction\BuilderInterface $transactionBuilder,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Checkout\Model\CartFactory $cartFactory,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Quote\Model\QuoteValidator $quoteValidator,
            ToOrderAddressConverter $quoteAddressToOrderAddress,
            ToOrderPaymentConverter $quotePaymentToOrderPayment,
            \Magento\Framework\Pricing\Helper\Data $priceHelper,
            \Magento\SalesRule\Model\CouponFactory $couponFactory,
            \Magento\Catalog\Model\ProductFactory $productFactory,
            \Magento\Framework\DataObject\Copy $objectCopyService,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Customer\Model\CustomerFactory $customerFactory,
            \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
            \Magento\Quote\Model\CustomerManagement $customerManagement,
            \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
            \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
            \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
            \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollection,
            \Magento\Downloadable\Helper\Catalog\Product\Configuration $downloadableConfiguration
        ) {
            $this->_country                    = $country;
            $this->_emulate                    = $emulate;
            $this->_priceHelper                = $priceHelper;
            $this->_cartFactory                = $cartFactory;
            $this->_customerUrl                = $customerUrl;
            $this->_deviceToken                = $deviceToken;
            $this->_orderSender                = $orderSender;
            $this->_coreRegistry               = $coreRegistry;
            $this->_orderFactory               = $orderFactory;
            $this->_storeManager               = $storeManager;
            $this->_quoteFactory               = $quoteFactory;
            $this->_helperCatalog              = $helperCatalog;
            $this->_couponFactory              = $couponFactory;
            $this->_invoiceSender              = $invoiceSender;
            $this->_stockRegistry              = $stockRegistry;
            $this->_quoteValidator             = $quoteValidator;
            $this->_productFactory             = $productFactory;
            $this->_quoteRepository            = $quoteRepository;
            $this->_customerFactory            = $customerFactory;
            $this->_checkoutSession            = $checkoutSession;
            $this->_customerSession            = $customerSession;
            $this->_regionCollection           = $regionCollection;
            $this->_dataObjectHelper           = $dataObjectHelper;
            $this->_accountManagement          = $accountManagement;
            $this->_objectCopyService          = $objectCopyService;
            $this->_customerRepository         = $customerRepository;
            $this->_transactionBuilder         = $transactionBuilder;
            $this->_customerManagement         = $customerManagement;
            $this->_quoteAddressToOrder        = $quoteAddressToOrder;
            $this->_quoteItemToOrderItem       = $quoteItemToOrderItem;
            $this->_downloadableConfiguration  = $downloadableConfiguration;
            $this->_quotePaymentToOrderPayment = $quotePaymentToOrderPayment;
            $this->_quoteAddressToOrderAddress = $quoteAddressToOrderAddress;
            parent::__construct($helper, $context);
        }

        protected function _customerEmailExists($email, $websiteId = null)  {
            $customer = $this->_customerFactory->create();
            if ($websiteId)
                $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($email);
            if ($customer->getId())
                return $customer;
            return false;
        }

        protected function _validateCustomerData($data)     {
            $storeId      = $data["storeId"];
            $customerData = [];
            $customer     = null;
            $customerForm = $this->_objectManager->create("\Magento\Customer\Model\Form")->setFormCode("checkout_register");
            $quote        = new \Magento\Framework\DataObject();
            if ($data["customerId"] != 0) {
                $customerId = $data["customerId"];
                $quoteCollection = $this->_quoteFactory->create()->getCollection()
                    ->addFieldToFilter("is_active", 1)
                    ->addFieldToFilter("customer_id", $customerId)
                    ->addOrder("updated_at", "DESC");
                $quote = $quoteCollection->getFirstItem();
            }
            if ($data["quoteId"] != 0) {
                $quoteId = $data["quoteId"];
                $quote   = $this->_quoteFactory->create()->setStoreId($storeId)->load($quoteId);
            }
            if ($quote->getCustomerId()) {
                $customer = $quote->getCustomer();
                $customer = $this->_customerFactory->create()->load($customer->getId());
                $customerForm->setEntity($customer);
                $customerData = $customer->getData();
            } else {
                $customer = $this->_customerFactory->create();
                $customerForm->setEntity($customer);
                $newAddress  = [];
                $billingData = $data["billingData"];
                $billingData = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($billingData);
                if (isset($billingData["newAddress"])) {
                    if (!empty($billingData["newAddress"]))
                        $newAddress = $billingData["newAddress"];
                }
                $customerData = array(
                    "lastname"   => $newAddress["lastName"],
                    "firstname"  => $newAddress["firstName"],
                    "dob"        => $this->_helper->validate($newAddress, "dob")        ? $newAddress["dob"]        : "",
                    "email"      => $this->_helper->validate($newAddress, "email")      ? $newAddress["email"]      : "",
                    "prefix"     => $this->_helper->validate($newAddress, "prefix")     ? $newAddress["prefix"]     : "",
                    "suffix"     => $this->_helper->validate($newAddress, "suffix")     ? $newAddress["suffix"]     : "",
                    "taxvat"     => $this->_helper->validate($newAddress, "taxvat")     ? $newAddress["taxvat"]     : "",
                    "gender"     => $this->_helper->validate($newAddress, "gender")     ? $newAddress["gender"]     : "",
                    "middlename" => $this->_helper->validate($newAddress, "middleName") ? $newAddress["middleName"] : ""
                );
            }
            $customerErrors = true;
            if ($customerErrors !== true)
                return ["error"=>1, "message"=>implode(", ", $customerErrors)];
            if ($quote->getCustomerId())
                return true;
            if ($quote->getCheckoutMethod() == "register") {
                $customerForm->compactData($customerData);
                $customer->setPassword($data["password"]);
                $customer->setConfirmation($data["confirmPassword"]);
                $customer->setPasswordConfirmation($data["confirmPassword"]);
                $result = $customer->validate();
                if (true !== $result && is_array($result))
                    return ["error"=>-1, "message"=>implode(", ", $result)];
            }
            if ($quote->getCheckoutMethod() == "register") {
                $quote->setPasswordHash($customer->encryptPassword($customer->getPassword()));
                $quote->setCustomer($customer);
            }
            $quote->getBillingAddress()->setEmail($customer->getEmail());
            $this->_objectManager
                ->create("\Magento\Framework\DataObject\Copy")
                ->copyFieldsetToTarget("customer_account", "to_quote", $customer, $quote);
            return true;
        }

    }