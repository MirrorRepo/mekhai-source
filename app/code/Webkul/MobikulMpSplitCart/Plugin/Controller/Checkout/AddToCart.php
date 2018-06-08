<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MobikulMpSplitCart
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MobikulMpSplitCart\Plugin\Controller\Checkout;

use \Magento\Framework\Controller\ResultFactory;

class AddToCart {

    public function __construct(
        \Webkul\Mobikul\Helper\Data $mobikulHelper,
        \Magento\Framework\App\Request\Http $request,
        \Webkul\MobikulMpSplitCart\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customerModel,
        \Webkul\Mpsplitcart\Helper\Data $splitCartHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        $this->mobikulHelper = $mobikulHelper;
        $this->request = $request;
        $this->helper = $helper;
        $this->cart = $cart;
        $this->quoteFactory = $quoteFactory;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->customerModel = $customerModel;
        $this->splitCartHelper = $splitCartHelper;
        $this->jsonHelper = $jsonHelper;
        $this->resultFactory = $resultFactory;
    }

    public function aroundExecute(\Webkul\Mobikul\Controller\Checkout\AddToCart $subject, callable $proceed)     {
        try {
            $wholeData       = $this->request->getPostValue();
            $isGuest = false;
            if ($wholeData) {
                $authKey     = $this->request->getHeader("authKey");
                $apiKey      = $this->request->getHeader("apiKey");
                $apiPassword = $this->request->getHeader("apiPassword");
                $authData    = $this->mobikulHelper->isAuthorized($authKey, $apiKey, $apiPassword);
                if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                    $quoteId         = $this->mobikulHelper->validate($wholeData, "quoteId")         ? $wholeData["quoteId"]         : 0;
                    $storeId         = $this->mobikulHelper->validate($wholeData, "storeId")         ? $wholeData["storeId"]         : 1;
                    $customerId      = $this->mobikulHelper->validate($wholeData, "customerId")      ? $wholeData["customerId"]      : 0;

                    if ($customerId == 0 && $quoteId == 0) {
                        $quote = $this->quoteFactory->create()
                            ->setStoreId($storeId)
                            ->setIsActive(true)
                            ->setIsMultiShipping(false)
                            ->save();
                        $quote->getBillingAddress();
                        $quote->getShippingAddress()->setCollectShippingRates(true);
                        $quote->collectTotals()->save();
                        $quoteId = (int) $quote->getId();
                    }

                    if ($customerId != 0) {
                        $quoteCollection = $this->quoteFactory->create()
                            ->getCollection()
                            ->addFieldToFilter("customer_id", $customerId)
                            ->addFieldToFilter("store_id", $storeId)
                            ->addFieldToFilter("is_active", 1)
                            ->addOrder("updated_at", "DESC");
                        $quote   = $quoteCollection->getFirstItem();
                        $quoteId = $quote->getId();
                        if ($quote->getId() < 0 || !$quoteId) {
                            $quote = $this->quoteFactory->create()
                                ->setStoreId($storeId)
                                ->setIsActive(true)
                                ->setIsMultiShipping(false)
                                ->save();
                            $quoteId = (int) $quote->getId();
                            $customer = $this->customerRepository->getById($customerId);
                            $quote->assignCustomer($customer);
                            $quote->setCustomer($customer);
                            $quote->getBillingAddress();
                            $quote->getShippingAddress()->setCollectShippingRates(true);
                            $quote->collectTotals()->save();
                        }
                        if (!$this->customerSession->isLoggedIn()) {
                            $customer = $this->customerModel->load($customerId);
                            $this->customerSession->setCustomerAsLoggedIn($customer);
                        }
                    } else {
                        $quote = $this->quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                        $isGuest = true;
                    }
                }
                $response = $proceed();
                $this->cart->setQuote($quote)->save();
                /* if ($isGuest && $this->splitCartHelper->checkMpsplitcartStatus()) {
                    $virtualCart = $this->splitCartHelper->addQuoteToVirtualCart();
                    if ($virtualCart && !empty($virtualCart)) {
                        $this->helper->logDataInLogger("AddToCart aroundExecute virtualCart : ".json_encode($virtualCart));
                        $returnArray = $this->jsonHelper->jsonDecode($response->getRawData());
                        $returnArray['virtual_cart'] = $virtualCart;
                        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                        $resultJson->setData($returnArray);
                        return $resultJson;
                    }
                } */
                return $response;
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("AddToCart aroundExecute : ".$e->getMessage());
        }
    }
}