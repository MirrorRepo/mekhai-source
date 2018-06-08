<?php
    /**
    * Webkul Software.
    *
    * @category Webkul_Mobikul
    *
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Controller\Customer;

    class CheckCustomerByEmail extends AbstractCustomer    {

        public function execute()   {
            $returnArray                    = [];
            $returnArray["authKey"]         = "";
            $returnArray["success"]         = false;
            $returnArray["message"]         = "";
            $returnArray["isCustomerExist"] = false;
            try {
                $wholeData           = $this->getRequest()->getPostValue();
                $this->_headers      = $this->getRequest()->getHeaders();
                $this->_helper->log(__CLASS__, "logClass", $wholeData);
                $this->_helper->log($wholeData, "logParams", $wholeData);
                $this->_helper->log($this->_headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $authKey         = $this->getRequest()->getHeader("authKey");
                    $apiKey          = $this->getRequest()->getHeader("apiKey");
                    $apiPassword     = $this->getRequest()->getHeader("apiPassword");
                    $authData        = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $email       = $this->_helper->validate($wholeData, "email")   ? $wholeData["email"]   : "";
                        $storeId     = $this->_helper->validate($wholeData, "storeId") ? $wholeData["storeId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $websiteId   = $this->_storeManager->getStore()->getWebsiteId();
                        $customer    = $this->_customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($email);
                        if($customer->getId() > 0)
                            $returnArray["isCustomerExist"] = true;
                        $returnArray["success"]             = true;
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
            } catch (\Exception $e) {
                $returnArray["message"] = __($e->getMessage());
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }