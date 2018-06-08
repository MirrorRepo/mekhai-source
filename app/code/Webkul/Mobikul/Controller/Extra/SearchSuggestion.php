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

    namespace Webkul\Mobikul\Controller\Extra;

    class SearchSuggestion extends AbstractMobikul    {

        public function execute()   {
            $returnArray                        = [];
            $returnArray["authKey"]             = "";
            $returnArray["message"]             = "";
            $returnArray["responseCode"]        = 0;
            $returnArray["suggestProductArray"] = [];
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
                        $storeId        = $this->_helper->validate($wholeData, "storeId")     ? $wholeData["storeId"]     : 1;
                        $categoryId     = $this->_helper->validate($wholeData, "categoryId")  ? $wholeData["categoryId"]  : 0;
                        $searchQuery    = $this->_helper->validate($wholeData, "searchQuery") ? $wholeData["searchQuery"] : "";
                        $environment    = $this->_emulate->startEnvironmentEmulation($storeId);
                        $helper         = $this->_objectManager->get("\Webkul\Mobikul\Helper\Searchsuggestion");
                        $query          = is_array($searchQuery) ? "" : trim($searchQuery);
                        $maxQueryLength = $this->_helper->getConfigData("catalog/search/max_query_length");
                        $query          = substr($searchQuery, 0, $maxQueryLength);
                        $tagArray       = [];
                        $productArray   = [];
                        if ($helper->displayTags()) {
                            $tagCollection = $this->_objectManager->get("\Magento\Search\Model\Query")
                                ->loadByQuery($query)
                                ->getSuggestCollection()
                                ->setPageSize($helper->getNumberOfTags());
                            foreach ($tagCollection as $item) {
                                $tagArray[] = [
                                    "term"           => $query,
                                    "title"          => $item->getQueryText(),
                                    "num_of_results" => $item->getNumResults()
                                ];
                            }
                        }
                        if ($helper->displayProducts()) {
                            $productCollection = $this->_objectManager->create("\Magento\Catalog\Model\ResourceModel\Product\Collection");
                            if ($categoryId > 0) {
                                $productCollection = $this->_objectManager->create("\Magento\Catalog\Model\Category")->load($categoryId)
                                    ->getProductCollection()
                                    ->addAttributeToSelect("*")
                                    ->addAttributeToFilter("status", ["in"=>$this->_productStatus->getVisibleStatusIds()])
                                    ->setVisibility($this->_productVisibility->getVisibleInSiteIds())
                                    ->addAttributeToFilter([["attribute"=>"name", "like"=>"%".$query."%"]]);
                            } else {
                                $productCollection->addAttributeToSelect("*")
                                    ->addAttributeToFilter("status", ["in"=>$this->_productStatus->getVisibleStatusIds()])
                                    ->setVisibility($this->_productVisibility->getVisibleInSiteIds());
                            }
                            // $productCollection->joinField("reviews_count", "review_entity_summary", "reviews_count", "entity_pk_value=entity_id", ["entity_type"=>1, "store_id"=>$storeId], "left")
                            // ->setOrder("reviews_count", "desc")
                            $productCollection->setPageSize($helper->getNumberOfProducts());
                            foreach ($productCollection as $item) {
                                $price       = number_format($item->getFinalPrice(), 2);
                                $isSalePrice = $helper->isOnSale($item);
                                $imgSrc      = $this->_objectManager->create("\Magento\Catalog\Helper\Image")->init($item, "product_page_image_large")->resize(144, 144)->getUrl();
                                if ($isSalePrice == true)
                                    $specialPrice = number_format($item->getSpecialPrice(), 2);
                                else
                                    $specialPrice = 0;
                                if ($item->getTypeId() == "grouped") {
                                    $minPrice = 0;
                                    if($item->getMinimalPrice() == "") {
                                        $associatedProducts = $item->getTypeInstance(true)->getAssociatedProducts($item);
                                        $minPrice = [];
                                        foreach ($associatedProducts as $associatedProduct) {
                                            if ($ogPrice = $associatedProduct->getPrice())
                                                $minPrice[] = $ogPrice;
                                        }
                                        $minPrice = min($minPrice);
                                    }
                                    else
                                        $minPrice = $item->getMinimalPrice();
                                        $price = $minPrice;
                                }
                                $productArray[] = [
                                    "title"        => $item->getName(),
                                    "image"        => $imgSrc,
                                    "productId"    => $item->getId(),
                                    "price"        => $price,
                                    "specialPrice" => $specialPrice,
                                    "term"         => $query
                                ];
                            }
                        }
                        $suggestData         = [$tagArray, $productArray];
                        $suggestProductArray = [];
                        if (count($suggestData[0]) != 0 || count($suggestData[1]) != 0) {
                            foreach ($suggestData[0] as $index => $item) {
                                $eachSuggestion = [];
                                $term    = html_entity_decode($item["term"]);
                                $tagName = html_entity_decode($item["title"]);
                                $title   = html_entity_decode($item["title"]);
                                $len     = strlen($term);
                                $str     = $helper->matchString($term, $tagName);
                                $tagName = $helper->getBoldName($tagName, $str, $term);
                                $eachSuggestion["label"] = $tagName;
                                $eachSuggestion["count"] = $item["num_of_results"];
                                $suggestProductArray["tags"][] = $eachSuggestion;
                            }
                            if (count($suggestData[1]) > 0) {
                                foreach ($suggestData[1] as $index => $item) {
                                    $eachSuggestion = [];
                                    $term           = html_entity_decode($item["term"]);
                                    $formattedPrice = strip_tags($this->_objectManager->get("\Magento\Checkout\Helper\Data")->formatPrice($item["price"]));
                                    $imgUrl         = html_entity_decode($item["image"]);
                                    $productName    = html_entity_decode($item["title"]);
                                    $specialPrice   = html_entity_decode($item["specialPrice"]);
                                    $title          = html_entity_decode($item["title"]);
                                    $str            = $helper->matchString($term, $productName);
                                    $productName    = $helper->getBoldName($productName, $str, $term);
                                    $eachSuggestion["price"]           = $formattedPrice;
                                    $eachSuggestion["thumbNail"]       = $imgUrl;
                                    $eachSuggestion["productId"]       = $item["productId"];
                                    $eachSuggestion["productName"]     = $productName;
                                    $eachSuggestion["specialPrice"]    = strip_tags($this->_objectManager->get("\Magento\Checkout\Helper\Data")->formatPrice(0));
                                    $eachSuggestion["hasSpecialPrice"] = false;
                                    if ($specialPrice > 0) {
                                        $eachSuggestion["hasSpecialPrice"] = true;
                                        $eachSuggestion["specialPrice"]    = strip_tags($this->_objectManager->get("\Magento\Checkout\Helper\Data")->formatPrice($specialPrice));
                                    }
                                    $suggestProductArray["products"][]     = $eachSuggestion;
                                }
                            }
                        }
                        $returnArray["suggestProductArray"]                = $suggestProductArray;
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

    }