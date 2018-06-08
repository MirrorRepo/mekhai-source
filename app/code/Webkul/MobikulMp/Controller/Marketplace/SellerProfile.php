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

    class SellerProfile extends AbstractMarketplace    {

        public function execute()   {
            $returnArray                         = [];
            $returnArray["authKey"]              = "";
            $returnArray["message"]              = "";
            $returnArray["success"]              = false;
            $returnArray["vimeoId"]              = "";
            $returnArray["shopUrl"]              = "";
            $returnArray["sellerId"]             = "";
            $returnArray["location"]             = "";
            $returnArray["shopTitle"]            = "";
            $returnArray["twitterId"]            = "";
            $returnArray["youtubeId"]            = "";
            $returnArray["facebookId"]           = "";
            $returnArray["orderCount"]           = "";
            $returnArray["price5Star"]           = "";
            $returnArray["price4Star"]           = "";
            $returnArray["price3Star"]           = "";
            $returnArray["price2Star"]           = "";
            $returnArray["price1Star"]           = "";
            $returnArray["value5Star"]           = "";
            $returnArray["value4Star"]           = "";
            $returnArray["value3Star"]           = "";
            $returnArray["value2Star"]           = "";
            $returnArray["value1Star"]           = "";
            $returnArray["reviewList"]           = "";
            $returnArray["pinterestId"]          = "";
            $returnArray["bannerImage"]          = "";
            $returnArray["instagramId"]          = "";
            $returnArray["description"]          = "";
            $returnArray["productCount"]         = "";
            $returnArray["googleplusId"]         = "";
            $returnArray["profileImage"]         = "";
            $returnArray["returnPolicy"]         = "";
            $returnArray["quality5Star"]         = "";
            $returnArray["quality4Star"]         = "";
            $returnArray["quality3Star"]         = "";
            $returnArray["quality2Star"]         = "";
            $returnArray["quality1Star"]         = "";
            $returnArray["isVimeoActive"]        = false;
            $returnArray["averageRating"]        = "";
            $returnArray["feedbackCount"]        = "";
            $returnArray["shippingPolicy"]       = "";
            $returnArray["isYoutubeActive"]      = false;
            $returnArray["backgroundColor"]      = "";
            $returnArray["isTwitterActive"]      = false;
            $returnArray["isFacebookActive"]     = false;
            $returnArray["isInstagramActive"]    = false;
            $returnArray["isPinterestActive"]    = false;
            $returnArray["averagePriceRating"]   = "";
            $returnArray["isgoogleplusActive"]   = false;
            $returnArray["averageValueRating"]   = "";
            $returnArray["averageQualityRating"] = "";
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
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 0;
                        $sellerId    = $this->_helper->validate($wholeData, "sellerId")   ? $wholeData["sellerId"]   : 0;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $sellerCollection = $this->_seller->getCollection()
                            ->addFieldToFilter("is_seller", 1)
                            ->addFieldToFilter("seller_id", $sellerId)
                            ->addFieldToFilter("store_id", $storeId);
                        if (!count($sellerCollection)) {
                            $sellerCollection = $this->_seller->getCollection()
                                ->addFieldToFilter("is_seller", 1)
                                ->addFieldToFilter("seller_id", $sellerId)
                                ->addFieldToFilter("store_id", 0);
                        }
                        foreach ($sellerCollection as $eachSeller)
                            $seller = $eachSeller;
                        $logopic    = $seller->getLogoPic();
                        $bannerpic  = $seller->getBannerPic();
                        if (strlen($bannerpic) <= 0)
                            $bannerpic = "banner-image.png";
                        if (strlen($logopic) <= 0)
                            $logopic   = "noimage.png";
                        $returnArray["bannerImage"]     = $this->_marketplaceHelper->getMediaUrl()."avatar/".$bannerpic;
                        $returnArray["profileImage"]    = $this->_marketplaceHelper->getMediaUrl()."avatar/".$logopic;
                        $returnArray["backgroundColor"] = $seller->getBackgroundWidth();
                        $shopUrl   = $this->_viewTemplate->escapeHtml($seller->getShopUrl());
                        $shopTitle = $this->_viewTemplate->escapeHtml($seller->getShopTitle());
                        if (!$shopTitle)
                            $shopTitle = $shopUrl;
                        $returnArray["shopUrl"]   = $shopUrl;
                        $returnArray["sellerId"]  = $seller->getSellerId();
                        $returnArray["location"]  = $this->_viewTemplate->escapeHtml($seller->getCompanyLocality());
                        $returnArray["shopTitle"] = $shopTitle;
                        if($seller->getTwActive() == 1)     {
                            $returnArray["isTwitterActive"] = true;
                        }
                        $returnArray["twitterId"] = $seller->getTwitterId();
// seller facebook Details //////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($seller->getFbActive() == 1)     {
                            $returnArray["isFacebookActive"] = true;
                        }
                        $returnArray["facebookId"] = $seller->getFacebookId();
// seller instagram Details /////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($seller->getInstagramActive() == 1)     {
                            $returnArray["isInstagramActive"] = true;
                        }
                        $returnArray["instagramId"] = $seller->getInstagramId();
// seller google plus Details ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if($seller->getGplusActive() == 1)     {
                            $returnArray["isgoogleplusActive"] = true;
                        }
                        $returnArray["googleplusId"] = $seller->getGplusId();
// seller youtube Details ///////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($seller->getYoutubeActive() == 1)     {
                            $returnArray["isYoutubeActive"] = true;
                        }
                        $returnArray["youtubeId"] = $seller->getYoutubeId();
// seller Vimeo Details /////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($seller->getVimeoActive() == 1)     {
                            $returnArray["isVimeoActive"] = true;
                        }
                        $returnArray["vimeoId"] = $seller->getVimeoId();
// seller Pinterest Details /////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($seller->getPinterestActive() == 1)     {
                            $returnArray["isPinterestActive"] = true;
                        }
                        $returnArray["orderCount"]     = $this->_marketplaceOrderhelper->getSellerOrders($sellerId);
                        $returnArray["pinterestId"]    = $seller->getPinterestId();
                        $returnArray["description"]    = $seller->getCompanyDescription();
                        $returnArray["productCount"]   = $this->_marketplaceHelper->getSellerProCount($sellerId);
                        $returnArray["returnPolicy"]   = $seller->getReturnPolicy();
                        $returnArray["averageRating"]  = $this->_marketplaceHelper->getSelleRating($sellerId);
                        $returnArray["shippingPolicy"] = $seller->getShippingPolicy();
// getting recently added products //////////////////////////////////////////////////////////////////////////////////////////////
                        $catalogProductWebsite = $this->_marketplaceProductResource->getTable("catalog_product_website");
                        $websiteId = $this->_marketplaceHelper->getWebsiteId();
                        $querydata = $this->_marketplaceProduct->getCollection()
                            ->addFieldToFilter("seller_id", $sellerId)
                            ->addFieldToFilter("status",  ["neq"=>2])
                            ->addFieldToSelect("mageproduct_id")
                            ->setOrder("mageproduct_id");
                        $productCollection = $this->_productModel->getCollection()
                            ->addAttributeToSelect("*")
                            ->addAttributeToFilter("entity_id", ["in"=>$querydata->getAllIds()])
                            ->addAttributeToFilter("visibility", ["in"=>[4]])
                            ->addAttributeToFilter("status", 1);
                        if ($websiteId) {
                            $productCollection->getSelect()->join(["cpw"=>$catalogProductWebsite], "cpw.product_id=e.entity_id")
                                ->where("cpw.website_id=".$websiteId);
                        }
                        $productCollection->setPageSize(4)->setCurPage(1)->setOrder("entity_id");
                        $recentProductList = [];
                        foreach ($productCollection as $eachProduct) {
                            $eachProduct         = $this->_productFactory->create()->load($eachProduct->getId());
                            $recentProductList[] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["recentProductList"] = $recentProductList;
// getting rating data for seller ///////////////////////////////////////////////////////////////////////////////////////////////
                        $feeds = $this->_marketplaceHelper->getFeedTotal($sellerId);
                        if (empty($feeds["feed_price"]))
                            $feeds["feed_price"] = 0;
                        if (empty($feeds["feed_value"]))
                            $feeds["feed_value"] = 0;
                        if (empty($feeds["feed_quality"]))
                            $feeds["feed_quality"] = 0;
                        $returnArray["feedbackCount"]      = $feeds["feedcount"];
// price rating /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["price5Star"]         = $feeds["price_star_5"];
                        $returnArray["price4Star"]         = $feeds["price_star_4"];
                        $returnArray["price3Star"]         = $feeds["price_star_3"];
                        $returnArray["price2Star"]         = $feeds["price_star_2"];
                        $returnArray["price1Star"]         = $feeds["price_star_1"];
                        $returnArray["averagePriceRating"] = round(($feeds["price"]/20), 1, PHP_ROUND_HALF_UP);
// value rating /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["value5Star"]         = $feeds["value_star_5"];
                        $returnArray["value4Star"]         = $feeds["value_star_4"];
                        $returnArray["value3Star"]         = $feeds["value_star_3"];
                        $returnArray["value2Star"]         = $feeds["value_star_2"];
                        $returnArray["value1Star"]         = $feeds["value_star_1"];
                        $returnArray["averageValueRating"] = round(($feeds["value"]/20), 1, PHP_ROUND_HALF_UP);
// quality rating ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["quality5Star"]         = $feeds["quality_star_5"];
                        $returnArray["quality4Star"]         = $feeds["quality_star_4"];
                        $returnArray["quality3Star"]         = $feeds["quality_star_3"];
                        $returnArray["quality2Star"]         = $feeds["quality_star_2"];
                        $returnArray["quality1Star"]         = $feeds["quality_star_1"];
                        $returnArray["averageQualityRating"] = round(($feeds["quality"]/20), 1, PHP_ROUND_HALF_UP);
// getting review list //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $reviewCollection = $this->_reviewModel->getCollection()
                            ->addFieldToFilter("status", ["neq"=>0])
                            ->addFieldToFilter("seller_id", $sellerId)
                            ->setOrder("entity_id", "DESC")
                            ->setPageSize(4)
                            ->setCurPage(1);
                        $reviewList = [];
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