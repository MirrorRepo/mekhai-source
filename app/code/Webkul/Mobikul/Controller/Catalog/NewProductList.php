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

    namespace Webkul\Mobikul\Controller\Catalog;

    class NewProductList extends AbstractCatalog    {

        public function execute()   {
            $returnArray                           = [];
            $returnArray["authKey"]                = "";
            $returnArray["message"]                = "";
            $returnArray["cartCount"]              = 0;
            $returnArray["totalCount"]             = 0;
            $returnArray["productList"]            = [];
            $returnArray["layeredData"]            = [];
            $returnArray["sortingData"]            = [];
            $returnArray["responseCode"]           = 0;
            $returnArray["showSwatchOnCollection"] = false;
            try {
                $wholeData            = $this->getRequest()->getPostValue();
                $this->_headers       = $this->getRequest()->getHeaders();
                $this->_helper->log(__CLASS__, "logClass", $wholeData);
                $this->_helper->log($wholeData, "logParams", $wholeData);
                $this->_helper->log($this->_headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $authKey         = $this->getRequest()->getHeader("authKey");
                    $apiKey          = $this->getRequest()->getHeader("apiKey");
                    $apiPassword     = $this->getRequest()->getHeader("apiPassword");
                    $authData        = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $width       = $this->_helper->validate($wholeData, "width")      ? $wholeData["width"]      : 1000;
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $quoteId     = $this->_helper->validate($wholeData, "quoteId")    ? $wholeData["quoteId"]    : 0;
                        $sortData    = $this->_helper->validate($wholeData, "sortData")   ? $wholeData["sortData"]   : "[]";
                        $filterData  = $this->_helper->validate($wholeData, "filterData") ? $wholeData["filterData"] : "[]";
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $pageNumber  = $this->_helper->validate($wholeData, "pageNumber") ? $wholeData["pageNumber"] : 1;
                        $jsonHelper  = $this->_objectManager->create("Magento\Framework\Json\Helper\Data");
                        $sortData    = $jsonHelper->jsonDecode($sortData);
                        $filterData  = $jsonHelper->jsonDecode($filterData);
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
// setting currency /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $store       = $this->_objectManager->create("\Magento\Store\Model\Store");
                        $currency    = $this->_helper->validate($wholeData, "currency") ? $wholeData["currency"] : $store->getBaseCurrencyCode();
                        $store->setCurrentCurrencyCode($currency);
// checking is swatch allowed on colletion page /////////////////////////////////////////////////////////////////////////////////
                        $returnArray["showSwatchOnCollection"] = (bool)$this->_helper->getConfigData("catalog/frontend/show_swatches_in_product_list");
// Featured Products ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $visibleCatalogIds   = $this->_objectManager->get("\Magento\Catalog\Model\Product\Visibility")->getVisibleInCatalogIds();
                        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
                        $todayEndOfDayDate   = $this->_localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
                        $productCollection   = $this->_objectManager->create("\Magento\Catalog\Model\ResourceModel\Product\Collection")
                            ->setVisibility($visibleCatalogIds)
                            ->addMinimalPrice()
                            ->addFinalPrice()
                            ->addAttributeToSelect("*")
                            ->addStoreFilter()
                            ->addAttributeToFilter("status", ["in"=>$this->_productStatus->getVisibleStatusIds()])
                            ->setVisibility($this->_productVisibility->getVisibleInSiteIds())
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
                        if($this->_helperCatalog->showOutOfStock() == 0)
                            $this->_objectManager->create("\Magento\CatalogInventory\Helper\Stock")->addInStockFilterToCollection($productCollection);
// Filtering product collection /////////////////////////////////////////////////////////////////////////////////////////////////
                        if(count($filterData) > 0) {
                            for($i=0; $i<count($filterData[0]); ++$i) {
                                if($filterData[0][$i] != "" && $filterData[1][$i] == "price") {
                                    $priceRange    = explode("-", $filterData[0][$i]);
                                    $to            = $priceRange[1];
                                    $from          = $priceRange[0];
                                    $currencyRate  = $productCollection->getCurrencyRate();
                                    $fromRange     = ($from - (.01 / 2)) / $currencyRate;
                                    $toRange       = ($to - (.01 / 2)) / $currencyRate;
                                    $select        = $productCollection->getSelect();
                                    $isFlatEnabled = $this->_objectManager->create("\Magento\Catalog\Model\ResourceModel\Product\Collection")->isEnabledFlat();
                                    if($isFlatEnabled){
                                        if($from !== "")
                                            $select->where("price_index.price".">=".$fromRange);
                                        if($to !== "")
                                            $select->where("price_index.price"."<".$toRange);
                                    }
                                    else{
                                        if($from !== "")
                                            $select->where("price_index.min_price".">=".$fromRange);
                                        if($to !== "")
                                            $select->where("price_index.min_price"."<".$toRange);
                                    }
                                } elseif($filterData[0][$i] != "" && $filterData[1][$i] == "cat") {
                                    $categoryToFilter = $this->_objectManager->create("\Magento\Catalog\Model\Category")->load($filterData[0][$i]);
                                    $productCollection->setStoreId($storeId)->addCategoryFilter($categoryToFilter);
                                } else {
                                    $attribute      = $this->_eavConfig->getAttribute("catalog_product", $filterData[1][$i]);
                                    $attributeModel = $this->_objectManager->create("\Magento\Catalog\Model\Layer\Filter\Attribute");
                                    $attributeModel->setAttributeModel($attribute);
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
// Sorting product collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if(count($sortData) > 0) {
                            $sortBy = $sortData[0];
                            if($sortData[1] == 0)
                                $productCollection->setOrder($sortBy, "ASC");
                            else
                                $productCollection->setOrder($sortBy, "DESC");
                        }
                        else{
                            $productCollection->addAttributeToSort("news_from_date", "DESC");
                        }
// Applying pagination //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($pageNumber >= 1) {
                            $returnArray["totalCount"] = $productCollection->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $productCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
// Creating product collection //////////////////////////////////////////////////////////////////////////////////////////////////
                        $productList = [];
                        foreach ($productCollection as $eachProduct) {
                            $eachProduct   = $this->_productFactory->create()->load($eachProduct->getId());
                            $productList[] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["productList"] = $productList;
// Creating layered attribute collection ////////////////////////////////////////////////////////////////////////////////////////
                        $this->_objectManager->get("\Webkul\Mobikul\Model\Layer")->_customCollection = $productCollection;
                        $this->_objectManager->get("\Webkul\Mobikul\Model\ResourceModel\Layer\Filter\Price")->_customCollection = $productCollection;
                        $doPrice     = true;
                        $layeredData = [];
                        if(count($filterData) > 0) {
                            if(in_array("price", $filterData[1]))
                                $doPrice = false;
                        }
                        $filters = $this->_filterableAttributes->getList();
                        foreach ($filters as $filter) {
                            if ($filter->getFrontendInput() == "price") {
                                if ($doPrice) {
                                    $priceFilterModel = $this->_objectManager->create("\Magento\Catalog\Model\Layer\Filter\DataProvider\Price");
                                    if ($priceFilterModel) {
                                        $each            = [];
                                        $each["code"]    = $filter->getAttributeCode();
                                        $each["label"]   = $filter->getFrontendLabel();
                                        $each["options"] = $this->_helperCatalog->getPriceFilter($priceFilterModel, $storeId);
                                        $layeredData[]   = $each;
                                    }
                                }
                            } else {
                                $doAttribute = true;
                                if (count($filterData) > 0) {
                                    if (in_array($filter->getAttributeCode(), $filterData[1]))
                                        $doAttribute = false;
                                }
                                if ($doAttribute) {
                                    $attributeFilterModel = $this->_objectManager->create("\Magento\Catalog\Model\Layer\Filter\Attribute")->setAttributeModel($filter);
                                    if ($attributeFilterModel->getItemsCount()) {
                                        $each            = [];
                                        $each["code"]    = $filter->getAttributeCode();
                                        $each["label"]   = $filter->getFrontendLabel();
                                        $each["options"] = $this->_helperCatalog->getAttributeFilter($attributeFilterModel, $filter);
                                        $layeredData[]   =  $each;
                                    }
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
                        $this->_emulate->stopEnvironmentEmulation($environment);
                        $returnArray["success"] = true;
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    } else {
                        $returnArray["message"]      = $authData["message"];
                        $returnArray["responseCode"] = $authData["responseCode"];
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