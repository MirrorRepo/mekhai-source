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

    class SavePdfHeader extends AbstractMarketplace    {

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
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 0;
                        $pdfHeader   = $this->_helper->validate($wholeData, "pdfHeader")  ? $wholeData["pdfHeader"]  : "";
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $sellerId    = 0;
                        $sellerCollection = $this->_seller->getCollection()
                            ->addFieldToFilter("seller_id", $customerId)
                            ->addFieldToFilter("store_id", $storeId);
                        foreach ($sellerCollection as $eachSeller) {
                            $sellerId = $eachSeller->getId();
                        }
                        $sellerData = [];
                        if (!$sellerId) {
                            $sellerDefaultData = [];
                            $sellerCollection  = $this->_seller->getCollection()
                                ->addFieldToFilter("seller_id", $customerId)
                                ->addFieldToFilter("store_id", 0);
                            foreach ($sellerCollection as $eachSeller) {
                                $sellerDefaultData = $eachSeller->getData();
                                $eachSeller->setOthersInfo($pdfHeader);
                                $eachSeller->save();
                            }
                            foreach ($sellerDefaultData as $key => $value) {
                                if ($key != "entity_id") {
                                    $sellerData[$key] = $value;
                                }
                            }
                        }
                        $seller = $this->_seller->load($sellerId);
                        if (!empty($sellerData)) {
                            $seller->addData($sellerData);
                        }
                        $seller->setOthersInfo($pdfHeader);
                        $seller->setStoreId($storeId);
                        $seller->save();
                        $returnArray["success"] = true;
                        $returnArray["message"] = __("Information was successfully saved");
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