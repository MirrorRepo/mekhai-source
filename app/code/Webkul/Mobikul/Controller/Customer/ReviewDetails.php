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

    class ReviewDetails extends AbstractCustomer      {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["name"]         = "";
            $returnArray["image"]        = "";
            $returnArray["rating"]       = 0;
            $returnArray["authKey"]      = "";
            $returnArray["message"]      = "";
            $returnArray["productId"]    = 0;
            $returnArray["ratingData"]   = [];
            $returnArray["reviewDate"]   = "";
            $returnArray["reviewDetail"] = "";
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
                        $width       = $this->_helper->validate($wholeData, "width")    ? $wholeData["width"]    : 10000;
                        $storeId     = $this->_helper->validate($wholeData, "storeId")  ? $wholeData["storeId"]  : 1;
                        $reviewId    = $this->_helper->validate($wholeData, "reviewId") ? $wholeData["reviewId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $review  = $this->_objectManager->create("\Magento\Review\Model\Review")->load($reviewId);
                        $product = $this->_objectManager->create("\Magento\Catalog\Model\Product")->setStoreId($storeId)
                        ->load($review->getEntityPkValue());
                        $returnArray["productId"] = $product->getId();
                        $returnArray["name"]      = $this->_helperCatalog->stripTags($product->getName());
                        $returnArray["image"]     = $this->_helperCatalog->getImageUrl($product, $width/2);
                        $ratingArray              = [];
                        $ratingCollection         = $this->_objectManager
                            ->create("\Magento\Review\Model\Rating\Option\Vote")
                            ->getResourceCollection()
                            ->setReviewFilter($reviewId)
                            ->addRatingInfo($storeId)
                            ->setStoreFilter($storeId)
                            ->load();
                        foreach ($ratingCollection as $rating) {
                            $eachRating                = [];
                            $eachRating["ratingCode"]  = $this->_helperCatalog->stripTags($rating->getRatingCode());
                            $eachRating["ratingValue"] = number_format($rating->getPercent(), 2, ".", "");
                            $ratingArray[]             = $eachRating;
                        }
                        $returnArray["ratingData"] = $ratingArray;
                        $returnArray["reviewDate"] = __("Your Review (submitted on %1)", $this->_helperCatalog->formatDate($review->getCreatedAt(), "long"));
                        $returnArray["reviewDetail"] = $this->_helperCatalog->stripTags($review->getDetail());
                        $reviews = $this->_objectManager
                            ->create("\Magento\Review\Model\Review")
                            ->getResourceCollection()
                            ->addStoreFilter($storeId)
                            ->addEntityFilter("product", $product->getId())
                            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
                            ->setDateOrder()
                            ->addRateVotes();
                        $ratings = [];
                        if (count($reviews) > 0) {
                            foreach ($reviews->getItems() as $review) {
                                foreach ($review->getRatingVotes() as $vote) {
                                    $ratings[] = $vote->getPercent();
                                }
                            }
                        }
                        if (count($ratings) > 0)
                            $returnArray["rating"] = number_format((5*(array_sum($ratings) / count($ratings)))/100, 2, ".", "");
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