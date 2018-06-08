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

    class ApplyCoupon extends AbstractCheckout      {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["responseCode"] = 0;
            try {
                $wholeData       = $this->getRequest()->getPostValue();
                $this->_headers  = $this->getRequest()->getHeaders();
                $this->_helper->log(__CLASS__, "logClass", $wholeData);
                $this->_helper->log($wholeData, "logParams", $wholeData);
                $this->_helper->log($this->_headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $itemId       = $this->_helper->validate($wholeData, "itemId")       ? $wholeData["itemId"]       : 0;
                        $quoteId      = $this->_helper->validate($wholeData, "quoteId")      ? $wholeData["quoteId"]      : 0;
                        $storeId      = $this->_helper->validate($wholeData, "storeId")      ? $wholeData["storeId"]      : 1;
                        $customerId   = $this->_helper->validate($wholeData, "customerId")   ? $wholeData["customerId"]   : 0;
                        $couponCode   = $this->_helper->validate($wholeData, "couponCode")   ? $wholeData["couponCode"]   : "";
                        $removeCoupon = $this->_helper->validate($wholeData, "removeCoupon") ? $wholeData["removeCoupon"] : 0;
                        $environment  = $this->_emulate->startEnvironmentEmulation($storeId);
                        $quote        = new \Magento\Framework\DataObject();
                        if ($customerId != 0) {
                            $quoteCollection = $this->_quoteFactory->create()->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                // ->addFieldToFilter("store_id", $storeId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if ($quoteId != 0)
                            $quote = $this->_quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                        if((bool)$removeCoupon)
                            $couponCode = "";
                        $codeLength = strlen($couponCode);
                        $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;
                        $itemsCount = $quote->getItemsCount();
                        if ($itemsCount) {
                            $quote->getShippingAddress()->setCollectShippingRates(true);
                            $quote->setCouponCode($isCodeLengthValid ? $couponCode : "")->collectTotals();
                            $this->_quoteRepository->save($quote);
                        }
                        if ($codeLength) {
                            $escaper = $this->_objectManager->get("Magento\Framework\Escaper");
                            if (!$itemsCount) {
                                if ($isCodeLengthValid) {
                                    $coupon = $this->_couponFactory->create();
                                    $coupon->load($couponCode, "code");
                                    if ($coupon->getId()) {
                                        $quote->setCouponCode($couponCode)->save();
                                        $returnArray["success"] = true;
                                        $returnArray["message"] = __("You used coupon code '%1'.", $escaper->escapeHtml($couponCode));
                                    } else
                                        $returnArray["message"] = __("The coupon code '%1' is not valid.", $escaper->escapeHtml($couponCode));
                                } else
                                    $returnArray["message"] = __("The coupon code '%1' is not valid.", $escaper->escapeHtml($couponCode));
                            } else {
                                if ($isCodeLengthValid && $couponCode == $quote->getCouponCode()) {
                                    $returnArray["success"] = true;
                                    $returnArray["message"] = __("You used coupon code '%1'.", $escaper->escapeHtml($couponCode));
                                } else {
                                    $returnArray["message"] = __("The coupon code '%1' is not valid.", $escaper->escapeHtml($couponCode));
                                    $quote->collectTotals();
                                    $this->_quoteRepository->save($quote);
                                }
                            }
                        } else {
                            $returnArray["success"] = true;
                            $returnArray["message"] = __("You canceled the coupon code.");
                        }
                        $quote->getShippingAddress()->setCollectShippingRates(true);
                        $quote->collectTotals()->save();
                        $this->_emulate->stopEnvironmentEmulation($environment);
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    } else {
                        $returnArray["message"]      = $authData["message"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = __("Invalid Request");
                    $this->_helper->log($returnArray, "logResponse", $wholeData);
                    return $this->getJsonResponse($returnArray);
                }
            } catch (\Exception $e) {
                $returnArray["message"] = __($e->getMessage());
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }