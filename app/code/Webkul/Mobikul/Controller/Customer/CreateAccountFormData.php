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

    namespace Webkul\Mobikul\Controller\Customer;

    class CreateAccountFormData extends AbstractCustomer    {

        public function execute()   {
            $returnArray                        = [];
            $returnArray["authKey"]             = "";
            $returnArray["message"]             = "";
            $returnArray["responseCode"]        = 0;
            $returnArray["isDOBVisible"]        = false;
            $returnArray["isTaxVisible"]        = false;
            $returnArray["prefixOptions"]       = [];
            $returnArray["suffixOptions"]       = [];
            $returnArray["isDOBRequired"]       = false;
            $returnArray["isTaxRequired"]       = false;
            $returnArray["isPrefixVisible"]     = false;
            $returnArray["isSuffixVisible"]     = false;
            $returnArray["isGenderVisible"]     = false;
            $returnArray["isMobileVisible"]     = false;
            $returnArray["isPrefixRequired"]    = false;
            $returnArray["prefixHasOptions"]    = false;
            $returnArray["isGenderRequired"]    = false;
            $returnArray["isSuffixRequired"]    = false;
            $returnArray["suffixHasOptions"]    = false;
            $returnArray["isMobileRequired"]    = false;
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
                        $storeId     = $this->_helper->validate($wholeData, "storeId") ? $wholeData["storeId"] : 1;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $showPrefix  = $this->_helper->getConfigData("customer/address/prefix_show");
                        if($showPrefix == "req"){
                            $returnArray["isPrefixVisible"]  = true;
                            $returnArray["isPrefixRequired"] = true;
                        }
                        elseif($showPrefix == "opt"){
                            $returnArray["isPrefixVisible"] = true;
                        }
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
                        elseif($showSuffix == "opt"){
                            $returnArray["isSuffixVisible"] = true;
                        }
                        $suffixOptions = $this->_helper->getConfigData("customer/address/suffix_options");
                        if($suffixOptions != ""){
                            $returnArray["suffixHasOptions"] = true;
                            $returnArray["suffixOptions"]    = explode(";", $suffixOptions);
                        }
                        $mobileStatus = $this->_helper->getConfigData("mobikul/configuration/enable_mobile_login");
                        if($mobileStatus == 1)  {
                            $returnArray["isMobileVisible"]  = true;
                            $returnArray["isMobileRequired"] = true;
                        }
                        $dobVisible = $this->_helper->getConfigData("customer/address/dob_show");
                        if($dobVisible == "req"){
                            $returnArray["isDOBVisible"]  = true;
                            $returnArray["isDOBRequired"] = true;
                        }
                        elseif($dobVisible == "opt"){
                            $returnArray["isDOBVisible"] = true;
                        }
                        $taxVisible = $this->_helper->getConfigData("customer/address/taxvat_show");
                        if($taxVisible == "req"){
                            $returnArray["isTaxVisible"]  = true;
                            $returnArray["isTaxRequired"] = true;
                        }
                        elseif($taxVisible == "opt"){
                            $returnArray["isTaxVisible"] = true;
                        }
                        $genderVisible = $this->_helper->getConfigData("customer/address/gender_show");
                        if($genderVisible == "req"){
                            $returnArray["isGenderVisible"]  = true;
                            $returnArray["isGenderRequired"] = true;
                        }
                        elseif($genderVisible == "opt"){
                            $returnArray["isGenderVisible"] = true;
                        }
                        $returnArray["dateFormat"] = \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT;
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
                $returnArray["message"] = __($e->getMessage());
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }