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

     namespace Webkul\MobikulMp\Controller\Product;

    class NewFormData extends AbstractProduct    {

        public function execute()   {
            $returnArray                                 = [];
            $returnArray["authKey"]                      = "";
            $returnArray["message"]                      = "";
            $returnArray["skuType"]                      = "";
            $returnArray["skuhint"]                      = "";
            $returnArray["taxHint"]                      = "";
            $returnArray["success"]                      = false;
            $returnArray["showHint"]                     = false;
            $returnArray["skuPrefix"]                    = "";
            $returnArray["priceHint"]                    = "";
            $returnArray["taxOptions"]                   = [];
            $returnArray["categories"]                   = [];
            $returnArray["weightHint"]                   = "";
            $returnArray["productHint"]                  = "";
            $returnArray["categoryHint"]                 = "";
            $returnArray["allowedTypes"]                 = [];
            $returnArray["inventoryHint"]                = "";
            $returnArray["currencySymbol"]               = "";
            $returnArray["descriptionHint"]              = "";
            $returnArray["specialPriceHint"]             = "";
            $returnArray["visibilityOptions"]            = [];
            $returnArray["allowedAttributes"]            = [];
            $returnArray["specialEndDateHint"]           = "";
            $returnArray["shortdescriptionHint"]         = "";
            $returnArray["specialStartDateHint"]         = "";
            $returnArray["isCategoryTreeAllowed"]        = false;
            $returnArray["inventoryAvailabilityHint"]    = "";
            $returnArray["inventoryAvailabilityOptions"] = [
                ["value"=>1, "label"=>__("In Stock")],
                ["value"=>0, "label"=>__("Out of Stock")]
            ];
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
                        $storeId      = $this->_helper->validate($wholeData, "storeId") ? $wholeData["storeId"] : 0;
                        $environment  = $this->_emulate->startEnvironmentEmulation($storeId);
                        $attibuteSets = [];
                        if (count($this->_marketplaceHelper->getAllowedSets()) > 1) {
                            foreach($this->_marketplaceHelper->getAllowedSets() as $set)
                                $attibuteSets[] = $set;
                            $returnArray["allowedAttributes"] = $attibuteSets;
                        } else {
                            $allowedSets = $this->_marketplaceHelper->getAllowedSets();
                            $returnArray["allowedAttributes"][] = $allowedSets[0]["value"];
                        }
                        $allowedTypes = [];
                        if (count($this->_marketplaceHelper->getAllowedProductTypes()) > 1) {
                            foreach($this->_marketplaceHelper->getAllowedProductTypes() as $type)
                                $allowedTypes[] = $type;
                            $returnArray["allowedTypes"] = $allowedTypes;
                        } else {
                            $allowedProducts = $this->_marketplaceHelper->getAllowedProductTypes();
                            $returnArray["allowedTypes"][] = $allowedProducts[0]["value"];
                        }
                        $returnArray["showHint"] = (bool)$this->_marketplaceHelper->getProductHintStatus();
                        $returnArray["isCategoryTreeAllowed"] = (bool)$this->_marketplaceHelper->getIsAdminViewCategoryTree();
                        if($this->_marketplaceHelper->getAllowedCategoryIds() && !$returnArray["isCategoryTreeAllowed"]){
                            $categoryIds = explode(",", trim($this->_marketplaceHelper->getAllowedCategoryIds()));
                            foreach($categoryIds as $categoryId)    {
                                $category = $this->_category->setStoreId($storeId)->load($categoryId);
                                if($category->getId())     {
                                    $eachCategory                = [];
                                    $eachCategory["id"]          = $category->getId();
                                    $eachCategory["name"]        = $category->getName();
                                    $returnArray["categories"][] = $eachCategory;
                                }
                            }
                        }
                        else{
                            $rootNode = $this->_categoryTree->getRootNode();
                            $returnArray["categories"] = $this->_categoryTree->getTree($rootNode, null, $storeId)->__toArray();
                        }
                        if(count($returnArray["categories"]) == 0)  {
                            $rootNode = $this->_categoryTree->getRootNode();
                            $returnArray["categories"] = $this->_categoryTree->getTree($rootNode, null, $storeId)->__toArray();
                        }
                        $returnArray["skuType"]                   = $this->_marketplaceHelper->getSkuType();
                        $returnArray["skuhint"]                   = $this->_marketplaceHelper->getProductHintSku();
                        $returnArray["skuPrefix"]                 = $this->_marketplaceHelper->getSkuPrefix();
                        $returnArray["priceHint"]                 = $this->_marketplaceHelper->getProductHintPrice();
                        $returnArray["productHint"]               = $this->_marketplaceHelper->getProductHintName();
                        $returnArray["categoryHint"]              = $this->_marketplaceHelper->getProductHintCategory();
                        $returnArray["inventoryHint"]             = $this->_marketplaceHelper->getProductHintQty();
                        $returnArray["currencySymbol"]            = $this->_marketplaceHelper->getCurrencySymbol();
                        $returnArray["descriptionHint"]           = $this->_marketplaceHelper->getProductHintDesc();
                        $returnArray["specialPriceHint"]          = $this->_marketplaceHelper->getProductHintSpecialPrice();
                        $returnArray["specialEndDateHint"]        = $this->_marketplaceHelper->getProductHintEndDate();
                        $returnArray["shortdescriptionHint"]      = $this->_marketplaceHelper->getProductHintShortDesc();
                        $returnArray["specialStartDateHint"]      = $this->_marketplaceHelper->getProductHintStartDate();
                        $returnArray["inventoryAvailabilityHint"] = $this->_marketplaceHelper->getProductHintStock();
                        $productVisibility                        = $this->_marketplaceHelper->getVisibilityOptionArray();
                        foreach($productVisibility as $key => $value){
                            $returnArray["visibilityOptions"][] = ["value"=>$key, "label"=>$value];
                        }
                        $returnArray["taxHint"] = $this->_marketplaceHelper->getProductHintTax();
                        $taxes = $this->_marketplaceHelper->getTaxClassModel();
                        foreach($taxes as $tax){
                            $returnArray["taxOptions"][] = ["value"=>$tax->getId(), "label"=>$tax->getClassName()];
                        }
                        $returnArray["weightHint"] = $this->_marketplaceHelper->getProductHintWeight();
                        $returnArray["success"]    = true;
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