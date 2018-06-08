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

    class SellerReviews extends AbstractMarketplace    {

        public function execute()   {
            $returnArray               = [];
            $returnArray["authKey"]    = "";
            $returnArray["message"]    = "";
            $returnArray["success"]    = false;
            $returnArray["reviewList"] = [];
            $returnArray["totalCount"] = 0;
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
                        $sellerId    = $this->_helper->validate($wholeData, "sellerId")   ? $wholeData["sellerId"]   : 0;
                        $pageNumber  = $this->_helper->validate($wholeData, "pageNumber") ? $wholeData["pageNumber"] : 1;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $reviewCollection = $this->_reviewModel->getCollection()
                            ->addFieldToFilter("status", ["neq"=>0])
                            ->addFieldToFilter("seller_id", $sellerId)
                            ->setOrder("entity_id", "DESC");
                        $reviewList = [];
                        if($pageNumber >= 1) {
                            $returnArray["totalCount"] = $reviewCollection->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $reviewCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        foreach ($reviewCollection as  $each) {
                            $eachReview                = [];
                            $eachReview["date"]        = date("M d, Y", strtotime($each["created_at"]));
                            $eachReview["summary"]     = $each["feed_summary"];
                            $eachReview["userName"]    = $this->_customer->load($each["buyer_id"])->getName();
                            $eachReview["feedPrice"]   = $each["feed_price"];
                            $eachReview["feedValue"]   = $each["feed_value"];
                            $eachReview["feedQuality"] = $each["feed_quality"];
                            $eachReview["description"] = $each["feed_review"];
                            $reviewList[]              = $eachReview;
                        }
                        $returnArray["reviewList"] = $reviewList;
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