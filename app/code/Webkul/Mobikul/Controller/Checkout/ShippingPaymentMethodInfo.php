<?php
    /**
    * Webkul Software.
    *
    * @category Webkul
    *
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Controller\Checkout;

    class ShippingPaymentMethodInfo extends AbstractCheckout    {

        public function execute()   {
            $returnArray                    = [];
            $returnArray["authKey"]         = "";
            $returnArray["message"]         = "";
            $returnArray["success"]         = false;
            $returnArray["cartCount"]       = 0;
            $returnArray["responseCode"]    = 0;
            $returnArray["paymentMethods"]  = [];
            $returnArray["shippingMethods"] = [];
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
                        $taxvat         = $this->_helper->validate($wholeData, "taxvat")         ? $wholeData["taxvat"]         : "";
                        $quoteId        = $this->_helper->validate($wholeData, "quoteId")        ? $wholeData["quoteId"]        : 0;
                        $storeId        = $this->_helper->validate($wholeData, "storeId")        ? $wholeData["storeId"]        : 1;
                        $customerId     = $this->_helper->validate($wholeData, "customerId")     ? $wholeData["customerId"]     : 0;
                        $billingData    = $this->_helper->validate($wholeData, "billingData")    ? $wholeData["billingData"]    : "{}";
                        $shippingData   = $this->_helper->validate($wholeData, "shippingData")   ? $wholeData["shippingData"]   : "{}";
                        $checkoutMethod = $this->_helper->validate($wholeData, "checkoutMethod") ? $wholeData["checkoutMethod"] : "";
                        $billingData    = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($billingData);
                        $shippingData   = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($shippingData);
                        $environment    = $this->_emulate->startEnvironmentEmulation($storeId);
                        $store          = $this->_objectManager->create("\Magento\Store\Model\Store");
                        $baseCurrency   = $store->getBaseCurrencyCode();
                        $currency       = $this->_helper->validate($wholeData, "currency") ? $wholeData["currency"] : $baseCurrency;
                        $store->setCurrentCurrencyCode($currency);
                        $quote          = new \Magento\Framework\DataObject();
                        if ($customerId != 0) {
                            $quoteCollection = $this->_quoteFactory->create()
                                ->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                // ->addFieldToFilter("store_id", $storeId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if ($quoteId != 0)
                            $quote = $this->_quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                        if($quote->getItemsQty()*1 == 0){
                            $returnArray["message"] = __("Sorry Something went wrong !!");
                            return $this->getJsonResponse($returnArray);
                        }
                        else{
                            $returnArray["cartCount"] = $quote->getItemsQty()*1;
                        }
                        $useForShipping = 0;
                        if(!empty($billingData))    {
                            $saveInAddressBook = 0;
                            if(isset($billingData["newAddress"]["saveInAddressBook"]))
                                $saveInAddressBook = $billingData["newAddress"]["saveInAddressBook"];
                            if($checkoutMethod == "register")
                                $saveInAddressBook = 1;
                            if($billingData["useForShipping"] != "")
                                $useForShipping = $billingData["useForShipping"];
                            $addressId = 0;
                            if($billingData["addressId"] != "")
                                $addressId = $billingData["addressId"];
                            $quote->setCheckoutMethod($checkoutMethod)->save();
                            $newAddress = [];
                            if($billingData["newAddress"] != "")
                                if(!empty($billingData["newAddress"]))
                                    $newAddress = $billingData["newAddress"];
                            $address     = $quote->getBillingAddress();
                            $addressForm = $this->_objectManager->create("\Magento\Customer\Model\Form");
                            $addressForm->setFormCode("customer_address_edit")->setEntityType("customer_address");
                            if($addressId > 0) {
                                $customerAddress = $this->_objectManager->create("\Magento\Customer\Model\Address")->load($addressId)->getDataModel();
                                if($customerAddress->getId()) {
                                    if($customerAddress->getCustomerId() != $quote->getCustomerId()){
                                        $returnArray["message"] = __("Customer Address is not valid.");
                                        return $this->getJsonResponse($returnArray);
                                    }
                                    $address->importCustomerAddressData($customerAddress)->setSaveInAddressBook(0);
                                    $addressForm->setEntity($address);
                                    $addressErrors = $addressForm->validateData($address->getData());
                                    if($addressErrors !== true){
                                        $returnArray["message"] = implode(", ", $addressErrors);
                                        return $this->getJsonResponse($returnArray);
                                    }
                                }
                            }
                            else {
                                $addressForm->setEntity($address);
                                $addressData = [
                                    "firstname"  => $newAddress["firstName"],
                                    "lastname"   => $newAddress["lastName"],
                                    "middlename" => $this->_helper->validate($newAddress, "middleName") ? $newAddress["middleName"] : "",
                                    "prefix"     => $this->_helper->validate($newAddress, "prefix")     ? $newAddress["prefix"]     : "",
                                    "suffix"     => $this->_helper->validate($newAddress, "suffix")     ? $newAddress["suffix"]     : "",
                                    "company"    => $newAddress["company"],
                                    "street"     => $newAddress["street"],
                                    "city"       => $newAddress["city"],
                                    "country_id" => $newAddress["country_id"],
                                    "region"     => $newAddress["region"],
                                    "region_id"  => $newAddress["region_id"],
                                    "postcode"   => $newAddress["postcode"],
                                    "telephone"  => $newAddress["telephone"],
                                    "fax"        => $newAddress["fax"],
                                    "taxvat"     => $this->_helper->validate($newAddress, "taxvat")     ? $newAddress["taxvat"]     : "",
                                    "dob"        => $this->_helper->validate($newAddress, "dob")        ? $newAddress["dob"]        : "",
                                    "gender"     => $this->_helper->validate($newAddress, "gender")     ? $newAddress["gender"]     : ""
                                ];
                                $addressErrors  = $addressForm->validateData($addressData);
                                if($addressErrors !== true){
                                    $returnArray["message"] = implode(", ", $addressErrors);
                                    return $this->getJsonResponse($returnArray);
                                }
                                $addressForm->compactData($addressData);
                                $address->setCustomerAddressId(null);
                                $address->setSaveInAddressBook($saveInAddressBook);
                                $quote->setCustomerFirstname($newAddress["firstName"])->setCustomerLastname($newAddress["lastName"]);
                            }
                            if(in_array($checkoutMethod, ["register", "guest"])){
                                $websiteId = $this->_storeManager->getStore()->getWebsiteId();
                                if (!empty($newAddress["email"]) && !\Zend_Validate::is($newAddress["email"], "EmailAddress")) {
                                    $returnArray["message"] = __("Invalid email format");
                                    return $this->getJsonResponse($returnArray);
                                }
                                if($this->_customerFactory->create()->setWebsiteId($websiteId)->loadByEmail(trim($newAddress["email"]))->getId() > 0){
                                    $returnArray["message"] = __("Email already exist");
                                    return $this->getJsonResponse($returnArray);
                                }
                                $quote->setCustomerEmail(trim($newAddress["email"]));
                                if(in_array($this->_helper->getConfigData("customer/address/taxvat_show"), ["req", "opt"]))
                                    $quote->setCustomerTaxvat($newAddress["taxvat"]);
                                $address->setEmail(trim($newAddress["email"]));
                            }
                            if(!$address->getEmail() && $quote->getCustomerEmail()){
                                $address->setEmail($quote->getCustomerEmail());
                            }
                            if(($validateRes = $address->validate()) !== true){
                                $returnArray["message"] = implode(",", $validateRes);
                                return $this->getJsonResponse($returnArray);
                            }
                            if(true !== ($result = $this->_validateCustomerData($wholeData))) {
                                $returnArray["message"] = implode(",", $result);
                                return $this->getJsonResponse($returnArray);
                            }
                            if(!$quote->getCustomerId() && "register" == $quote->getCheckoutMethod()) {
                                if($this->_customerEmailExists($address->getEmail(), $this->_storeManager->getStore()->getWebsiteId())){
                                    $returnArray["message"] = __("This email already exist.");
                                    return $this->getJsonResponse($returnArray);
                                }
                            }
                            if(!$quote->isVirtual()) {
                                $usingCase = isset($useForShipping) ? (int)$useForShipping : 0;
                                switch($usingCase) {
                                    case 0:
                                        $shipping = $quote->getShippingAddress();
                                        $shipping->setSameAsBilling(0);
                                        $setStepDataShipping = 0;
                                        break;
                                    case 1:
                                        $billing = clone $address;
                                        $billing->unsAddressId()->unsAddressType();
                                        $shipping = $quote->getShippingAddress();
                                        $shippingMethod = $shipping->getShippingMethod();
                                        $shipping->addData($billing->getData())
                                            ->setSameAsBilling(1)
                                            ->setSaveInAddressBook(0)
                                            ->setShippingMethod($shippingMethod)
                                            ->setCollectShippingRates(true);
                                        $setStepDataShipping = 1;
                                        break;
                                }
                            }
                            $quote->collectTotals()->save();
                            if(!$quote->isVirtual() && $setStepDataShipping)
                                $quote->getShippingAddress()->setCollectShippingRates(true);
                        }
                        else{
                            $returnArray["message"] = __("Invalid Billing data.");
                            return $this->getJsonResponse($returnArray);
                        }
// step 4 process starts here ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if(!$quote->isVirtual()){
                            if($useForShipping == 0){
                                if($shippingData != ""){
                                    $sameAsBilling = 0;
                                    if($shippingData["sameAsBilling"] != "")
                                        $sameAsBilling = $shippingData["sameAsBilling"];
                                    $newAddress = [];
                                    if($shippingData["newAddress"] != "")
                                        if(!empty($shippingData["newAddress"]))
                                            $newAddress = $shippingData["newAddress"];
                                    $addressId = 0;
                                    if($shippingData["addressId"] != "")
                                        $addressId = $shippingData["addressId"];
                                    $saveInAddressBook = 0;
                                    if(isset($shippingData["newAddress"]["saveInAddressBook"]) && $shippingData["newAddress"]["saveInAddressBook"] != "")
                                        $saveInAddressBook = $shippingData["newAddress"]["saveInAddressBook"];
                                    $address = $quote->getShippingAddress();
                                    $addressForm = $this->_objectManager->create("\Magento\Customer\Model\Form");
                                    $addressForm->setFormCode("customer_address_edit")->setEntityType("customer_address");
                                    if($addressId > 0) {
                                        $customerAddress = $this->_objectManager->create("\Magento\Customer\Model\Address")->load($addressId)->getDataModel();
                                        if($customerAddress->getId()) {
                                            if($customerAddress->getCustomerId() != $quote->getCustomerId()){
                                                $returnArray["message"] = __("Customer Address is not valid.");
                                                return $this->getJsonResponse($returnArray);
                                            }
                                            $address->importCustomerAddressData($customerAddress)->setSaveInAddressBook(0);
                                            $addressForm->setEntity($address);
                                            $addressErrors  = $addressForm->validateData($address->getData());
                                            if($addressErrors !== true){
                                                $returnArray["message"] = implode(", ", $addressErrors);
                                                return $this->getJsonResponse($returnArray);
                                            }
                                        }
                                    }
                                    else {
                                        $addressForm->setEntity($address);
                                        $addressData = [
                                            "firstname"  => $newAddress["firstName"],
                                            "lastname"   => $newAddress["lastName"],
                                            "middlename" => $this->_helper->validate($newAddress, "middleName") ? $newAddress["middleName"] : "",
                                            "prefix"     => $this->_helper->validate($newAddress, "prefix")     ? $newAddress["prefix"]     : "",
                                            "suffix"     => $this->_helper->validate($newAddress, "suffix")     ? $newAddress["suffix"]     : "",
                                            "company"    => $newAddress["company"],
                                            "street"     => $newAddress["street"],
                                            "city"       => $newAddress["city"],
                                            "country_id" => $newAddress["country_id"],
                                            "region"     => $newAddress["region"],
                                            "region_id"  => $newAddress["region_id"],
                                            "postcode"   => $newAddress["postcode"],
                                            "telephone"  => $newAddress["telephone"],
                                            "fax"        => $newAddress["fax"],
                                            "taxvat"     => $this->_helper->validate($newAddress, "taxvat")     ? $newAddress["taxvat"]     : "",
                                            "dob"        => $this->_helper->validate($newAddress, "dob")        ? $newAddress["dob"]        : "",
                                            "gender"     => $this->_helper->validate($newAddress, "gender")     ? $newAddress["gender"]     : ""
                                        ];
                                        $addressErrors = $addressForm->validateData($addressData);
                                        if($addressErrors !== true){
                                            $returnArray["message"] = implode(", ", $addressErrors);
                                            return $this->getJsonResponse($returnArray);
                                        }
                                        $addressForm->compactData($addressData);
                                        $address->setCustomerAddressId(null);
// Additional form data, not fetched by extractData (as it fetches only attributes) /////////////////////////////////////////////
                                        $address->setSaveInAddressBook($saveInAddressBook);
                                        $address->setSameAsBilling($sameAsBilling);
                                    }
                                    // $address->implodeStreetAddress();
                                    $address->setCollectShippingRates(true);
                                    if(($validateRes = $address->validate()) !== true){
                                        $returnArray["message"] = implode(", ", $validateRes);
                                        return $this->getJsonResponse($returnArray);
                                    }
                                    $quote->collectTotals()->save();
                                }
                                else{
                                    $returnArray["message"] = __("Invalid Shipping data.");
                                    return $this->getJsonResponse($returnArray);
                                }
                            }
                            $quote->getShippingAddress()->collectShippingRates()->save();
                            $shippingRateGroups = $quote->getShippingAddress()->getGroupedAllShippingRates();
                            foreach($shippingRateGroups as $code => $rates) {
                                $oneShipping = [];
                                $oneShipping["title"] = $this->_helperCatalog->stripTags($this->_helper->getConfigData("carriers/".$code."/title"));
                                foreach($rates as $rate){
                                    $oneMethod = [];
                                    if($rate->getErrorMessage())
                                        $oneMethod["error"] = $rate->getErrorMessage();
                                    $oneMethod["code"]  = $rate->getCode();
                                    $oneMethod["label"] = $rate->getMethodTitle();
                                    $oneMethod["price"] = $this->_helperCatalog->stripTags($this->_priceHelper->currency((float) $rate->getPrice()));
                                    $oneShipping["method"][] = $oneMethod;
                                }
                                $returnArray["shippingMethods"][] = $oneShipping;
                            }
                        }
                        foreach($this->_objectManager->get("\Magento\Payment\Helper\Data")->getStoreMethods($storeId, $quote) as $method) {
                            $oneMethod          = [];
                            $oneMethod["code"]  = $method->getCode();
                            $oneMethod["title"] = $method->getTitle();
                            $oneMethod["extraInformation"] = "";
                            if(in_array($method->getCode(), ["paypal_standard", "paypal_express"])){
                                $oneMethod["extraInformation"] = __("You will be redirected to the PayPal website.");
                                $config = $this->_objectManager->create("\Magento\Paypal\Model\Config")->setMethod($method->getCode());
                                $locale = $this->_objectManager->create("\Magento\Framework\Locale\ResolverInterface");
                                $oneMethod["title"]    = "";
                                $oneMethod["link"]     = $config->getPaymentMarkWhatIsPaypalUrl($locale);
                                $oneMethod["imageUrl"] = $config->getPaymentMarkImageUrl($locale->getDefaultLocale());
                            }
                            else
                            if(in_array($method->getCode(), ["paypal_express_bml"])){
                                $oneMethod["extraInformation"] = __("You will be redirected to the PayPal website.");
                                $oneMethod["title"]    = "";
                                $oneMethod["link"]     = "https://www.securecheckout.billmelater.com/paycapture-content/fetch?hash=AU826TU8&content=/bmlweb/ppwpsiw.html";
                                $oneMethod["imageUrl"] = "https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppc-acceptance-medium.png";
                            }
                            else
                            if($method->getCode() == "checkmo"){
                                if($method->getPayableTo())
                                    $extraInformationPrefix = __("Make Check payable to:");
                                else
                                    $extraInformationPrefix = __("Send Check to:");
                                $extraInformation = $this->_helper->getConfigData("payment/".$method->getCode()."/mailing_address");
                                if($extraInformation == "")
                                    $extraInformation = __(" xxxxxxx");
                                $oneMethod["extraInformation"] = $extraInformationPrefix.$extraInformation;
                            }
                            else
                            if($method->getCode() == "banktransfer"){
                                $extraInformation = $this->_helper->getConfigData("payment/".$method->getCode()."/instructions");
                                if($extraInformation == "")
                                    $extraInformation = __("Bank Details are xxxxxxx");
                                $oneMethod["extraInformation"] = $extraInformation;
                            }
                            else
                            if($method->getCode() == "cashondelivery"){
                                $extraInformation = $this->_helper->getConfigData("payment/".$method->getCode()."/instructions");
                                if($extraInformation == "")
                                    $extraInformation = __("Pay at the time of delivery");
                                $oneMethod["extraInformation"] = $extraInformation;
                            }
                            else
                            if (in_array($method->getCode(), ["webkul_stripe", "authorizenet"])) {
                                $allowedCc            = [];
                                $allowedCcTypesString = $method->getConfigData("cctypes");
                                $allowedCcTypes       = explode(",", $allowedCcTypesString);
                                $ccTypes              = $this->_objectManager->create("\Magento\Payment\Model\Source\Cctype")->toOptionArray();
                                $types                = [];
                                foreach ($ccTypes as $data) {
                                    if (isset($data["value"]) && isset($data["label"]))
                                        $types[$data["value"]] = $data["label"];
                                }
                                foreach ($allowedCcTypes as $value) {
                                    $eachAllowedCc         = [];
                                    $eachAllowedCc["code"] = $value;
                                    $eachAllowedCc["name"] = $types[$value];
                                    $allowedCc[]           = $eachAllowedCc;
                                }
                                $extraInformation          = $allowedCc;
                                $oneMethod["extraInformation"] = $extraInformation;
                            }
                            $returnArray["paymentMethods"][] = $oneMethod;
                        }
                        $returnArray["success"] = true;
                        $this->_emulate->stopEnvironmentEmulation($environment);
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    } else {
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $returnArray["message"]      = $authData["message"];
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    }
                } else {
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = __("Invalid Request");
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