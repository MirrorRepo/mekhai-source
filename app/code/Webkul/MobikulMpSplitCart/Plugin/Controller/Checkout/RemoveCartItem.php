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

class RemoveCartItem {

    public function __construct(
        \Webkul\Mobikul\Helper\Data $mobikulHelper,
        \Magento\Framework\App\Request\Http $request,
        \Webkul\MobikulMpSplitCart\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
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
        $this->splitCartHelper = $splitCartHelper;
        $this->resultFactory = $resultFactory;
    }

    public function aroundExecute(\Webkul\Mobikul\Controller\Checkout\RemoveCartItem $subject, callable $proceed)     {
        try {
            $wholeData       = $this->request->getPostValue();
            $isGuest = false;
            if ($wholeData) {
                $authKey     = $this->request->getHeader("authKey");
                $apiKey      = $this->request->getHeader("apiKey");
                $apiPassword = $this->request->getHeader("apiPassword");
                $authData    = $this->mobikulHelper->isAuthorized($authKey, $apiKey, $apiPassword);
                if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                    $itemId      = $this->mobikulHelper->validate($wholeData, "itemId")     ? $wholeData["itemId"]     : 0;
                    $customerId  = $this->mobikulHelper->validate($wholeData, "customerId")      ? $wholeData["customerId"]      : 0;

                    if ($customerId != 0) {
                        if (!$this->customerSession->isLoggedIn()) {
                            $customer = $this->customerModel->load($customerId);
                            $this->customerSession->setCustomerAsLoggedIn($customer);
                        }
                    } else {
                        $isGuest = true;
                    }

                    $response = $proceed();

                    $this->cart->removeItem($itemId);
                    $this->splitCartHelper->saveCart();
                    $this->splitCartHelper->updateCart();

                    // $this->cart->saveQuote($quote);
                    /* if ($isGuest && $this->splitCartHelper->checkMpsplitcartStatus()) {
                        $virtualCart = $this->splitCartHelper->addQuoteToVirtualCart();
                        $returnArray = $this->jsonHelper->jsonDecode($response->getRawData());
                        $returnArray['virtual_cart'] = $virtualCart;
                        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                        $resultJson->setData($returnArray);
                        return $resultJson;
                    } */
                    return $response;
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("RemoveCartItem aroundExecute : ".$e->getMessage());
        }
    }
}