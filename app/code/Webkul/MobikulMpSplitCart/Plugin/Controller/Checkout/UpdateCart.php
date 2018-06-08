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

class UpdateCart {

    public function __construct(
        \Webkul\Mobikul\Helper\Data $mobikulHelper,
        \Magento\Framework\App\Request\Http $request,
        \Webkul\MobikulMpSplitCart\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Locale\Resolver $resolver,
        \Webkul\Mpsplitcart\Helper\Data $splitCartHelper,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        $this->mobikulHelper = $mobikulHelper;
        $this->request = $request;
        $this->helper = $helper;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        $this->customerModel = $customerModel;
        $this->jsonHelper = $jsonHelper;
        $this->resolver = $resolver;
        $this->splitCartHelper = $splitCartHelper;
        $this->resultFactory = $resultFactory;
    }

    public function aroundExecute(\Webkul\Mobikul\Controller\Checkout\UpdateCart $subject, callable $proceed)     {
        try {
            $wholeData       = $this->request->getPostValue();
            $isGuest = false;
            if ($wholeData) {
                $authKey     = $this->request->getHeader("authKey");
                $apiKey      = $this->request->getHeader("apiKey");
                $apiPassword = $this->request->getHeader("apiPassword");
                $authData    = $this->mobikulHelper->isAuthorized($authKey, $apiKey, $apiPassword);
                if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                    $itemIds         = $this->mobikulHelper->validate($wholeData, "itemIds")    ? $wholeData["itemIds"]    : "[]";
                    $itemQtys    = $this->mobikulHelper->validate($wholeData, "itemQtys")   ? $wholeData["itemQtys"]   : "[]";
                    $customerId      = $this->mobikulHelper->validate($wholeData, "customerId")      ? $wholeData["customerId"]      : 0;
                    $itemIds     = $this->jsonHelper->jsonDecode($itemIds);
                    $itemQtys    = $this->jsonHelper->jsonDecode($itemQtys);

                    if ($customerId != 0) {
                        if (!$this->customerSession->isLoggedIn()) {
                            $customer = $this->customerModel->load($customerId);
                            $this->customerSession->setCustomerAsLoggedIn($customer);
                        }
                    } else {
                        $isGuest = true;
                    }
                    $cartData = [];
                    foreach ($itemIds as $key=>$itemId) {
                        $cartData[$itemId] = ["qty"=>$itemQtys[$key]];
                    }
                    $filter = new \Magento\Framework\Filter\LocalizedToNormalized([
                        "locale" => $this->resolver->getLocale()
                    ]);
                    foreach ($cartData as $index=>$eachData) {
                        if (isset($eachData["qty"])) {
                            $cartData[$index]["qty"] = $filter->filter(trim($eachData["qty"]));
                        }
                    }
                    $this->cart->updateItems($cartData)->save();

                    $response = $proceed();
                    // $this->cart->setQuote($quote)->save();
                    /* if ($isGuest && $this->splitCartHelper->checkMpsplitcartStatus()) {
                        $virtualCart = $this->splitCartHelper->addQuoteToVirtualCart();
                        if ($virtualCart && !empty($virtualCart)) {
                            $this->helper->logDataInLogger("UpdateCart aroundExecute virtualCart : ".json_encode($virtualCart));
                            $returnArray = $this->jsonHelper->jsonDecode($response->getRawData());
                            $returnArray['virtual_cart'] = $virtualCart;
                            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                            $resultJson->setData($returnArray);
                            return $resultJson;
                        }
                    } */
                    return $response;
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("UpdateCart aroundExecute : ".$e->getMessage());
        }
    }
}