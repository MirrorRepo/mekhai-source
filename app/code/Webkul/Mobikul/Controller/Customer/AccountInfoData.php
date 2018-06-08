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

    class AccountInfoData extends AbstractCustomer   {

        public function execute()   {
            $returnArray                        = [];
            $returnArray["mobile"]              = "";
            $returnArray["authKey"]             = "";
            $returnArray["success"]             = false;
            $returnArray["message"]             = "";
            $returnArray["DOBValue"]            = "";
            $returnArray["taxValue"]            = "";
            $returnArray["middleName"]          = "";
            $returnArray["suffixValue"]         = "";
            $returnArray["prefixValue"]         = "";
            $returnArray["genderValue"]         = 1;
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
            $returnArray["isSuffixRequired"]    = false;
            $returnArray["suffixHasOptions"]    = false;
            $returnArray["isGenderRequired"]    = false;
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
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $customer    = $this->_customerFactory->create()->load($customerId);
                        $returnArray["firstName"] = $customer->getFirstname();
                        $returnArray["lastName"]  = $customer->getLastname();
                        $returnArray["email"]     = $customer->getEmail();
                        $showPrefix  = $this->_helper->getConfigData("customer/address/prefix_show");
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
                        $mobileStatus = $this->_helper->getConfigData("mobikul/configuration/enable_mobile_login");
                        if($mobileStatus == 1)  {
                            $returnArray["isMobileVisible"]  = true;
                            $returnArray["isMobileRequired"] = true;
                            // $collection = Mage::getModel("mobikul/customermobile")->getCollection()->addFieldToFilter("customer_id", $customerId);
                            // foreach ($collection as $each) {
                            //     $returnArray["mobile"] = $each->getMobile();
                            // }
                        }
                        $DOBVisible = $this->_helper->getConfigData("customer/address/dob_show");
                        if($DOBVisible == "req"){
                            $returnArray["isDOBVisible"]  = true;
                            $returnArray["isDOBRequired"] = true;
                            $returnArray["DOBValue"]      = is_null($customer->getDob()) ? "" : $customer->getDob();
                        }
                        elseif($DOBVisible == "opt"){
                            $returnArray["isDOBVisible"] = true;
                            $returnArray["DOBValue"]     = is_null($customer->getDob()) ? "" : $customer->getDob();
                        }
                        $TaxVisible = $this->_helper->getConfigData("customer/address/taxvat_show");
                        if($TaxVisible == "req"){
                            $returnArray["isTaxVisible"]  = true;
                            $returnArray["isTaxRequired"] = true;
                            $returnArray["taxValue"]      = is_null($customer->getTaxvat()) ? "" : $customer->getTaxvat();
                        }
                        elseif($TaxVisible == "opt"){
                            $returnArray["isTaxVisible"] = true;
                            $returnArray["taxValue"]     = is_null($customer->getTaxvat()) ? "" : $customer->getTaxvat();
                        }
                        $GenderVisible = $this->_helper->getConfigData("customer/address/gender_show");
                        if($GenderVisible == "req"){
                            $returnArray["isGenderVisible"]  = true;
                            $returnArray["isGenderRequired"] = true;
                            $returnArray["genderValue"]      = is_null($customer->getGender()) ? 0 : $customer->getGender();
                        }
                        elseif($GenderVisible == "opt"){
                            $returnArray["isGenderVisible"] = true;
                            $returnArray["genderValue"]     = is_null($customer->getGender()) ? 0 : $customer->getGender();
                        }
                        $returnArray["dateFormat"] = \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT;
                        $returnArray["success"]    = true;
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