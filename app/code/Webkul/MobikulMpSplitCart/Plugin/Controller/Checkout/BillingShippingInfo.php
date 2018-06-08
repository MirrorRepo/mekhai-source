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

class BillingShippingInfo {

    public function __construct(
        \Webkul\MobikulMpSplitCart\Helper\Data $helper,
        \Webkul\Mpsplitcart\Helper\Data $splitCartHelper,
        \Magento\Framework\App\Request\Http $request,
        \Webkul\Mobikul\Helper\Data $mobikulHelper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->helper = $helper;
        $this->splitCartHelper = $splitCartHelper;
        $this->request = $request;
        $this->mobikulHelper = $mobikulHelper;
        $this->cart = $cart;
        $this->quoteFactory = $quoteFactory;
        $this->customerModel = $customerModel;
        $this->customerSession = $customerSession;
    }

    public function beforeExecute(\Webkul\Mobikul\Controller\Checkout\BillingShippingInfo $subject) {
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

                    if ($customerId != 0) {
                        $quoteCollection = $this->quoteFactory->create()
                            ->getCollection()
                            ->addFieldToFilter("customer_id", $customerId)
                            // ->addFieldToFilter("store_id", $storeId)
                            ->addFieldToFilter("is_active", 1)
                            ->addOrder("updated_at", "DESC");
                        $quote   = $quoteCollection->getFirstItem();
                        $quoteId = $quote->getId();
                        if (!$this->customerSession->isLoggedIn()) {
                            $customer = $this->customerModel->load($customerId);
                            $this->customerSession->setCustomerAsLoggedIn($customer);
                        }
                    } else {
                        $quote = $this->quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                        $isGuest = true;
                    }

                    $this->cart->setQuote($quote)->save();

                    // $this->splitCartHelper->getUpdatedQuote(0);

                    if (isset($wholeData['mpslitcart-checkout'])
                        && $wholeData['mpslitcart-checkout']!==""
                    ) {
                        $this->splitCartHelper->getUpdatedQuote(
                            $wholeData['mpslitcart-checkout']
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("BillingShippingInfo beforeExecute : ".$e->getMessage());
        }
    }
}