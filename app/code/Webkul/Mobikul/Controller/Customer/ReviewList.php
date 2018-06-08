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

    class ReviewList extends AbstractCustomer    {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["message"]      = "";
            $returnArray["totalCount"]   = 0;
            $returnArray["reviewList"]   = [];
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
                        $width       = $this->_helper->validate($wholeData, "width")      ? $wholeData["width"]      : 1000;
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $pageNumber  = $this->_helper->validate($wholeData, "pageNumber") ? $wholeData["pageNumber"] : 1;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $reviews     =  $this->_objectManager
                            ->create("\Magento\Review\Model\Review")
                            ->getProductCollection()
                            ->addStoreFilter($storeId)
                            ->addCustomerFilter($customerId)
                            ->setDateOrder();
// Applying pagination //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($pageNumber >= 1) {
                            $returnArray["totalCount"] = $reviews->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $reviews->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
// Creating Review List /////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $reviewList = [];
                        foreach ($reviews as $key=>$review) {
                            $eachReview = [];
                            $eachReview["date"]      = $this->_objectManager->get("\Magento\Review\Block\Customer\ListCustomer")->dateFormat($review->getReviewCreatedAt());
                            $eachReview["id"]        = $key;
                            $product                 = $this->_objectManager->create("\Magento\Catalog\Model\Product")->load($review->getEntityPkValue());
                            $eachReview["thumbNail"] = $this->_helperCatalog->getImageUrl($product, $width/3);
                            $eachReview["productId"] = $product->getId();
                            $eachReview["proName"]   = $this->_helperCatalog->stripTags($product->getName());
                            $eachReview["details"]   = $this->_objectManager->create("Magento\Review\Helper\Data")->getDetailHtml($review->getDetail());
                            $ratingCollection = $this->_objectManager->create("Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection")
                                ->setReviewFilter($key)
                                ->addRatingInfo($storeId)
                                ->setStoreFilter($storeId)
                                ->load();
                            $ratingArray = [];
                            foreach ($ratingCollection as $rating) {
                                $eachRating = array();
                                $eachRating["ratingCode"]  = $this->_helperCatalog->stripTags($rating->getRatingCode());
                                $eachRating["ratingValue"] = number_format($rating->getPercent(), 2, ".", "");
                                $ratingArray[] = $eachRating;
                            }
                            $eachReview["ratingData"] = $ratingArray;
                            $reviewList[] = $eachReview;
                        }
                        $returnArray["reviewList"] = $reviewList;
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
                $returnArray["message"] = $e->getMessage();
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }