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

    class CustomCollection extends AbstractMobikul   {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["message"]      = "";
            $returnArray["authKey"]      = "";
            $returnArray["cartCount"]    = 0;
            $returnArray["totalCount"]   = 0;
            $returnArray["productList"]  = [];
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
                        $width             = $this->_helper->validate($wholeData, "width")          ? $wholeData["width"]          : 1000;
                        $storeId           = $this->_helper->validate($wholeData, "storeId")        ? $wholeData["storeId"]        : 1;
                        $quoteId           = $this->_helper->validate($wholeData, "quoteId")        ? $wholeData["quoteId"]        : 0;
                        $sortData          = $this->_helper->validate($wholeData, "sortData")       ? $wholeData["sortData"]       : "[]";
                        $customerId        = $this->_helper->validate($wholeData, "customerId")     ? $wholeData["customerId"]     : 0;
                        $pageNumber        = $this->_helper->validate($wholeData, "pageNumber")     ? $wholeData["pageNumber"]     : 1;
                        $filterData        = $this->_helper->validate($wholeData, "filterData")     ? $wholeData["filterData"]     : "[]";
                        $notificationId    = $this->_helper->validate($wholeData, "notificationId") ? $wholeData["notificationId"] : 0;
                        $jsonHelper        = $this->_objectManager->create("Magento\Framework\Json\Helper\Data");
                        $sortData          = $jsonHelper->jsonDecode($sortData);
                        $filterData        = $jsonHelper->jsonDecode($filterData);
                        $environment       = $this->_emulate->startEnvironmentEmulation($storeId);
                        $productCollection = $this->_objectManager->create("\Magento\Catalog\Model\ResourceModel\Product\Collection");
                        $notification      = $this->_objectManager->create("\Webkul\Mobikul\Model\Notification")->load($notificationId);
                        $customFilterData  = unserialize($notification->getFilterData());
                        if ($notification->getCollectionType() == "product_attribute") {
                            $productCollection->setStore($storeId)
                                ->addAttributeToSelect("*")
                                ->addAttributeToSelect("as_featured")
                                ->addAttributeToSelect("visibility")
                                ->addStoreFilter()
                                ->addAttributeToFilter("status", ["in"=>$this->_productStatus->getVisibleStatusIds()])
                                ->setVisibility($this->_productVisibility->getVisibleInSiteIds());
                            foreach ($customFilterData as $key => $filterValue) {
                                if ($key == "category_ids") {
                                    foreach (explode(",", $filterValue) as $value)
                                        $productCollection->addCategoryFilter($this->_objectManager->create("\Magento\Catalog\Model\Category")->load($value));
                                } else {
                                    $productCollection->addAttributeToSelect($key);
                                    $productCollection->addAttributeToFilter($key, array("in" => $filterValue));
                                }
                            }
                        } elseif ($notification->getCollectionType() == "product_ids") {
                            $productCollection->setStore($storeId)
                                ->addAttributeToSelect("*")
                                ->addAttributeToSelect("as_featured")
                                ->addAttributeToSelect("visibility")
                                ->addStoreFilter()
                                ->addAttributeToFilter("status", ["in"=>$this->_productStatus->getVisibleStatusIds()])
                                ->setVisibility($this->_productVisibility->getVisibleInSiteIds());
                            $productCollection->addAttributeToFilter("entity_id", array("in" => explode(",", $customFilterData)));
                        } elseif ($notification->getCollectionType() == "product_new") {
                            $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
                            $todayEndOfDayDate   = $this->_localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
                            $productCollection->setVisibility($visibleCatalogIds)
                                ->addMinimalPrice()
                                ->addFinalPrice()
                                ->addAttributeToSelect("*")
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
                                    ]);
                            $returnArray["totalCount"] = $customFilterData;
                            if ($pageNumber >= 1)
                                $productCollection->setPageSize($customFilterData)->setCurPage($pageNumber);
                        }
// Filtering product collection /////////////////////////////////////////////////////////////////////////////////////////////////
                        if(count($filterData) > 0) {
                            for($i=0; $i<count($filterData[0]); ++$i) {
                                if($filterData[0][$i] != "" && $filterData[1][$i] == "price") {
                                    $minPossiblePrice = .01;
                                    $currencyRate     = $productCollection->getCurrencyRate();
                                    $priceRange       = explode("-", $filterData[0][$i]);
                                    $from             = $priceRange[0];
                                    $to               = $priceRange[1];
                                    $fromRange        = ($from - ($minPossiblePrice / 2)) / $currencyRate;
                                    $toRange          = ($to - ($minPossiblePrice / 2)) / $currencyRate;
                                    $select           = $productCollection->getSelect();
                                    if($from !== "")
                                        $select->where("price_index.min_price".">=".$fromRange);
                                    if($to !== "")
                                        $select->where("price_index.min_price"."<".$toRange);
                                } elseif($filterData[0][$i] != "" && $filterData[1][$i] == "cat") {
                                    $categoryToFilter = $this->_objectManager->create("\Magento\Catalog\Model\Category")->load($filterData[0][$i]);
                                    $productCollection->setStoreId($storeId)->addCategoryFilter($categoryToFilter);
                                } else {
                                    $attribute  = $this->_eavConfig->getAttribute("catalog_product", $filterData[1][$i]);
                                    $this->_objectManager->create("\Magento\Catalog\Model\Layer\Filter\Attribute")->setAttributeModel($attribute);
                                    $filterAtr  = $this->_objectManager->create("\Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute");
                                    $connection = $filterAtr->getConnection();
                                    $tableAlias = $attribute->getAttributeCode()."_idx";
                                    $conditions = [
                                        "{$tableAlias}.entity_id = e.entity_id",
                                        $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
                                        $connection->quoteInto("{$tableAlias}.store_id = ?", $productCollection->getStoreId()),
                                        $connection->quoteInto("{$tableAlias}.value = ?", $filterData[0][$i]),
                                    ];
                                    $productCollection->getSelect()->join([$tableAlias=>$filterAtr->getMainTable()],implode(" AND ",$conditions),[]);
                                }
                            }
                        }
// Applying pagination //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($notification->getCollectionType() != "product_new" && $pageNumber >= 1) {
                            $returnArray["totalCount"] = $productCollection->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $productCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
// Sorting product collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if(count($sortData) > 0) {
                            $sortBy = $sortData[0];
                            if($sortData[1] == 0)
                                $productCollection->setOrder($sortBy, "ASC");
                            else
                                $productCollection->setOrder($sortBy, "DESC");
                        }
                        elseif($notification->getCollectionType() == "product_new"){
                            $productCollection->addAttributeToSort("news_from_date", "DESC");
                        }
                        foreach ($productCollection as $eachProduct) {
                            $eachProduct = $this->_productFactory->create()->load($eachProduct->getId());
                            if($eachProduct->isAvailable() || (bool)$this->_helper->getConfigData("cataloginventory/options/show_out_of_stock"))
                                $returnArray["productList"][] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
// Creating layered attribute collection ////////////////////////////////////////////////////////////////////////////////////////
                        $layeredData = [];
                        $filters = $this->_filterableAttributes->getList();
                        foreach ($filters as $filter) {
                            $doAttribute = true;
                            if (count($filterData) > 0) {
                                if (in_array($filter->getAttributeCode(), $filterData[1]))
                                    $doAttribute = false;
                            }
                            if ($doAttribute) {
                                $attributeFilterModel = $this->_objectManager->create("\Magento\Catalog\Model\Layer\Filter\Attribute")->setAttributeModel($filter);
                                if ($attributeFilterModel->getItemsCount()) {
                                    $each = array();
                                    $each["label"]   = $filter->getFrontendLabel();
                                    $each["code"]    = $filter->getAttributeCode();
                                    $each["options"] = $this->_helperCatalog->getAttributeFilter($attributeFilterModel, $filter);
                                    $layeredData[]   =  $each;
                                }
                            }
                        }
                        $returnArray["layeredData"] = $layeredData;
// Creating sort attribute collection ///////////////////////////////////////////////////////////////////////////////////////////
                        $sortingData       = [];
                        $toolbar           = $this->_toolbar;
                        foreach($toolbar->getAvailableOrders() as $key=>$order) {
                            $each          = [];
                            $each["code"]  = $key;
                            $each["label"] = $order;
                            $sortingData[] = $each;
                        }
                        $returnArray["sortingData"] = $sortingData;
                        if ($customerId != 0) {
                            $quote = $this->_objectManager->create("\Magento\Quote\Model\Quote")->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                // ->addFieldToFilter("store_id", $storeId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC")
                                ->getFirstItem();
                            $quote->collectTotals()->save();
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                        }
                        if ($quoteId != 0) {
                            $returnArray["cartCount"] = $this->_objectManager
                                ->create("\Magento\Quote\Model\Quote")
                                ->setStoreId($storeId)
                                ->load($quoteId)->getItemsQty() * 1;
                        }

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