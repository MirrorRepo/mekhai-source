<?php
    /*
    *
    * Webkul Software.
    *
    * @category  Webkul
    * @package   Webkul_Mobikul
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Controller\Customer;

    class AddressBookData extends AbstractCustomer  {

        public function execute()   {
            $returnArray                             = [];
            $returnArray["message"]                  = "";
            $returnArray["authKey"]                  = "";
            $returnArray["responseCode"]             = 0;
            $returnArray["additionalAddress"]        = [];
            $returnArray["billingAddress"]["id"]     = 0;
            $returnArray["billingAddress"]["value"]  = __("You have no default billing address in your address book.");
            $returnArray["shippingAddress"]["id"]    = 0;
            $returnArray["shippingAddress"]["value"] = __("You have no default shipping address in your address book.");
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
                        $address     = $customer->getPrimaryBillingAddress();
                        if ($address) {
                            $returnArray["billingAddress"]["value"] = $address->format("html");
                            $returnArray["billingAddress"]["id"]    = $address->getId();
                        }
                        $address = $customer->getPrimaryShippingAddress();
                        if ($address) {
                            $returnArray["shippingAddress"]["value"] = $address->format("html");
                            $returnArray["shippingAddress"]["id"]    = $address->getId();
                        }
                        $additionalAddress = $customer->getAdditionalAddresses();
                        foreach ($additionalAddress as $eachAdditionalAddress) {
                            $eachAdditionalAddressArray = [];
                            if ($eachAdditionalAddress) {
                                $eachAdditionalAddressArray["id"]    = $eachAdditionalAddress->getId();
                                $eachAdditionalAddressArray["value"] = $eachAdditionalAddress->format("html");
                            }
                            else{
                                $eachAdditionalAddressArray["id"]    =  0;
                                $eachAdditionalAddressArray["value"] =  __("You have no other address entries in your address book.");
                            }
                            $returnArray["additionalAddress"][] = $eachAdditionalAddressArray;
                        }
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
                $returnArray["message"] = $e->getMessage();
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }