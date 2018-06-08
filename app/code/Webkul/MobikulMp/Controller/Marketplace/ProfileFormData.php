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

    class ProfileFormData extends AbstractMarketplace    {

        public function execute()   {
            $returnArray                           = [];
            $returnArray["taxvat"]                 = "";
            $returnArray["authKey"]                = "";
            $returnArray["message"]                = "";
            $returnArray["success"]                = false;
            $returnArray["vimeoId"]                = "";
            $returnArray["country"]                = "";
            $returnArray["twitterId"]              = "";
            $returnArray["youtubeId"]              = "";
            $returnArray["shopTitle"]              = "";
            $returnArray["facebookId"]             = "";
            $returnArray["taxvatHint"]             = "";
            $returnArray["bannerHint"]             = "";
            $returnArray["instagramId"]            = "";
            $returnArray["countryList"]            = "";
            $returnArray["pinterestId"]            = "";
            $returnArray["twitterHint"]            = "";
            $returnArray["metaKeyword"]            = "";
            $returnArray["bannerImage"]            = "";
            $returnArray["countryHint"]            = "";
            $returnArray["flagImageUrl"]           = "";
            $returnArray["responseCode"]           = "";
            $returnArray["facebookHint"]           = "";
            $returnArray["googleplusId"]           = "";
            $returnArray["profileImage"]           = "";
            $returnArray["returnPolicy"]           = "";
            $returnArray["contactNumber"]          = "";
            $returnArray["shopTitleHint"]          = "";
            $returnArray["isVimeoActive"]          = false;
            $returnArray["paymentDetails"]         = "";
            $returnArray["shippingPolicy"]         = "";
            $returnArray["companyLocality"]        = "";
            $returnArray["showProfileHint"]        = false;
            $returnArray["isYoutubeActive"]        = false;
            $returnArray["isTwitterActive"]        = false;
            $returnArray["backgroundColor"]        = "";
            $returnArray["metaDescription"]        = "";
            $returnArray["metaKeywordHint"]        = "";
            $returnArray["returnPolicyHint"]       = "";
            $returnArray["isFacebookActive"]       = false;
            $returnArray["profileImageHint"]       = "";
            $returnArray["isInstagramActive"]      = false;
            $returnArray["isPinterestActive"]      = false;
            $returnArray["contactNumberHint"]      = "";
            $returnArray["isgoogleplusActive"]     = false;
            $returnArray["companyDescription"]     = "";
            $returnArray["shippingPolicyHint"]     = "";
            $returnArray["paymentDetailsHint"]     = "";
            $returnArray["backgroundColorHint"]    = "";
            $returnArray["metaDescriptionHint"]    = "";
            $returnArray["companyLocalityHint"]    = "";
            $returnArray["companyDescriptionHint"] = "";
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
                        $storeId          = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 0;
                        $customerId       = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment      = $this->_emulate->startEnvironmentEmulation($storeId);
                        $data             = [];
                        $logopic          = "";
                        $bannerpic        = "";
                        $countrylogopic   = "";
                        $sellerCollection = $this->_seller->getCollection()
                            ->addFieldToFilter("seller_id", $customerId)
                            ->addFieldToFilter("store_id", $storeId);
                        if (!count($sellerCollection)) {
                            $sellerCollection = $this->_seller->getCollection()
                                ->addFieldToFilter("seller_id", $customerId)
                                ->addFieldToFilter("store_id", 0);
                        }
                        $customer = $this->_customer->load($customerId);
                        foreach ($sellerCollection as $seller)   {
                            $logopic        = $seller->getLogoPic();
                            $bannerpic      = $seller->getBannerPic();
                            $countrylogopic = $seller->getCountryPic();
                            if (strlen($bannerpic) <= 0)
                                $bannerpic  = "banner-image.png";
                            if (strlen($logopic) <= 0)
                                $logopic    = "noimage.png";
                        }
                        $returnArray["showProfileHint"] = (bool)$this->_marketplaceHelper->getProfileHintStatus();
// seller twitter Details ///////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($seller->getTwActive() == 1)     {
                            $returnArray["isTwitterActive"] = true;
                        }
                        $returnArray["twitterId"]    = $seller->getTwitterId();
                        $returnArray["twitterHint"]  = $this->_marketplaceHelper->getProfileHintTw();
// seller facebook Details //////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($seller->getFbActive() == 1)     {
                            $returnArray["isFacebookActive"] = true;
                        }
                        $returnArray["facebookId"]   = $seller->getFacebookId();
                        $returnArray["facebookHint"] = $this->_marketplaceHelper->getProfileHintFb();
// seller instagram Details /////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($seller->getInstagramActive() == 1)     {
                            $returnArray["isInstagramActive"] = true;
                        }
                        $returnArray["instagramId"]  = $seller->getInstagramId();
// seller google plus Details ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if($seller->getGplusActive() == 1)     {
                            $returnArray["isgoogleplusActive"] = true;
                        }
                        $returnArray["googleplusId"] = $seller->getGplusId();
// seller youtube Details ///////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($seller->getYoutubeActive() == 1)     {
                            $returnArray["isYoutubeActive"] = true;
                        }
                        $returnArray["youtubeId"]    = $seller->getYoutubeId();
// seller Vimeo Details /////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($seller->getVimeoActive() == 1)     {
                            $returnArray["isVimeoActive"] = true;
                        }
                        $returnArray["vimeoId"]      = $seller->getVimeoId();
// seller Pinterest Details /////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($seller->getPinterestActive() == 1)     {
                            $returnArray["isPinterestActive"] = true;
                        }
                        $returnArray["pinterestId"]  = $seller->getPinterestId();
// seller Contact Number Details ////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["contactNumber"]          = $seller->getContactNumber();
                        $returnArray["contactNumberHint"]      = $this->_marketplaceHelper->getProfileHintCn();
// seller Tax Vat Details ///////////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["taxvat"]                 = $customer->getTaxvat();
                        $returnArray["taxvatHint"]             = $this->_marketplaceHelper->getProductHintTax();
// seller Background Color Details //////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["backgroundColor"]        = $seller->getBackgroundWidth();
                        $returnArray["backgroundColorHint"]    = $this->_marketplaceHelper->getProfileHintBc();
// seller Shop Title Details ////////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["shopTitle"]              = $seller->getShopTitle();
                        $returnArray["shopTitleHint"]          = $this->_marketplaceHelper->getProfileHintShop();
// seller BannerImage Details ///////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["bannerHint"]             = $this->_marketplaceHelper->getProfileHintBanner();
                        $returnArray["bannerImage"]            = $this->_marketplaceHelper->getMediaUrl()."avatar/".$bannerpic;
// seller ProfileImage Details //////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["profileImageHint"]       = $this->_marketplaceHelper->getProfileHintLogo();
                        $returnArray["profileImage"]           = $this->_marketplaceHelper->getMediaUrl()."avatar/".$logopic;
// seller Company Locality Details //////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["companyLocalityHint"]    = $this->_marketplaceHelper->getProfileHintLoc();
                        $returnArray["companyLocality"]        = $seller->getCompanyLocality();
// seller Company Description Details ///////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["companyDescriptionHint"] = $this->_marketplaceHelper->getProfileHintDesc();
                        $returnArray["companyDescription"]     = $seller->getCompanyDescription();
// seller Return Policy Details /////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["returnPolicyHint"]       = $this->_marketplaceHelper->getProfileHintReturnPolicy();
                        $returnArray["returnPolicy"]           = $seller->getReturnPolicy();
// seller Shipping Policy Details ///////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["shippingPolicyHint"]     = $this->_marketplaceHelper->getProfileHintShippingPolicy();
                        $returnArray["shippingPolicy"]         = $seller->getShippingPolicy();
// seller Country Details ///////////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["countryHint"]            = $this->_marketplaceHelper->getProfileHintCountry();
                        $returnArray["country"]                = $seller->getCountryPic();
                        $destinations = $this->_helper->getConfigData("general/country/destinations");
                        $destinations = !empty($destinations) ? explode(",", $destinations) : [];
                        $countryCollection = $this->_countryCollectionFactory->create()->loadByStore()
                            ->setForegroundCountries($destinations)
                            ->toOptionArray();
                        $countryList = [];
                        foreach($countryCollection as $country){
                            $countryList[] = $country;
                        }
                        $returnArray["countryList"]            = $countryList;
                        $returnArray["flagImageUrl"]           = $this->_viewTemplate->getViewFileUrl("Webkul_Marketplace::images/country/countryflags/");
// seller Meta Keyword Details //////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["metaKeywordHint"]        = $this->_marketplaceHelper->getProfileHintMeta();
                        $returnArray["metaKeyword"]            = $seller->getMetaKeyword();
// seller Meta Description Details //////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["metaDescriptionHint"]    = $this->_marketplaceHelper->getProfileHintMetaDesc();
                        $returnArray["metaDescription"]        = $seller->getMetaDescription();
// seller Payment Details ///////////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["paymentDetailsHint"]     = $this->_marketplaceHelper->getProfileHintBank();
                        $returnArray["paymentDetails"]         = $seller->getPaymentSource();
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