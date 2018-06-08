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

    namespace Webkul\Mobikul\Controller\Customer;

    class AddressFormData extends AbstractCustomer  {

        public function execute()   {
            $returnArray                        = [];
            $returnArray["authKey"]             = "";
            $returnArray["message"]             = "";
            $returnArray["middleName"]          = "";
            $returnArray["prefixValue"]         = "";
            $returnArray["suffixValue"]         = "";
            $returnArray["responseCode"]        = 0;
            $returnArray["prefixOptions"]       = [];
            $returnArray["suffixOptions"]       = [];
            $returnArray["streetLineCount"]     = 2;
            $returnArray["isPrefixVisible"]     = false;
            $returnArray["isSuffixVisible"]     = false;
            $returnArray["isPrefixRequired"]    = false;
            $returnArray["prefixHasOptions"]    = false;
            $returnArray["isSuffixRequired"]    = false;
            $returnArray["suffixHasOptions"]    = false;
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
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $addressId   = $this->_helper->validate($wholeData, "addressId")  ? $wholeData["addressId"]  : 0;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $customer = $this->_customerFactory->create();
                        if ($customerId != 0) {
                            $customer = $this->_customerFactory->create()->load($customerId);
                            if ($customer->getDefaultBilling() == $addressId)
                                $returnArray["addressData"]["isDefaultBilling"] = true;
                            else
                                $returnArray["addressData"]["isDefaultBilling"] = false;
                            if ($customer->getDefaultShipping() == $addressId)
                                $returnArray["addressData"]["isDefaultShipping"] = true;
                            else
                                $returnArray["addressData"]["isDefaultShipping"] = false;
                        }
                        if ($addressId != 0) {
                            $address = $this->_objectManager->create("\Magento\Customer\Model\Address")->load($addressId);
                            $addressData = $address->getData();
                            foreach ($addressData as $key=>$addata) {
                                if ($addata != "")
                                    $returnArray["addressData"][$key] = $addata;
                                else
                                    $returnArray["addressData"][$key] = "";
                            }
                            $returnArray["addressData"]["street"] = $address->getStreet();
                        }
                        else
                            $returnArray["addressData"] = new \stdClass();
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
                                $eachCountry["states"] = $result;
                            $returnArray["countryData"][] = $eachCountry;
                        }
                        $returnArray["firstName"]       = $customer->getFirstname();
                        $returnArray["lastName"]        = $customer->getLastname();
                        $returnArray["streetLineCount"] = $this->_objectManager->get("\Magento\Customer\Helper\Address")->getStreetLines();
                        $showPrefix = $this->_helper->getConfigData("customer/address/prefix_show");
                        if($showPrefix == "req"){
                            $returnArray["isPrefixVisible"]  = true;
                            $returnArray["isPrefixRequired"] = true;
                            $returnArray["prefixValue"]      = is_null($customer->getPrefix()) ? "" : $customer->getPrefix();
                        }
                        elseif($showPrefix == "opt"){
                            $returnArray["isPrefixVisible"] = true;
                            $returnArray["prefixValue"]     = is_null($customer->getPrefix()) ? "" : $customer->getPrefix();
                        }
                        $prefixOptions = $this->_helper->getConfigData("customer/address/prefix_options");
                        if($prefixOptions != ""){
                            $returnArray["prefixHasOptions"] = true;
                            $returnArray["prefixOptions"]    = explode(";", $prefixOptions);
                        }
                        $showMiddleName = $this->_helper->getConfigData("customer/address/middlename_show");
                        if($showMiddleName == 1){
                            $returnArray["middleName"]          = is_null($customer->getMiddlename()) ? "" : $customer->getMiddlename();
                            $returnArray["isMiddlenameVisible"] = true;
                        }
                        $showSuffix = $this->_helper->getConfigData("customer/address/suffix_show");
                        if($showSuffix == "req"){
                            $returnArray["isSuffixVisible"]  = true;
                            $returnArray["isSuffixRequired"] = true;
                            $returnArray["suffixValue"]      = is_null($customer->getSuffix()) ? "" : $customer->getSuffix();
                        }
                        elseif($showSuffix == "opt"){
                            $returnArray["isSuffixVisible"] = true;
                            $returnArray["suffixValue"]     = is_null($customer->getSuffix()) ? "" : $customer->getSuffix();
                        }
                        $suffixOptions = $this->_helper->getConfigData("customer/address/suffix_options");
                        if($suffixOptions != ""){
                            $returnArray["suffixHasOptions"] = true;
                            $returnArray["suffixOptions"]    = explode(";", $suffixOptions);
                        }
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
            } catch (\Exception $e) {
                $returnArray["message"] = $e->getMessage();
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }