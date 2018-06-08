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

    class ContactSeller extends AbstractMarketplace    {

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
                        $name               = $this->_helper->validate($wholeData, "name")       ? $wholeData["name"]       : "";
                        $email              = $this->_helper->validate($wholeData, "email")      ? $wholeData["email"]      : "";
                        $query              = $this->_helper->validate($wholeData, "query")      ? $wholeData["query"]      : "";
                        $subject            = $this->_helper->validate($wholeData, "subject")    ? $wholeData["subject"]    : "";
                        $storeId            = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 0;
                        $sellerId           = $this->_helper->validate($wholeData, "sellerId")   ? $wholeData["sellerId"]   : 0;
                        $productId          = $this->_helper->validate($wholeData, "productId")  ? $wholeData["productId"]  : 0;
                        $customerId         = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment        = $this->_emulate->startEnvironmentEmulation($storeId);
                        $data               = [];
                        $data["ask"]        = $query;
                        $data["name"]       = $name;
                        $data["email"]      = $email;
                        $data["subject"]    = $subject;
                        $data["seller-id"]  = $sellerId;
                        $data["product-id"] = $productId;
                        $this->_eventManager->dispatch("mp_send_querymail", [$data]);
                        if ($customerId != 0) {
                            $customer   = $this->_customer->load($customerId);
                            $buyerName  = $customer->getName();
                            $buyerEmail = $customer->getEmail();
                        } else {
                            $buyerEmail = $email;
                            $buyerName  = $name;
                            if (strlen($buyerName) < 2) {
                                $buyerName = "Guest";
                            }
                        }
                        $senderInfo   = [];
                        $templateVars = [];
                        $receiverInfo = [];
                        $seller       = $this->_customer->load($sellerId);
                        $templateVars["myvar1"] = $seller->getName();
                        $sellerEmail = $seller->getEmail();
                        if ($productId != 0) {
                            $templateVars["myvar3"] = $this->_productModel->load($productId)->getName();
                        }
                        $templateVars["myvar4"] = $query;
                        $templateVars["myvar6"] = $subject;
                        $templateVars["myvar5"] = $buyerEmail;
                        $senderInfo = [
                            "name"  => $buyerName,
                            "email" => $buyerEmail
                        ];
                        $receiverInfo = [
                            "name"  => $seller->getName(),
                            "email" => $sellerEmail
                        ];
                        $this->_marketplaceEmailHelper->sendQuerypartnerEmail($data, $templateVars, $senderInfo, $receiverInfo);
                        $returnArray["success"] = true;
                        $returnArray["message"] = __("Your mail has been sent.");
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