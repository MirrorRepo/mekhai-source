<?php
    /**
     * Webkul Software.
     *
     * @category  Webkul
     * @package   Webkul_MobikulMp
     * @author    Webkul
     * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
     * @license   https://store.webkul.com/license.html
     */

     namespace Webkul\MobikulMp\Controller\Marketplace;

    class AskQuestionToAdmin extends AbstractMarketplace    {

        public function execute()   {
            $returnArray            = [];
            $returnArray["authKey"] = "";
            $returnArray["message"] = "";
            $returnArray["success"] = false;
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
                        $query         = $this->_helper->validate($wholeData, "query")      ? $wholeData["query"]      : "";
                        $subject       = $this->_helper->validate($wholeData, "subject")    ? $wholeData["subject"]    : "";
                        $storeId       = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 0;
                        $customerId    = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment   = $this->_emulate->startEnvironmentEmulation($storeId);
                        $customer      = $this->_customer->load($customerId);
                        $adminEmail    = $this->_marketplaceHelper->getAdminEmailId();
                        $adminEmail    = $adminEmail ? $adminEmail : $this->_marketplaceHelper->getDefaultTransEmailId();
                        $sellerName    = $customer->getName();
                        $senderInfo    = [];
                        $sellerEmail   = $customer->getEmail();
                        $templateVars  = [];
                        $receiverInfo  = [];
                        $adminUsername = "Admin";
                        $templateVars["myvar1"]  = $adminUsername;
                        $templateVars["myvar2"]  = $sellerName;
                        $templateVars["myvar3"]  = $query;
                        $templateVars["subject"] = $subject;
                        $senderInfo = [
                            "name"  => $sellerName,
                            "email" => $sellerEmail
                        ];
                        $receiverInfo = [
                            "name"  => $adminUsername,
                            "email" => $adminEmail
                        ];
                        $this->_marketplaceEmailHelper->askQueryAdminEmail($templateVars, $senderInfo, $receiverInfo);
                        $returnArray["success"] = true;
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