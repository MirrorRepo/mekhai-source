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

    class LandingPageData extends AbstractMarketplace    {

        public function execute()   {
            $returnArray                                   = [];
            $returnArray["authKey"]                        = "";
            $returnArray["success"]                        = false;
            $returnArray["message"]                        = "";
            $returnArray["pageLayout"]                     = 1;
// layout 1 default data ////////////////////////////////////////////////////////////////////////////////////////////////////////
            $returnArray["layout1"]["iconOne"]             = "";
            $returnArray["layout1"]["iconTwo"]             = "";
            $returnArray["layout1"]["labelOne"]            = "";
            $returnArray["layout1"]["labelTwo"]            = "";
            $returnArray["layout1"]["iconFour"]            = "";
            $returnArray["layout1"]["iconThree"]           = "";
            $returnArray["layout1"]["labelFour"]           = "";
            $returnArray["layout1"]["firstLabel"]          = "";
            $returnArray["layout1"]["labelThree"]          = "";
            $returnArray["layout1"]["thirdLabel"]          = "";
            $returnArray["layout1"]["fourthLabel"]         = "";
            $returnArray["layout1"]["bannerImage"]         = "";
            $returnArray["layout1"]["displayIcon"]         = false;
            $returnArray["layout1"]["showSellers"]         = false;
            $returnArray["layout1"]["secondLabel"]         = "";
            $returnArray["layout1"]["sellersData"]         = [];
            $returnArray["layout1"]["aboutContent"]        = "";
            $returnArray["layout1"]["displayBanner"]       = false;
            $returnArray["layout1"]["bannerContent"]       = "";
            $returnArray["layout1"]["buttonNHeadingLabel"] = "";
// layout 2 default data ////////////////////////////////////////////////////////////////////////////////////////////////////////
            $returnArray["layout2"]["buttonLabel"]         = "";
            $returnArray["layout2"]["bannerImage"]         = "";
            $returnArray["layout2"]["displayBanner"]       = false;
            $returnArray["layout2"]["bannerContent"]       = "";
// layout 3 default data ////////////////////////////////////////////////////////////////////////////////////////////////////////
            $returnArray["layout3"]["iconOne"]             = "";
            $returnArray["layout3"]["iconTwo"]             = "";
            $returnArray["layout3"]["labelOne"]            = "";
            $returnArray["layout3"]["labelTwo"]            = "";
            $returnArray["layout3"]["iconFour"]            = "";
            $returnArray["layout3"]["iconFive"]            = "";
            $returnArray["layout3"]["iconThree"]           = "";
            $returnArray["layout3"]["labelFour"]           = "";
            $returnArray["layout3"]["labelFive"]           = "";
            $returnArray["layout3"]["headingOne"]            = "";
            $returnArray["layout3"]["headingTwo"]            = "";
            $returnArray["layout3"]["labelThree"]          = "";
            $returnArray["layout3"]["bannerImage"]         = "";
            $returnArray["layout3"]["displayIcon"]         = false;
            $returnArray["layout3"]["headingThree"]          = "";
            $returnArray["layout3"]["displayBanner"]       = false;
            $returnArray["layout3"]["bannerContent"]       = "";
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
                        $width        = $this->_helper->validate($wholeData, "width")   ? $wholeData["width"]   : 1000;
                        $mFactor      = $this->_helper->validate($wholeData, "mFactor") ? $wholeData["mFactor"] : 1;
                        $storeId      = $this->_helper->validate($wholeData, "storeId") ? $wholeData["storeId"] : 0;
                        $environment  = $this->_emulate->startEnvironmentEmulation($storeId);
                        $Iconheight   = $IconWidth = 144 * $mFactor;
                        $bannerWidth  = $width * $mFactor;
                        $bannerHeight = ($width/2) * $mFactor;
                        $returnArray["pageLayout"] = $this->_marketplaceHelper->getPageLayout();
                        if($returnArray["pageLayout"] == 1) {
// collecting banner related data ///////////////////////////////////////////////////////////////////////////////////////////////
                            $returnArray["layout1"]["displayBanner"] = (bool)$this->_marketplaceHelper->getDisplayBanner();
                            $returnArray["layout1"]["bannerContent"] = $this->_marketplaceBlock->getCmsFilterContent($this->_marketplaceHelper->getBannerContent());
                            $returnArray["layout1"]["buttonNHeadingLabel"] = $this->_marketplaceHelper->getMarketplacebutton();
                            $bannerImage  = $this->_helper->getConfigData("marketplace/landingpage_settings/banner");
                            $basePath     = $this->_baseDir->getPath("media").DS."marketplace".DS."banner".DS.$bannerImage;
                            $newUrl       = "";
                            if (is_file($basePath)) {
                                $newPath = $this->_baseDir->getPath("media").DS."mobikulresized".DS.$bannerWidth."x".$bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
                                $this->_helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $bannerHeight);
                                $newUrl  = $this->_helper->getUrl("media")."mobikulresized".DS.$bannerWidth."x".$bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
                            }
                            $returnArray["layout1"]["bannerImage"] = $newUrl;
                            $returnArray["layout1"]["firstLabel"]  = $this->_escaper->escapeHtml($this->_marketplaceHelper->getMarketplacelabel1());
// collecting icon related data /////////////////////////////////////////////////////////////////////////////////////////////////
                            $returnArray["layout1"]["displayIcon"] = (bool)$this->_marketplaceHelper->getDisplayIcon();
                            if($returnArray["layout1"]["displayIcon"]){
                                $iconUrl  = "";
                                $icon1    = $this->_helper->getConfigData("marketplace/landingpage_settings/feature_icon1");
                                $basePath = $this->_baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon1;
                                if (is_file($basePath)) {
                                    $newPath = $this->_baseDir->getPath("media").DS."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon1;
                                    $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                                    $iconUrl = $this->_helper->getUrl("media")."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon1;
                                }
                                $returnArray["layout1"]["iconOne"]  = $iconUrl;
                                $returnArray["layout1"]["labelOne"] = $this->_escaper->escapeHtml($this->_marketplaceHelper->getIconImageLabel1());
                                $icon2    = $this->_helper->getConfigData("marketplace/landingpage_settings/feature_icon2");
                                $basePath = $this->_baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon2;
                                if (is_file($basePath)) {
                                    $newPath = $this->_baseDir->getPath("media").DS."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon2;
                                    $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                                    $iconUrl = $this->_helper->getUrl("media")."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon2;
                                }
                                $returnArray["layout1"]["iconTwo"]  = $iconUrl;
                                $returnArray["layout1"]["labelTwo"] = $this->_escaper->escapeHtml($this->_marketplaceHelper->getIconImageLabel2());
                                $icon3    = $this->_helper->getConfigData("marketplace/landingpage_settings/feature_icon3");
                                $basePath = $this->_baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon3;
                                if (is_file($basePath)) {
                                    $newPath = $this->_baseDir->getPath("media").DS."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon3;
                                    $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                                    $iconUrl = $this->_helper->getUrl("media")."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon3;
                                }
                                $returnArray["layout1"]["iconThree"]  = $iconUrl;
                                $returnArray["layout1"]["labelThree"] = $this->_escaper->escapeHtml($this->_marketplaceHelper->getIconImageLabel3());
                                $icon4    = $this->_helper->getConfigData("marketplace/landingpage_settings/feature_icon4");
                                $basePath = $this->_baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon4;
                                if (is_file($basePath)) {
                                    $newPath = $this->_baseDir->getPath("media").DS."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon4;
                                    $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                                    $iconUrl = $this->_helper->getUrl("media")."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon4;
                                }
                                $returnArray["layout1"]["iconFour"]  = $iconUrl;
                                $returnArray["layout1"]["labelFour"] = $this->_escaper->escapeHtml($this->_marketplaceHelper->getIconImageLabel4());
                            }
// seller details ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
                            $returnArray["layout1"]["showSellers"]     = (bool)$this->_marketplaceHelper->getSellerProfileDisplayFlag();
                            if ($returnArray["layout1"]["showSellers"])    {
                                $returnArray["layout1"]["secondLabel"] = $this->_escaper->escapeHtml($this->_marketplaceHelper->getMarketplacelabel2());
                                $bestSellersData  = $this->_marketplaceBlock->getBestSaleSellers();
                                $seller_arr       = $bestSellersData[0];
                                $sellerCountArr   = $bestSellersData[2];
                                $sellerProfileArr = $bestSellersData[1];
                                $i                = 0;
                                $sellersData      = [];
                                foreach ($seller_arr as $seller_id => $products) {
                                    $eachSellerData = [];
                                    $sellerProducts = [];
                                    $i++;
                                    if ($i <= 4) {
                                        $logo               = "noimage.png";
                                        $shoptitle          = "";
                                        $profileurl         = 0;
                                        $sellerProductCount = 0;
                                        $sellerProductCount = $sellerCountArr[$seller_id];
                                        if (isset($sellerProfileArr[$seller_id][0])) {
                                            $logo       = $sellerProfileArr[$seller_id][0]["logo"];
                                            $shoptitle  = $sellerProfileArr[$seller_id][0]["shoptitle"];
                                            $profileurl = $sellerProfileArr[$seller_id][0]["profileurl"];
                                        }
                                        if(!$shoptitle)
                                            $shoptitle = $profileurl;
                                        $basePath = $this->_baseDir->getPath("media")."/avatar/".$logo;
                                        if (is_file($basePath)) {
                                            $newPath = $this->_baseDir->getPath("media")."/mobikulresized/avatar/".$IconWidth."x".$Iconheight."/".$logo;
                                            $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                                        }
                                        $logo = $this->_helper->getUrl("media")."mobikulresized/avatar/".$IconWidth."x".$Iconheight."/".$logo;
                                        if(isset($products[0])){
                                            $product          = $this->_productRepository->getById($products[0]);
                                            $sellerProducts[] = $this->_helperCatalog->getOneProductRelevantData($product, $storeId, $width);
                                        }
                                        if(isset($products[1])){
                                            $product          = $this->_productRepository->getById($products[1]);
                                            $sellerProducts[] = $this->_helperCatalog->getOneProductRelevantData($product, $storeId, $width);
                                        }
                                        if(isset($products[2])){
                                            $product          = $this->_productRepository->getById($products[2]);
                                            $sellerProducts[] = $this->_helperCatalog->getOneProductRelevantData($product, $storeId, $width);
                                        }
                                    }
                                    $eachSellerData["logo"]         = $logo;
                                    $eachSellerData["products"]     = $sellerProducts;
                                    $eachSellerData["sellerId"]     = $seller_id;
                                    $eachSellerData["shoptitle"]    = $shoptitle;
                                    $eachSellerData["productCount"] = __("%1 Products", $sellerProductCount);
                                    $sellersData[]                  = $eachSellerData;
                                }
                                $returnArray["layout1"]["sellersData"] = $sellersData;
                                $returnArray["layout1"]["thirdLabel"]  = $this->_escaper->escapeHtml($this->_marketplaceHelper->getMarketplacelabel3());
                            }
                            $returnArray["layout1"]["fourthLabel"]  = $this->_escaper->escapeHtml($this->_marketplaceHelper->getMarketplacelabel4());
                            $returnArray["layout1"]["aboutContent"] = $this->_marketplaceBlock->getCmsFilterContent($this->_marketplaceHelper->getMarketplaceprofile());
                        }
                        else
                        if($returnArray["pageLayout"] == 2)     {
                            $returnArray["layout2"]["displayBanner"] = (bool)$this->_marketplaceHelper->getDisplayBannerLayout2();
                            if($returnArray["layout2"]["displayBanner"])    {
                                $returnArray["layout2"]["bannerContent"] = $this->_marketplaceBlock->getCmsFilterContent($this->_marketplaceHelper->getBannerContentLayout2());
                                $returnArray["layout2"]["buttonLabel"] = $this->_marketplaceHelper->getBannerButtonLayout2();
                                $bannerImage = $this->_helper->getConfigData("marketplace/landingpage_settings/bannerLayout2");
                                $basePath    = $this->_baseDir->getPath("media").DS."marketplace".DS."banner".DS.$bannerImage;
                                $newUrl      = "";
                                if (is_file($basePath)) {
                                    $newPath = $this->_baseDir->getPath("media").DS."mobikulresized".DS.$bannerWidth."x".$bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
                                    $this->_helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $bannerHeight);
                                    $newUrl  = $this->_helper->getUrl("media")."mobikulresized".DS.$bannerWidth."x".$bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
                                }
                                $returnArray["layout2"]["bannerImage"] = $newUrl;
                            }
                        }
                        else    {
                            $returnArray["layout3"]["displayBanner"] = (bool)$this->_marketplaceHelper->getDisplayBannerLayout3();
                            if($returnArray["layout3"]["displayBanner"]){
                                $returnArray["layout3"]["bannerContent"] = $this->_marketplaceBlock->getCmsFilterContent($this->_marketplaceHelper->getBannerContentLayout3());
                                $bannerImage = $this->_helper->getConfigData("marketplace/landingpage_settings/bannerLayout3");
                                $basePath    = $this->_baseDir->getPath("media").DS."marketplace".DS."banner".DS.$bannerImage;
                                $newUrl      = "";
                                if (is_file($basePath)) {
                                    $newPath = $this->_baseDir->getPath("media").DS."mobikulresized".DS.$bannerWidth."x".$bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
                                    $this->_helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $bannerHeight);
                                    $newUrl  = $this->_helper->getUrl("media")."mobikulresized".DS.$bannerWidth."x".$bannerHeight.DS."marketplace".DS."banner".DS.$bannerImage;
                                }
                                $returnArray["layout3"]["bannerImage"] = $newUrl;
                            }
                            $returnArray["layout3"]["headingOne"]    = $this->_marketplaceHelper->getMarketplacelabel1Layout3();
                            $returnArray["layout3"]["displayIcon"] = (bool)$this->_marketplaceHelper->getDisplayIconLayout3();
                            if($returnArray["layout3"]["displayIcon"])  {
                                $iconUrl  = "";
                                $icon1    = $this->_helper->getConfigData("marketplace/landingpage_settings/feature_icon1_layout3");
                                $basePath = $this->_baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon1;
                                if (is_file($basePath)) {
                                    $newPath = $this->_baseDir->getPath("media").DS."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon1;
                                    $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                                    $iconUrl = $this->_helper->getUrl("media")."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon1;
                                }
                                $returnArray["layout3"]["iconOne"]  = $iconUrl;
                                $returnArray["layout3"]["labelOne"] = $this->_escaper->escapeHtml($this->_marketplaceHelper->getIconImageLabel1Layout3());
                                $icon2    = $this->_helper->getConfigData("marketplace/landingpage_settings/feature_icon2_layout3");
                                $basePath = $this->_baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon2;
                                if (is_file($basePath)) {
                                    $newPath = $this->_baseDir->getPath("media").DS."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon2;
                                    $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                                    $iconUrl = $this->_helper->getUrl("media")."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon2;
                                }
                                $returnArray["layout3"]["iconTwo"]  = $iconUrl;
                                $returnArray["layout3"]["labelTwo"] = $this->_escaper->escapeHtml($this->_marketplaceHelper->getIconImageLabel2Layout3());
                                $icon3    = $this->_helper->getConfigData("marketplace/landingpage_settings/feature_icon3_layout3");
                                $basePath = $this->_baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon3;
                                if (is_file($basePath)) {
                                    $newPath = $this->_baseDir->getPath("media").DS."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon3;
                                    $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                                    $iconUrl = $this->_helper->getUrl("media")."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon3;
                                }
                                $returnArray["layout3"]["iconThree"]  = $iconUrl;
                                $returnArray["layout3"]["labelThree"] = $this->_escaper->escapeHtml($this->_marketplaceHelper->getIconImageLabel3Layout3());
                                $icon4    = $this->_helper->getConfigData("marketplace/landingpage_settings/feature_icon4_layout3");
                                $basePath = $this->_baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon4;
                                if (is_file($basePath)) {
                                    $newPath = $this->_baseDir->getPath("media").DS."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon4;
                                    $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                                    $iconUrl = $this->_helper->getUrl("media")."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon4;
                                }
                                $returnArray["layout3"]["iconFour"]  = $iconUrl;
                                $returnArray["layout3"]["labelFour"] = $this->_escaper->escapeHtml($this->_marketplaceHelper->getIconImageLabel4Layout3());
                                $icon5    = $this->_helper->getConfigData("marketplace/landingpage_settings/feature_icon5_layout3");
                                $basePath = $this->_baseDir->getPath("media").DS."marketplace".DS."icon".DS.$icon5;
                                if (is_file($basePath)) {
                                    $newPath = $this->_baseDir->getPath("media").DS."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon5;
                                    $this->_helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                                    $iconUrl = $this->_helper->getUrl("media")."mobikulresized".DS.$IconWidth."x".$Iconheight.DS."marketplace".DS."icon".DS.$icon5;
                                }
                                $returnArray["layout3"]["iconFive"]  = $iconUrl;
                                $returnArray["layout3"]["labelFive"] = $this->_escaper->escapeHtml($this->_marketplaceHelper->getIconImageLabel5Layout3());
                            }
                            $returnArray["layout3"]["headingTwo"]      = $this->_marketplaceHelper->getMarketplacelabel2Layout3();
                            $returnArray["layout3"]["headingThree"]    = $this->_marketplaceHelper->getMarketplacelabel3Layout3();
                        }
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