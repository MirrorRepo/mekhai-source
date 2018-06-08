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

    class Wishlist extends AbstractCustomer  {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["wishList"]     = [];
            $returnArray["totalCount"]   = 0;
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
                        $wishlist    = $this->_objectManager->create("\Magento\Wishlist\Model\Wishlist")->loadByCustomerId($customerId, true);
                        $wishListCollection = $wishlist->getItemCollection();
// Applying pagination //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($pageNumber >= 1) {
                            $returnArray["totalCount"] = $wishListCollection->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $wishListCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
// Creating Wish List ///////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $wishList = [];
                        foreach ($wishListCollection as $item) {
                            $eachWishData = [];
                            $eachWishData["id"]          = $item->getId();
                            $product                     = $this->_objectManager->create("\Magento\Catalog\Model\Product")->load($item->getProductId());
                            $eachWishData["name"]        = $product->getName();
                            $eachWishData["description"] = $item->getDescription();
                            $eachWishData["sku"]         = $product->getSku();
                            $eachWishData["productId"]   = $product->getId();
                            $eachWishData["qty"]         = $item->getQty() * 1;
                            $eachWishData["price"]       = $this->_helperCatalog->stripTags($this->_objectManager->create("Magento\Framework\Pricing\Helper\Data")->currency($product->getFinalPrice()));
                            $eachWishData["thumbNail"]   = $this->_helperCatalog->getImageUrl($product, $width/3);
                            $options = $this->_objectManager->create("\Magento\Catalog\Helper\Product\Configuration")->getOptions($item);
                            $eachWishData["options"]     = [];
                            if (count($options) > 0){
                                foreach ($options as $option){
                                    $eachOption                = [];
                                    $eachOption["label"]       = html_entity_decode($option["label"]);
                                    if (is_array($option["value"]))
                                        $eachOption["value"]   = $option["value"]; 
                                    else
                                        $eachOption["value"][] = $option["value"]; 
                                    $eachWishData["options"][] = $eachOption;
                                }
                            }
                            $reviews = $this->_objectManager->create("\Magento\Review\Model\ResourceModel\Review\Collection")
                                    ->addStoreFilter($storeId)
                                    ->addEntityFilter("product", $product->getId())
                                    ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
                                    ->setDateOrder()
                                    ->addRateVotes();
                            $ratings = [];
                            if (count($reviews) > 0) {
                                foreach ($reviews->getItems() as $review) {
                                    foreach ($review->getRatingVotes() as $vote)
                                        $ratings[] = $vote->getPercent();
                                }
                            }
                            if (count($ratings) > 0)
                                $rating = number_format((5 * (array_sum($ratings) / count($ratings))) / 100, 2, ".", "");
                            else
                                $rating = 0;
                            $eachWishData["rating"] = $rating;
                            $wishList[] = $eachWishData;
                        }
                        $returnArray["wishList"] = $wishList;
                        $returnArray["success"]  = true;
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