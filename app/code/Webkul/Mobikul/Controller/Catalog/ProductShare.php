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

    namespace Webkul\Mobikul\Controller\Catalog;

    class ProductShare extends AbstractCatalog    {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["responseCode"] = 0;
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
                        $storeId        = $this->_helper->validate($wholeData, "storeId")        ? $wholeData["storeId"]        : 1;
                        $message        = $this->_helper->validate($wholeData, "message")        ? $wholeData["message"]        : "";
                        $productId      = $this->_helper->validate($wholeData, "productId")      ? $wholeData["productId"]      : 0;
                        $customerName   = $this->_helper->validate($wholeData, "customerName")   ? $wholeData["customerName"]   : "";
                        $customerEmail  = $this->_helper->validate($wholeData, "customerEmail")  ? $wholeData["customerEmail"]  : "";
                        $recipientName  = $this->_helper->validate($wholeData, "recipientName")  ? $wholeData["recipientName"]  : "[]";
                        $recipientEmail = $this->_helper->validate($wholeData, "recipientEmail") ? $wholeData["recipientEmail"] : "[]";
                        $recipientName  = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($recipientName);
                        $recipientEmail = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($recipientEmail);
                        $environment    = $this->_emulate->startEnvironmentEmulation($storeId);
                        $senderData = [
                            "name"    => $customerName,
                            "email"   => $customerEmail,
                            "message" => $message
                        ];
                        $recipientData          = [];
                        $recipientData["name"]  = $recipientName;
                        $recipientData["email"] = $recipientEmail;
                        $product    = $this->_objectManager->create("\Magento\Catalog\Model\Product")->load($productId);
                        $sendFriend = $this->_objectManager->create("\Magento\SendFriend\Model\SendFriend");
                        $sendFriend->setSender($senderData);
                        $sendFriend->setRecipients($recipientData);
                        $sendFriend->setProduct($product);
                        $validate = $sendFriend->validate();
                        if ($validate === true) {
                            $sendFriend->send();
                            $returnArray["message"] = __("The link to a friend was sent.");
                            $returnArray["success"] = true;
                            $this->_emulate->stopEnvironmentEmulation($environment);
                            return $this->getJsonResponse($returnArray);
                        } else {
                            if (is_array($validate))
                                $returnArray["message"] = implode(", ", $validate);
                            else
                                 $returnArray["message"] = __("We found some problems with the data.");
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
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $returnArray["message"] = $e->getMessage();
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            } catch (\Exception $e) {
                $returnArray["message"] = __("Some emails were not sent.");
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }