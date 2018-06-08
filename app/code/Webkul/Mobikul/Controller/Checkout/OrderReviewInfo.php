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

    class OrderReviewInfo extends AbstractCheckout   {

        public function execute()   {
            $returnArray                    = [];
            $returnArray["authKey"]         = "";
            $returnArray["message"]         = "";
            $returnArray["success"]         = false;
            $returnArray["cartCount"]       = 0;
            $returnArray["responseCode"]    = 0;
            $returnArray["currencyCode"]    = "";
            $returnArray["billingMethod"]   = "";
            $returnArray["shippingMethod"]  = "";
            $returnArray["billingAddress"]  = "";
            $returnArray["shippingAddress"] = "";
            $returnArray["orderReviewData"] = new \stdClass();
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
                        $width          = $this->_helper->validate($wholeData, "width")          ? $wholeData["width"]          : 1000;
                        $method         = $this->_helper->validate($wholeData, "method")         ? $wholeData["method"]         : "";
                        $cc_cid         = $this->_helper->validate($wholeData, "cc_cid")         ? $wholeData["cc_cid"]         : "";
                        $storeId        = $this->_helper->validate($wholeData, "storeId")        ? $wholeData["storeId"]        : 1;
                        $cc_type        = $this->_helper->validate($wholeData, "cc_type")        ? $wholeData["cc_type"]        : "";
                        $quoteId        = $this->_helper->validate($wholeData, "quoteId")        ? $wholeData["quoteId"]        : 0;
                        $cc_number      = $this->_helper->validate($wholeData, "cc_number")      ? $wholeData["cc_number"]      : "";
                        $customerId     = $this->_helper->validate($wholeData, "customerId")     ? $wholeData["customerId"]     : 0;
                        $cc_exp_year    = $this->_helper->validate($wholeData, "cc_exp_year")    ? $wholeData["cc_exp_year"]    : "";
                        $cc_exp_month   = $this->_helper->validate($wholeData, "cc_exp_month")   ? $wholeData["cc_exp_month"]   : "";
                        $shippingMethod = $this->_helper->validate($wholeData, "shippingMethod") ? $wholeData["shippingMethod"] : "";
                        $environment    = $this->_emulate->startEnvironmentEmulation($storeId);
                        $store          = $this->_objectManager->create("\Magento\Store\Model\Store");
                        $baseCurrency   = $store->getBaseCurrencyCode();
                        $currency       = $this->_helper->validate($wholeData, "currency") ? $wholeData["currency"] : $baseCurrency;
                        $store->setCurrentCurrencyCode($currency);
                        $checkoutHelper = $this->_objectManager->get("\Magento\Checkout\Helper\Data");
                        $quote          = new \Magento\Framework\DataObject();
                        if ($customerId != 0) {
                            $quoteCollection = $this->_quoteFactory->create()
                                ->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if ($quoteId != 0) {
                            $quote = $this->_quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                        }
                        if($quote->getItemsQty()*1 == 0){
                            $returnArray["message"] = __("Sorry Something went wrong !!");
                            return $this->getJsonResponse($returnArray);
                        }
                        else{
                            $returnArray["cartCount"] = $quote->getItemsQty()*1;
                        }
// saving shipping //////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($shippingMethod != ""){
                            $rate = $quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
                            if(!$rate){
                                $returnArray["message"] = __("Invalid shipping method.");
                                return $this->getJsonResponse($returnArray);
                            }
                            $quote->getShippingAddress()->setShippingMethod($shippingMethod);
                        }
//saving payment ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($method != ""){
                            $paymentData = [];
                            $paymentData["method"] = $method;
                            if($cc_cid != "")
                                $paymentData["cc_cid"] = $cc_cid;
                            if($cc_exp_month != "")
                                $paymentData["cc_exp_month"] = $cc_exp_month;
                            if($cc_exp_year != "")
                                $paymentData["cc_exp_year"] = $cc_exp_year;
                            if($cc_number != "")
                                $paymentData["cc_number"] = $cc_number;
                            if($cc_type != "")
                                $paymentData["cc_type"] = $cc_type;
                            if($quote->isVirtual())
                                $quote->getBillingAddress()->setPaymentMethod(isset($method) ? $method : null);
                            else
                                $quote->getShippingAddress()->setPaymentMethod(isset($method) ? $method : null);
                            if(!$quote->isVirtual() && $quote->getShippingAddress())
                                $quote->getShippingAddress()->setCollectShippingRates(true);
                            $paymentData["checks"] = [\Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT, \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY, \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY, \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX, \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL];
                            $payment = $quote->getPayment()->importData($paymentData);
                            $quote->save();
                        }
                        $orderReviewData = [];
                        foreach($quote->getAllVisibleItems() as $item) {
                            $eachItem                = [];
                            $eachItem["productName"] = $this->_helperCatalog->stripTags($item->getName());
                            $customoptions           = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                            $result                  = [];
                            if($customoptions) {
                                if(isset($customoptions["options"]))
                                    $result = array_merge($result, $customoptions["options"]);
                                if(isset($customoptions["additional_options"]))
                                    $result = array_merge($result, $customoptions["additional_options"]);
                                if(isset($customoptions["attributes_info"]))
                                    $result = array_merge($result, $customoptions["attributes_info"]);
                            }
                            if($result){
                                foreach($result as $option){
                                    $eachOption           = [];
                                    $eachOption["label"]  = $this->_helperCatalog->stripTags($option["label"]);
                                    $eachOption["value"]  = $option["value"];
                                    $eachItem["option"][] = $eachOption;
                                }
                            }
                            $eachItem["price"]     = $this->_helperCatalog->stripTags($checkoutHelper->formatPrice($item->getCalculationPrice()));
                            $eachItem["unformattedPrice"] = $item->getCalculationPrice();
                            $eachItem["qty"]       = $item->getQty();
                            $eachItem["thumbNail"] = $this->_helperCatalog->getImageUrl($item->getProduct(), $width/2.5, "product_page_image_small");
                            $eachItem["subTotal"]  = $this->_helperCatalog->stripTags($checkoutHelper->formatPrice($item->getRowTotal()));
                            $orderReviewData["items"][] = $eachItem;
                        }
                        $address = $quote->getBillingAddress();
                        if ($address instanceof \Magento\Framework\DataObject)
                            $returnArray["billingAddress"] = $address->format("html");
                        $returnArray["billingMethod"] = $quote->getPayment()->getMethodInstance()->getTitle();
                        if(!$quote->isVirtual()){
                            $address = $quote->getShippingAddress();
                            if ($address instanceof \Magento\Framework\DataObject)
                                $returnArray["shippingAddress"] = $address->format("html");
                            if ($shippingMethod = $quote->getShippingAddress()->getShippingDescription())
                                $returnArray["shippingMethod"] = $this->_helperCatalog->stripTags($shippingMethod);
                        }
                        $totals = [];
                        if ($quote->isVirtual())
                            $totals = $quote->getBillingAddress()->getTotals();
                        else
                            $totals = $quote->getShippingAddress()->getTotals();
                        if(isset($totals["subtotal"])){
                            $subtotal = $totals["subtotal"];
                            $orderReviewData["subtotal"] = [
                                "title" => $subtotal->getTitle(),
                                "value" => $this->_helperCatalog->stripTags($checkoutHelper->formatPrice($subtotal->getValue())),
                                "unformatedValue" => $subtotal->getValue()
                            ];
                        }
                        if(isset($totals["discount"])){
                            $discount = $totals["discount"];
                            $orderReviewData["discount"] = [
                                "title" => $discount->getTitle(),
                                "value" => $this->_helperCatalog->stripTags($checkoutHelper->formatPrice($discount->getValue())),
                                "unformatedValue" => $discount->getValue()
                            ];
                        }
                        if(isset($totals["tax"])){
                            $tax = $totals["tax"];
                            $orderReviewData["tax"] = [
                                "title" => $tax->getTitle(),
                                "value" => $this->_helperCatalog->stripTags($checkoutHelper->formatPrice($tax->getValue())),
                                "unformatedValue" => $tax->getValue()
                            ];
                        }
                        if(isset($totals["shipping"])){
                            $shipping = $totals["shipping"];
                            $orderReviewData["shipping"] = [
                                "title" => $shipping->getTitle(),
                                "value" => $this->_helperCatalog->stripTags($checkoutHelper->formatPrice($shipping->getValue())),
                                "unformatedValue" => $shipping->getValue()
                            ];
                        }
                        if(isset($totals["grand_total"])){
                            $grandtotal = $totals["grand_total"];
                            $orderReviewData["grandtotal"] = [
                                "title" => $grandtotal->getTitle(),
                                "value" => $this->_helperCatalog->stripTags($checkoutHelper->formatPrice($grandtotal->getValue())),
                                "unformatedValue" => $grandtotal->getValue()
                            ];
                        }
                        $returnArray["orderReviewData"] = $orderReviewData;
                        $returnArray["currencyCode"]    = $this->_storeManager->getStore()->getCurrentCurrencyCode();
                        $returnArray["success"]         = true;
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
                    $returnArray["message"]      = __("Invalid Request");
                    $returnArray["responseCode"] = 0;
                    $this->_helper->log($returnArray, "logResponse", $wholeData);
                    return $this->getJsonResponse($returnArray);
                }
            } catch(Exception $e)   {
                $returnArray["message"] = $e->getMessage();
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }