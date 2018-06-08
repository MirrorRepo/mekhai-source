<?php
    /**
    * Webkul Software.
    *
    * @category Webkul
    *
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Controller\Catalog;

    class HomePageData extends AbstractCatalog   {

        public function execute()   {
            $returnArray                           = [];
            $returnArray["authKey"]                = "";
            $returnArray["success"]                = false;
            $returnArray["message"]                = "";
            $returnArray["storeId"]                = 0;
            $returnArray["cmsData"]                = [];
            $returnArray["hotDeals"]               = [];
            $returnArray["storeData"]              = [];
            $returnArray["cartCount"]              = 0;
            $returnArray["themeCode"]              = "";
            $returnArray["productId"]              = 0;
            $returnArray["otherError"]             = "";
            $returnArray["categories"]             = new \stdClass();
            $returnArray["categoryId"]             = 0;
            $returnArray["newProducts"]            = [];
            $returnArray["productName"]            = "";
            $returnArray["priceFormat"]            = new \stdClass();
            $returnArray["responseCode"]           = 0;
            $returnArray["bannerImages"]           = [];
            $returnArray["customerName"]           = "";
            $returnArray["categoryName"]           = "";
            $returnArray["customerEmail"]          = "";
            $returnArray["categoryImages"]         = [];
            $returnArray["defaultCurrency"]        = "";
            $returnArray["featuredProducts"]       = [];
            $returnArray["allowedCurrencies"]      = [];
            $returnArray["featuredCategories"]     = [];
            $returnArray["customerBannerImage"]    = "";
            $returnArray["customerProfileImage"]   = "";
            $returnArray["showSwatchOnCollection"] = false;
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
                        $url          = $this->_helper->validate($wholeData, "url")        ? $wholeData["url"]        : "";
                        $width        = $this->_helper->validate($wholeData, "width")      ? $wholeData["width"]      : 1000;
                        $quoteId      = $this->_helper->validate($wholeData, "quoteId")    ? $wholeData["quoteId"]    : 0;
                        $storeId      = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 0;
                        $mFactor      = $this->_helper->validate($wholeData, "mFactor")    ? $wholeData["mFactor"]    : 1;
                        $websiteId    = $this->_helper->validate($wholeData, "websiteId")  ? $wholeData["websiteId"]  : 0;
                        $isFromUrl    = $this->_helper->validate($wholeData, "isFromUrl")  ? $wholeData["isFromUrl"]  : 0;
                        $customerId   = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        if ($customerId != 0) {
                            $customer = $this->_customer->load($customerId);
                            if(!$customer->getId()){
                                $returnArray["message"]    = __("As customer you are requesting does not exist, so you need to logout.");
                                $returnArray["otherError"] = "customerNotExist";
                                $customerId = 0;
                            }
                        }
                        if ($storeId == 0) {
                            $storeId  = $this->_websiteManager->create()->load($websiteId)->getDefaultGroup()->getDefaultStoreId();
                            $returnArray["storeId"] = $storeId;
                        }
                        $environment  = $this->_emulate->startEnvironmentEmulation($storeId);
// getting currency data ////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["allowedCurrencies"] = $this->_store->getAvailableCurrencyCodes(false);
                        $baseCurrency = $this->_store->getBaseCurrencyCode();
                        $currency     = $this->_helper->validate($wholeData, "currency") ? $wholeData["currency"] : $baseCurrency;
                        $this->_store->setCurrentCurrencyCode($currency);
                        $returnArray["defaultCurrency"] = $baseCurrency;
// checking is swatch allowed on colletion page /////////////////////////////////////////////////////////////////////////////////
                        $returnArray["showSwatchOnCollection"] = (bool)$this->_helper->getConfigData("catalog/frontend/show_swatches_in_product_list");
// getting price format /////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["priceFormat"] = $this->_localeFormat->getPriceFormat();
// precessing deep linking //////////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($isFromUrl != 0) {
                            $baseurl  = $this->_storeInterface->getStore($storeId)->getBaseUrl();
                            $urlArray = explode($baseurl, $url);
                            if(count($urlArray) > 1){
                                $itemFound = $this->getDataFromUrl($urlArray[1], $storeId);
                                if($itemFound){
                                    if ($itemFound["entity_type"] == "product") {
                                        $returnArray["productId"]   = $itemFound["entity_id"];
                                        $returnArray["productName"] = $this->_productResourceModel->getAttributeRawValue($returnArray["productId"], "name", $storeId);
                                    } elseif ($itemFound["entity_type"] == "category") {
                                        $returnArray["categoryId"]   = $itemFound["entity_id"];
                                        $returnArray["categoryName"] = $this->_categoryResourceModel->getAttributeRawValue($returnArray["categoryId"], "name", $storeId);
                                    }
                                    $returnArray["success"] = true;
                                    return $this->getJsonResponse($returnArray);
                                }
                            }
                            else{
                                $returnArray["message"] = __("Sorry, something went wrong.");
                                return $this->getJsonResponse($returnArray);
                            }
                        }
// Theme Code of the application ////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["themeCode"] = $this->_helper->getConfigData("mobikul/theme/code");
// Category data for drawer menu ////////////////////////////////////////////////////////////////////////////////////////////////
                        $rootNode     = $this->_categoryTree->getRootNode();
                        $categories   = $this->_categoryTree->getTree($rootNode, null, $storeId);
                        $categoryTree = $categories->__toArray();
                        $returnArray["categories"] = $categoryTree;
// Featured Category ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $featuredCategoryCollection = $this->_featuredCategories
                            ->getCollection()
                            ->addFieldToFilter("status", 1)
                            ->setOrder("sort_order", "ASC");
                        $Iconheight         = $IconWidth = 144 * $mFactor;
                        $featuredCategories = [];
                        foreach ($featuredCategoryCollection as $eachCategory) {
                            $oneCategory = [];
                            $newUrl      = "";
                            $basePath    = $this->_baseDir.DS.$eachCategory->getFilename();
                            if (is_file($basePath)) {
                                $newPath = $this->_baseDir.DS."mobikulresized".DS.$IconWidth."x".$Iconheight.DS.$eachCategory->getFilename();
                                $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                                $newUrl  = $this->_helper->getUrl("media")."mobikulresized".DS.$IconWidth."x".$Iconheight.DS.$eachCategory->getFilename();
                            }
                            $oneCategory["url"]            = $newUrl;
                            $oneCategory["categoryName"]   = $this->_categoryResourceModel->getAttributeRawValue($eachCategory->getCategoryId(), "name", $storeId);
                            $oneCategory["categoryId"]     = $eachCategory->getCategoryId();
                            $featuredCategories[]          = $oneCategory;
                        }
                        $returnArray["featuredCategories"] = $featuredCategories;
// Banner Images ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $collection = $this->_bannerImage
                            ->getCollection()
                            ->addFieldToFilter("status", 1)
                            ->setOrder("sort_order", "ASC");
                        $bannerWidth  = $width * $mFactor;
                        $height       = ($width/2) * $mFactor;
                        $bannerImages = [];
                        foreach ($collection as $eachBanner) {
                            $oneBanner = [];
                            $newUrl    = "";
                            $basePath  = $this->_baseDir.DS.$eachBanner->getFilename();
                            if (is_file($basePath)) {
                                $newPath = $this->_baseDir.DS."mobikulresized".DS.$bannerWidth."x".$height.DS.$eachBanner->getFilename();
                                $this->_helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $height);
                                $newUrl = $this->_helper->getUrl("media")."mobikulresized".DS.$bannerWidth."x".$height.DS.$eachBanner->getFilename();
                            }
                            $oneBanner["url"] = $newUrl;
                            $oneBanner["bannerType"] = $eachBanner->getType();
                            if ($eachBanner->getType() == "category") {
                                $categoryName = $this->_categoryResourceModel->getAttributeRawValue($eachBanner->getProCatId(), "name", $storeId);
                                if ($categoryName != "")
                                    $oneBanner["error"]    = false;
                                else
                                    $oneBanner["error"]    = true;
                                $oneBanner["categoryName"] = $categoryName;
                                $oneBanner["categoryId"]   = $eachBanner->getProCatId();
                            } elseif ($eachBanner->getType() == "product") {
                                $productName = $this->_productResourceModel->getAttributeRawValue($eachBanner->getProCatId(), "name", $storeId);
                                if ($productName != "")
                                    $oneBanner["error"] = false;
                                else
                                    $oneBanner["error"] = true;
                                $oneBanner["productName"] = $productName;
                                $oneBanner["productId"] = $eachBanner->getProCatId();
                            }
                            $bannerImages[] = $oneBanner;
                        }
                        $returnArray["bannerImages"] = $bannerImages;
// Featured Products ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $featuredProducts          = [];
                        $featuredProductCollection = new \Magento\Framework\DataObject();
                        $attributes = $this->_catalogConfig->getProductAttributes();
                        if($this->_helper->getConfigData("mobikul/configuration/featuredproduct") == 1)    {
                            $featuredProductCollection = $this->_productCollection->create()->addAttributeToSelect($attributes);
                            $featuredProductCollection->getSelect()->order("rand()");
                            $featuredProductCollection->addAttributeToFilter("status", ["in"=>$this->_productStatus->getVisibleStatusIds()]);
                            $featuredProductCollection->setVisibility($this->_productVisibility->getVisibleInSiteIds());
                            if($this->_helperCatalog->showOutOfStock() == 0)
                                $this->_stockFilter->addInStockFilterToCollection($featuredProductCollection);
                            $featuredProductCollection->setPage(1, 5)->load();
                        }
                        else    {
                            $featuredProductCollection = $this->_productCollection->create()
                                ->setStore($storeId)
                                ->addAttributeToSelect($attributes)
                                ->addAttributeToSelect("as_featured")
                                ->addAttributeToSelect("visibility")
                                ->addStoreFilter()
                                ->addAttributeToFilter("status", ["in"=>$this->_productStatus->getVisibleStatusIds()])
                                ->setVisibility($this->_productVisibility->getVisibleInSiteIds())
                                ->addAttributeToFilter("as_featured", 1);
                            if($this->_helperCatalog->showOutOfStock() == 0)
                                $this->_stockFilter->addInStockFilterToCollection($featuredProductCollection);
                            $featuredProductCollection->setPageSize(5)->setCurPage(1);
                        }
                        foreach ($featuredProductCollection as $eachProduct) {
                            $eachProduct = $this->_productFactory->create()->load($eachProduct->getId());
                            if($eachProduct->isAvailable() || (bool)$this->_helper->getConfigData("cataloginventory/options/show_out_of_stock"))
                                $featuredProducts[] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["featuredProducts"] = $featuredProducts;
// New Products /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $newProducts          = [];
                        $todayStartOfDayDate  = $this->_localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
                        $todayEndOfDayDate    = $this->_localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
                        $newProductCollection = $this->_productCollection->create()
                            ->setVisibility($this->_productVisibility->getVisibleInCatalogIds())
                            ->addMinimalPrice()
                            ->addFinalPrice()
                            ->addTaxPercents()
                            ->addAttributeToSelect($attributes)
                            ->addUrlRewrite()
                            ->addStoreFilter()
                            ->addAttributeToFilter("news_from_date", ["or"=>[
                                0=>["date"=>true, "to"=>$todayEndOfDayDate],
                                1=>["is"=>new \Zend_Db_Expr("null")]]
                            ], "left")
                            ->addAttributeToFilter("news_to_date", ["or"=>[
                                0=>["date"=>true, "from"=>$todayStartOfDayDate],
                                1=>["is"=>new \Zend_Db_Expr("null")]]
                            ], "left")
                            ->addAttributeToFilter([
                                ["attribute"=>"news_from_date", "is"=>new \Zend_Db_Expr("not null")],
                                ["attribute"=>"news_to_date", "is"=>new \Zend_Db_Expr("not null")]
                            ])
                            ->addAttributeToSort("news_from_date", "desc");
                        if($this->_helperCatalog->showOutOfStock() == 0)
                            $this->_stockFilter->addInStockFilterToCollection($newProductCollection);
                        $newProductCollection->setPageSize(5)->setCurPage(1);
                        foreach ($newProductCollection as $eachProduct) {
                            $eachProduct = $this->_productFactory->create()->load($eachProduct->getId());
                            if($eachProduct->isAvailable() || (bool)$this->_helper->getConfigData("cataloginventory/options/show_out_of_stock"))
                                $newProducts[] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["newProducts"] = $newProducts;
// Hot Products /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $hotDeals = [];
                        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
                        $todayEndOfDayDate   = $this->_localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
                        $hotDealCollection   = $this->_productCollection->create()
                            ->setVisibility($this->_productVisibility->getVisibleInCatalogIds())
                            ->addMinimalPrice()
                            ->addFinalPrice()
                            ->addTaxPercents()
                            ->addAttributeToSelect("special_from_date")
                            ->addAttributeToSelect("special_to_date")
                            ->addAttributeToSelect($attributes);
                        $hotDealCollection->addStoreFilter()
                            ->addAttributeToFilter("special_from_date", ["or"=>[
                                0=>["date"=>true, "to"=>$todayEndOfDayDate],
                                1=>["is"=>new \Zend_Db_Expr("null")]]
                            ], "left")
                            ->addAttributeToFilter("special_to_date", ["or"=>[
                                0=>["date"=>true, "from"=>$todayStartOfDayDate],
                                1=>["is"=>new \Zend_Db_Expr("null")]]
                            ], "left")
                            ->addAttributeToFilter([
                                ["attribute"=>"special_from_date", "is"=>new \Zend_Db_Expr("not null")],
                                ["attribute"=>"special_to_date", "is"=>new \Zend_Db_Expr("not null")]
                            ]);
                        if($this->_helperCatalog->showOutOfStock() == 0)
                            $this->_stockFilter->addInStockFilterToCollection($hotDealCollection);
                        $hotDealCollection->setPageSize(5)->setCurPage(1);
                        foreach ($hotDealCollection as $eachProduct) {
                            $eachProduct = $this->_productFactory->create()->load($eachProduct->getId());
                            if($eachProduct->isAvailable() || (bool)$this->_helper->getConfigData("cataloginventory/options/show_out_of_stock"))
                                $hotDeals[] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["hotDeals"]  = $hotDeals;
// Store Data ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["storeData"] = $this->_helperCatalog->getStoreData();
// Customer Profile and Banner Images ///////////////////////////////////////////////////////////////////////////////////////////
                        if ($customerId != 0) {
                            $returnArray["customerName"]  = $customer->getName();
                            $returnArray["customerEmail"] = $customer->getEmail();
                            $quoteCollection = $this->_quote
                                ->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC");
                            $quote = $quoteCollection->getFirstItem();
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                            $height = $width / 2;
                            $collection = $this->_customerImage->getCollection()->addFieldToFilter("customer_id", $customerId);
                            $time = time();
                            if ($collection->getSize() > 0) {
                                foreach ($collection as $value) {
                                    if ($value->getBanner() != "") {
                                        $basePath = $this->_baseDir.DS."mobikul".DS."customerpicture".DS.$customerId.DS.$value->getBanner();
                                        $newUrl = "";
                                        if (is_file($basePath)) {
                                            $newPath = $this->_baseDir.DS."mobikul".DS."customerpicture".DS.$customerId.DS.$bannerWidth."x".$height.DS.$value->getBanner();
                                            $this->_helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $height);
                                            $newUrl = $this->_helper->getUrl("media")."mobikul".DS."customerpicture".DS.$customerId.DS.$bannerWidth."x".$height.DS.$value->getBanner();
                                        }
                                        $returnArray["customerBannerImage"] = $newUrl."?".$time;
                                    }
                                    if ($value->getProfile() != "") {
                                        $basePath = $this->_baseDir.DS."mobikul".DS."customerpicture".DS.$customerId.DS.$value->getProfile();
                                        $newUrl = "";
                                        if (is_file($basePath)) {
                                            $newPath = $this->_baseDir.DS."mobikul".DS."customerpicture".DS.$customerId.DS.$IconWidth."x".$Iconheight.DS.$value->getProfile();
                                            $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                                            $newUrl = $this->_helper->getUrl("media")."mobikul".DS."customerpicture".DS.$customerId.DS.$IconWidth."x".$Iconheight.DS.$value->getProfile();
                                        }
                                        $returnArray["customerProfileImage"] = $newUrl."?".$time;
                                    }
                                }
                            }
                        }
// Category Image Collection ////////////////////////////////////////////////////////////////////////////////////////////////////
                        $categoryImgCollection = $this->_categoryImageFactory->create()->getCollection();
                        $categoryImages = [];
                        foreach ($categoryImgCollection as $categoryImage) {
                            if ($categoryImage->getBanner() != "" && $categoryImage->getIcon() != "") {
                                $eachCategoryImage["id"] = $categoryImage->getCategoryId();
                                if ($categoryImage->getBanner() != "") {
                                    $basePath = $this->_baseDir.DS."mobikul".DS."categoryimages".DS."banner".DS.$categoryImage->getBanner();
                                    $newUrl = "";
                                    if (is_file($basePath)) {
                                        $newPath = $this->_baseDir.DS."mobikul".DS."categoryimages".DS.$bannerWidth."x".$height.DS.$categoryImage->getBanner();
                                        $this->_helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $height);
                                        $newUrl = $this->_helper->getUrl("media")."mobikul".DS."categoryimages".DS.$bannerWidth."x".$height.DS.$categoryImage->getBanner();
                                    }
                                    $eachCategoryImage["banner"] = $newUrl;
                                }
                                if ($categoryImage->getIcon() != "") {
                                    $basePath = $this->_baseDir.DS."mobikul".DS."categoryimages".DS."icon".DS.$categoryImage->getIcon();
                                    $newUrl = "";
                                    if (is_file($basePath)) {
                                        $newPath = $this->_baseDir.DS."mobikul".DS."categoryimages".DS.$IconWidth."x".$Iconheight.DS.$categoryImage->getIcon();
                                        $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                                        $newUrl = $this->_helper->getUrl("media")."mobikul".DS."categoryimages".DS.$IconWidth."x".$Iconheight.DS.$categoryImage->getIcon();
                                    }
                                    $eachCategoryImage["thumbnail"] = $newUrl;
                                }
                                $categoryImages[] = $eachCategoryImage;
                            }
                        }
                        $returnArray["categoryImages"] = $categoryImages;
// Getting CMS page data ////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $allowedCmsPages = $this->_helper->getConfigData("mobikul/configuration/cms");
                        if($allowedCmsPages != ""){
                            $allowedIds  = explode(",", $allowedCmsPages);
                            $collection  = $this->_cmsCollection
                                ->addFieldToFilter("is_active", \Magento\Cms\Model\Page::STATUS_ENABLED)
                                ->addFieldToFilter("store_id", $storeId)
                                ->addFieldToFilter("page_id", ["in"=>$allowedIds]);
                            $cmsData    = [];
                            foreach ($collection as $cms) {
                                $cmsData[] = [
                                    "id"    => $cms->getId(),
                                    "title" => $cms->getTitle()
                                ];
                            }
                            $returnArray["cmsData"] = $cmsData;
                        }
// Cart Count ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($quoteId != 0) {
                            $returnArray["cartCount"] = $this->_quote->setStoreId($storeId)->load($quoteId)->getItemsQty() * 1;
                        }
                        if($returnArray["otherError"] == "")
                            $returnArray["success"] = true;
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
                $returnArray["message"] = __($e->getMessage());
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

        private function getDataFromUrl($url, $storeId)    {
            $path     = [$url];
            $pathBind = [];
            foreach ($path as $key => $url)
                $pathBind["path".$key] = $url;
            $tableName = $this->_connection->getTableName("url_rewrite");
            $sql       = "select * from ".$tableName." where request_path IN (:".implode(", :", array_flip($pathBind)).") AND store_id IN(0,".$storeId.")";
            $items          = $this->_connection->getConnection()->fetchAll($sql, $pathBind);
            $foundItem      = null;
            $mapPenalty     = array_flip(array_values($path));
            $currentPenalty = null;
            foreach ($items as $item) {
                if (!array_key_exists($item["request_path"], $mapPenalty))
                    continue;
                $penalty = $mapPenalty[$item["request_path"]] << 1 + ($item["store_id"] ? 0 : 1);
                if (!$foundItem || $currentPenalty > $penalty) {
                    $foundItem      = $item;
                    $currentPenalty = $penalty;
                    if (!$currentPenalty)
                        break;
                }
            }
            return $foundItem;
        }

    }