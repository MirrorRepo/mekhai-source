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

    class BillingShippingInfo extends AbstractCheckout   {

        public function execute()   {
            $returnArray                        = [];
            $returnArray["authKey"]             = "";
            $returnArray["message"]             = "";
            $returnArray["success"]             = false;
            $returnArray["address"]             = [];
            $returnArray["lastName"]            = "";
            $returnArray["isVirtual"]           = false;
            $returnArray["cartCount"]           = 0;
            $returnArray["firstName"]           = "";
            $returnArray["middleName"]          = "";
            $returnArray["prefixValue"]         = "";
            $returnArray["suffixValue"]         = "";
            $returnArray["countryData"]         = [];
            $returnArray["responseCode"]        = 0;
            $returnArray["isDOBVisible"]        = false;
            $returnArray["isTaxVisible"]        = false;
            $returnArray["isDOBRequired"]       = false;
            $returnArray["prefixOptions"]       = [];
            $returnArray["suffixOptions"]       = [];
            $returnArray["isTaxRequired"]       = false;
            $returnArray["streetLineCount"]     = 2;
            $returnArray["isPrefixVisible"]     = false;
            $returnArray["isSuffixVisible"]     = false;
            $returnArray["isGenderVisible"]     = false;
            $returnArray["isPrefixRequired"]    = false;
            $returnArray["prefixHasOptions"]    = false;
            $returnArray["isSuffixRequired"]    = false;
            $returnArray["suffixHasOptions"]    = false;
            $returnArray["isGenderRequired"]    = false;
            $returnArray["isMiddlenameVisible"] = false;
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
                        $quoteId     = $this->_helper->validate($wholeData, "quoteId")    ? $wholeData["quoteId"]    : 0;
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $quote       = new \Magento\Framework\DataObject();
                        $addressIds  = [];
                        if($customerId != 0){
                            $customer = $this->_customerFactory->create()->load($customerId);
                            $address  = $customer->getPrimaryBillingAddress();
                            if($address instanceof \Magento\Framework\DataObject){
                                $tempbillingAddress = [];
                                $tempbillingAddress["value"] = preg_replace("/(<br\ ?\/?>)+/", ", ", rtrim(preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace("/[\n\r]/", "<br>", $this->_helperCatalog->stripTags($address->format("html")))), "<br>"));
                                $tempbillingAddress["id"] = $address->getId();
                                if(!in_array($address->getId(), $addressIds)){
                                    $addressIds[] = $address->getId();
                                    $returnArray["address"][] = $tempbillingAddress;
                                }
                            }
                            $address = $customer->getPrimaryShippingAddress();
                            if($address instanceof \Magento\Framework\DataObject){
                                $tempshippingAddress = [];
                                $tempshippingAddress["value"] = preg_replace("/(<br\ ?\/?>)+/", ", ", rtrim(preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace("/[\n\r]/", "<br>", $this->_helperCatalog->stripTags($address->format("html")))), "<br>"));
                                $tempshippingAddress["id"] = $address->getId();
                                if(!in_array($address->getId(), $addressIds)){
                                    $addressIds[] = $address->getId();
                                    $returnArray["address"][] = $tempshippingAddress;
                                }
                            }
                            $additionalAddress = $customer->getAdditionalAddresses();
                            foreach($additionalAddress as $eachAdditionalAddress) {
                                if($eachAdditionalAddress instanceof \Magento\Framework\DataObject){
                                    $eachAdditionalAddressArray = [];
                                    $eachAdditionalAddressArray["value"] = preg_replace("/(<br\ ?\/?>)+/", ", ", rtrim(preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace("/[\n\r]/", "<br>", $this->_helperCatalog->stripTags($eachAdditionalAddress->format("html")))), "<br>"));
                                    $eachAdditionalAddressArray["id"] = $eachAdditionalAddress->getId();
                                    $returnArray["address"][] = $eachAdditionalAddressArray;
                                }
                            }
                            $quoteCollection = $this->_quoteFactory->create()
                                ->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                // ->addFieldToFilter("store_id", $storeId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC");
                            $quote = $quoteCollection->getFirstItem();
                            $returnArray["firstName"]   = $customer->getFirstname();
                            $returnArray["lastName"]    = $customer->getLastname();
                            $returnArray["prefixValue"] = is_null($customer->getPrefix())     ? "" : $customer->getPrefix();
                            $returnArray["middleName"]  = is_null($customer->getMiddlename()) ? "" : $customer->getMiddlename();
                            $returnArray["suffixValue"] = is_null($customer->getSuffix())     ? "" : $customer->getSuffix();
                        }
                        if($quoteId != 0)
                            $quote = $this->_quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                        if($quote->getItemsQty()*1 == 0){
                            $returnArray["message"] = __("Sorry Something went wrong !!");
                            return $this->getJsonResponse($returnArray);
                        }
                        else{
                            $returnArray["cartCount"] = $quote->getItemsQty()*1;
                        }
                        $DOBVisible = $this->_helper->getConfigData("customer/address/dob_show");
                        if($DOBVisible == "req"){
                            $returnArray["isDOBVisible"]  = true;
                            $returnArray["isDOBRequired"] = true;
                        }
                        elseif($DOBVisible == "opt")
                            $returnArray["isDOBVisible"] = true;
                        $TaxVisible = $this->_helper->getConfigData("customer/address/taxvat_show");
                        if($TaxVisible == "req"){
                            $returnArray["isTaxVisible"]  = true;
                            $returnArray["isTaxRequired"] = true;
                        }
                        elseif($TaxVisible == "opt")
                            $returnArray["isTaxVisible"] = true;
                        $GenderVisible = $this->_helper->getConfigData("customer/address/gender_show");
                        if($GenderVisible == "req"){
                            $returnArray["isGenderVisible"]  = true;
                            $returnArray["isGenderRequired"] = true;
                        }
                        elseif($GenderVisible == "opt")
                            $returnArray["isGenderVisible"] = true;
                        $returnArray["dateFormat"] = \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT;
                        $returnArray["isVirtual"]  = $quote->isVirtual();
                        $returnArray["streetLineCount"] = $this->_objectManager->get("\Magento\Customer\Helper\Address")->getStreetLines();
                        $countryCollection = $this->_objectManager
                            ->create("\Magento\Directory\Model\ResourceModel\Country\Collection")
                            ->loadByStore()
                            ->toOptionArray(true);
                        unset($countryCollection[0]);
                        foreach ($countryCollection as $country) {
                            $eachCountry = [];
                            $eachCountry["country_id"] = $country["value"];
                            $eachCountry["name"]       = $country["label"];
                            $country = $this->_objectManager->create("\Magento\Directory\Model\Country")->loadByCode($country["value"]);
                            $result = [];
                            foreach ($country->getRegions() as $region) {
                                $eachRegion = [];
                                $eachRegion["region_id"] = $region->getRegionId();
                                $eachRegion["code"]      = $region->getCode();
                                $eachRegion["name"]      = $region->getName();
                                $result[]                = $eachRegion;
                            }
                            if (count($result) > 0)
                                $eachCountry["states"]    = $result;
                            $returnArray["countryData"][] = $eachCountry;
                        }
                        $showPrefix = $this->_helper->getConfigData("customer/address/prefix_show");
                        if($showPrefix == "req"){
                            $returnArray["isPrefixVisible"]  = true;
                            $returnArray["isPrefixRequired"] = true;
                        }
                        elseif($showPrefix == "opt")
                            $returnArray["isPrefixVisible"] = true;
                        $prefixOptions = $this->_helper->getConfigData("customer/address/prefix_options");
                        if($prefixOptions != ""){
                            $returnArray["prefixHasOptions"] = true;
                            $returnArray["prefixOptions"]    = explode(";", $prefixOptions);
                        }
                        $showMiddleName = $this->_helper->getConfigData("customer/address/middlename_show");
                        if($showMiddleName == 1)
                            $returnArray["isMiddlenameVisible"] = true;
                        $showSuffix = $this->_helper->getConfigData("customer/address/suffix_show");
                        if($showSuffix == "req"){
                            $returnArray["isSuffixVisible"]  = true;
                            $returnArray["isSuffixRequired"] = true;
                        }
                        elseif($showSuffix == "opt")
                            $returnArray["isSuffixVisible"] = true;
                        $suffixOptions = $this->_helper->getConfigData("customer/address/suffix_options");
                        if($suffixOptions != ""){
                            $returnArray["suffixHasOptions"] = true;
                            $returnArray["suffixOptions"]    = explode(";", $suffixOptions);
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