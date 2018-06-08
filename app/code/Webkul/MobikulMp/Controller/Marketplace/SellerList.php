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

    class SellerList extends AbstractMarketplace    {

        public function execute()   {
            $returnArray                        = [];
            $returnArray["authKey"]             = "";
            $returnArray["message"]             = "";
            $returnArray["success"]             = false;
            $returnArray["topLabel"]            = "";
            $returnArray["bannerImage"]         = "";
            $returnArray["sellersData"]         = [];
            $returnArray["displayBanner"]       = "";
            $returnArray["bannerContent"]       = "";
            $returnArray["buttonNHeadingLabel"] = "";
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
                        $width        = $this->_helper->validate($wholeData, "width")       ? $wholeData["width"]       : 1000;
                        $storeId      = $this->_helper->validate($wholeData, "storeId")     ? $wholeData["storeId"]     : 0;
                        $mFactor      = $this->_helper->validate($wholeData, "mFactor")     ? $wholeData["mFactor"]     : 1;
                        $searchQuery  = $this->_helper->validate($wholeData, "searchQuery") ? $wholeData["searchQuery"] : "";
                        $environment  = $this->_emulate->startEnvironmentEmulation($storeId);
                        $Iconheight   = $IconWidth = 144 * $mFactor;
                        $bannerWidth  = $width * $mFactor;
                        $bannerHeight = ($width/2) * $mFactor;
                        $returnArray["displayBanner"] = (bool)$this->_marketplaceHelper->getDisplayBanner();
                        $returnArray["bannerContent"] = $this->_marketplaceBlock->getCmsFilterContent($this->_marketplaceHelper->getBannerContent());
                        $returnArray["buttonNHeadingLabel"] = $this->_marketplaceHelper->getMarketplacebutton();
                        $bannerImage  = $this->_helper->getConfigData("marketplace/landingpage_settings/banner");
                        $basePath     = $this->_baseDir->getPath("media").DS."marketplace".DS."banner".DS.$bannerImage;
                        $newUrl       = "";
                        if (is_file($basePath)) {
                            $newPath = $this->_baseDir->getPath("media").DS."mobikulresized".DS.$bannerWidth."x".$bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
                            $this->_helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $bannerHeight);
                            $newUrl  = $this->_helper->getUrl("media")."mobikulresized".DS.$bannerWidth."x".$bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
                        }
                        $returnArray["bannerImage"] = $newUrl;
                        $returnArray["topLabel"]    = $this->_viewTemplate->escapeHtml($this->_marketplaceHelper->getSellerlisttopLabel());
                        $sellerArr         = [];
                        $sellerProductColl = $this->_marketplaceProduct
                            ->getCollection()
                            ->addFieldToFilter("status", 1)
                            ->addFieldToSelect("seller_id")
                            ->distinct(true);
                        $sellerArr = $sellerProductColl->getAllSellerIds();
                        $storeCollection = $this->_sellerlistCollectionFactory
                            ->create()
                            ->addFieldToSelect("*")
                            ->addFieldToFilter("seller_id", ["in"=>$sellerArr])
                            ->addFieldToFilter("is_seller", 1)
                            ->addFieldToFilter("store_id", $storeId)
                            ->setOrder("entity_id", "desc");
                        $storeSellerIDs     = $storeCollection->getAllIds();
                        $storeMainSellerIDs = $storeCollection->getAllSellerIds();
                        $sellerArr = array_diff($sellerArr, $storeMainSellerIDs);
                        $adminStoreCollection = $this->_sellerlistCollectionFactory
                            ->create()
                            ->addFieldToSelect("*")
                            ->addFieldToFilter("seller_id", ["in"=>$sellerArr]);
                        if (!empty($storeSellerIDs)) {
                            $adminStoreCollection->addFieldToFilter("entity_id", ["nin"=>$storeSellerIDs]);
                        }
                        $adminStoreCollection->addFieldToFilter("is_seller", ["eq"=>1])
                            ->addFieldToFilter("store_id", 0)
                            ->setOrder("entity_id", "desc");
                        $adminStoreSellerIDs = $adminStoreCollection->getAllIds();
                        $allSellerIDs = array_merge($storeSellerIDs, $adminStoreSellerIDs);
                        $collection = $this->_sellerlistCollectionFactory
                            ->create()
                            ->addFieldToSelect("*")
                            ->addFieldToFilter("entity_id", ["in"=>$allSellerIDs])
                            ->setOrder("entity_id", "desc");
                        if ($searchQuery) {
                            $collection->addFieldToFilter(["shop_title", "shop_url"], [
                                    ["like"=>"%".$searchQuery."%"],
                                    ["like"=>"%".$searchQuery."%"]
                            ]);
                        }
                        $websiteId = $this->_marketplaceHelper->getWebsiteId();
                        $joinTable = $this->_sellerCollection->getTable("customer_grid_flat");
                        $collection->getSelect()->join($joinTable." as cgf", "main_table.seller_id=cgf.entity_id AND website_id=".$websiteId);
                        $sellersData            = [];
                        foreach($collection as $seller){
                            $eachSellerData     = [];
                            $sellerId           = $seller->getSellerId();
                            $sellerProductCount = 0;
                            $profileurl         = $seller->getShopUrl();
                            $shoptitle          = "";
                            $sellerProductCount = $this->_marketplaceHelper->getSellerProCount($sellerId);
                            $shoptitle          = $seller->getShopTitle();
                            $logo               = $seller->getLogoPic() == "" ? "noimage.png" : $seller->getLogoPic();
                            if(!$shoptitle){
                                $shoptitle = $profileurl;
                            }
                            $basePath = $this->_baseDir->getPath("media")."/avatar/".$logo;
                            if (is_file($basePath)) {
                                $newPath = $this->_baseDir->getPath("media")."/mobikulresized/avatar/".$IconWidth."x".$Iconheight."/".$logo;
                                $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                            }
                            $logo = $this->_helper->getUrl("media")."mobikulresized/avatar/".$IconWidth."x".$Iconheight."/".$logo;
                            $eachSellerData["logo"]         = $logo;
                            $eachSellerData["sellerId"]     = $sellerId;
                            $eachSellerData["shoptitle"]    = $shoptitle;
                            $eachSellerData["productCount"] = __("%1 Products", $sellerProductCount);
                            $sellersData[]                  = $eachSellerData;
                        }
                        $returnArray["sellersData"] = $sellersData;
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