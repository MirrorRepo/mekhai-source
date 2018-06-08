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

    class SaveReview extends AbstractMarketplace    {

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
                        $storeId       = $this->_helper->validate($wholeData, "storeId")       ? $wholeData["storeId"]       : 0;
                        $summary       = $this->_helper->validate($wholeData, "summary")       ? $wholeData["summary"]       : "";
                        $shopUrl       = $this->_helper->validate($wholeData, "shopUrl")       ? $wholeData["shopUrl"]       : "";
                        $nickName      = $this->_helper->validate($wholeData, "nickName")      ? $wholeData["nickName"]      : "";
                        $sellerId      = $this->_helper->validate($wholeData, "sellerId")      ? $wholeData["sellerId"]      : 0;
                        $customerId    = $this->_helper->validate($wholeData, "customerId")    ? $wholeData["customerId"]    : 0;
                        $priceRating   = $this->_helper->validate($wholeData, "priceRating")   ? $wholeData["priceRating"]   : 20;
                        $valueRating   = $this->_helper->validate($wholeData, "valueRating")   ? $wholeData["valueRating"]   : 20;
                        $description   = $this->_helper->validate($wholeData, "description")   ? $wholeData["description"]   : "";
                        $customerEmail = $this->_helper->validate($wholeData, "customerEmail") ? $wholeData["customerEmail"] : "";
                        $qualityRating = $this->_helper->validate($wholeData, "qualityRating") ? $wholeData["qualityRating"] : 20;
                        $environment   = $this->_emulate->startEnvironmentEmulation($storeId);
                        $data                       = [];
                        $data["buyer_id"]           = $customerId;
                        $data["shop_url"]           = $shopUrl;
                        $data["seller_id"]          = $sellerId;
                        $data["created_at"]         = $this->_date->gmtDate();
                        $data["feed_price"]         = $priceRating;
                        $data["feed_value"]         = $valueRating;
                        $data["buyer_email"]        = $customerEmail;
                        $data["feed_review"]        = $description;
                        $data["feed_quality"]       = $qualityRating;
                        $data["feed_summary"]       = $summary;
                        $data["feed_nickname"]      = $nickName;
                        $data["admin_notification"] = 1;
                        $feedbackcount  = 0;
                        $collectionfeed = $this->_feedBackModel->getCollection()
                            ->addFieldToFilter("seller_id", $sellerId)
                            ->addFieldToFilter("buyer_id", $customerId);
                        foreach ($collectionfeed as $value) {
                            $feedbackcount = $value->getFeedbackCount();
                            $value->setFeedbackCount($feedbackcount + 1);
                            $value->save();
                        }
                        $this->_reviewModel->setData($data)->save();
                        $returnArray["message"] = __("Your Review was successfully saved");
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