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

    class AdvancedSearchResult extends AbstractCatalog  {

        public function execute()   {
            $returnArray                           = [];
            $returnArray["authKey"]                = "";
            $returnArray["success"]                = false;
            $returnArray["message"]                = "";
            $returnArray["totalCount"]             = 0;
            $returnArray["productList"]            = [];
            $returnArray["sortingData"]            = [];
            $returnArray["layeredData"]            = [];
            $returnArray["responseCode"]           = 0;
            $returnArray["criteriaData"]           = [];
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
                        $width       = $this->_helper->validate($wholeData, "width")       ? $wholeData["width"]       : 1000;
                        $storeId     = $this->_helper->validate($wholeData, "storeId")     ? $wholeData["storeId"]     : 0;
                        $sortData    = $this->_helper->validate($wholeData, "sortData")    ? $wholeData["sortData"]    : "[]";
                        $pageNumber  = $this->_helper->validate($wholeData, "pageNumber")  ? $wholeData["pageNumber"]  : 1;
                        $customerId  = $this->_helper->validate($wholeData, "customerId")  ? $wholeData["customerId"]  : 0;
                        $filterData  = $this->_helper->validate($wholeData, "filterData")  ? $wholeData["filterData"]  : "[]";
                        $queryString = $this->_helper->validate($wholeData, "queryString") ? $wholeData["queryString"] : "[]";
                        $sortData    = $this->_jsonHelper->jsonDecode($sortData);
                        $filterData  = $this->_jsonHelper->jsonDecode($filterData);
                        $queryArray  = $this->_jsonHelper->jsonDecode($queryString);
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
// setting currency /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $currency    = $this->_helper->validate($wholeData, "currency") ? $wholeData["currency"] : $this->_store->getBaseCurrencyCode();
                        $this->_store->setCurrentCurrencyCode($currency);
// checking is swatch allowed on colletion page /////////////////////////////////////////////////////////////////////////////////
                        $returnArray["showSwatchOnCollection"] = (bool)$this->_helper->getConfigData("catalog/frontend/show_swatches_in_product_list");
// Getting Product Collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        $queryArray        = $this->_helperCatalog->getQueryArray($queryArray);
                        $advancedSearch    = $this->_advancedCatalogSearch->addFilters($queryArray);
                        $productCollection = $advancedSearch->getProductCollection();
                        if($this->_helperCatalog->showOutOfStock() == 0)
                            $this->_stockFilter->addInStockFilterToCollection($productCollection);
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
                                    $isFlatEnabled = $this->_productCollection->isEnabledFlat();
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
                                    $categoryToFilter = $this->_category->create()->load($filterData[0][$i]);
                                    $productCollection->setStoreId($storeId)->addCategoryFilter($categoryToFilter);
                                } else {
                                    $attribute = $this->_eavConfig->getAttribute("catalog_product", $filterData[1][$i]);
                                    $this->_layerAttribute->create()->setAttributeModel($attribute);
                                    $connection = $this->_layerFilterAttributeResource->create()->getConnection();
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
// Applying pagination //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($pageNumber >= 1) {
                            $returnArray["totalCount"] = $productCollection->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $productCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
// Creating product collection //////////////////////////////////////////////////////////////////////////////////////////////////
                        $productList = [];
                        foreach($productCollection as $eachProduct) {
                            $eachProduct = $this->_productFactory->create()->load($eachProduct->getId());
                            if($eachProduct->isAvailable() || (bool)$this->_helper->getConfigData("cataloginventory/options/show_out_of_stock"))
                                $productList[] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["productList"] = $productList;
// Creating layered attribute collection ////////////////////////////////////////////////////////////////////////////////////////
                        $this->_mobikulLayer->_customCollection = $productCollection;
                        $this->_mobikulLayerPrice->_customCollection = $productCollection;
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
                                    $priceFilterModel = $this->_filterPriceDataprovider->create();
                                    if ($priceFilterModel) {
                                        $each = [];
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
                                    $attributeFilterModel = $this->_layerAttribute->create()->setAttributeModel($filter);
                                    if ($attributeFilterModel->getItemsCount()) {
                                        $each = array();
                                        $each["code"]    = $filter->getAttributeCode();
                                        $each["label"]   = $filter->getFrontendLabel();
                                        $each["options"] = $this->_helperCatalog->getAttributeFilter($attributeFilterModel, $filter);
                                        $layeredData[]   =  $each;
                                    }
                                }
                            }
                        }
                        $returnArray["layeredData"] = $layeredData;
// Getting Sorating Collection //////////////////////////////////////////////////////////////////////////////////////////////////
                        $toolbar           = $this->_toolbar;
                        $availableOrders   = $toolbar->getAvailableOrders();
                        unset($availableOrders["position"]);
                        $availableOrders   = array_merge(["relevance"=>"Relevance"], $availableOrders);
                        foreach ($availableOrders as $key=>$order) {
                            $each          = [];
                            $each["code"]  = $key;
                            $each["label"] = $order;
                            $sortingData[] = $each;
                        }
                        $returnArray["sortingData"] = $sortingData;
// Getting Criteria /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $criteriaData = [];
                        $searchCriterias = $this->getSearchCriterias($advancedSearch->getSearchCriterias());
                        foreach(["left", "right"] as $side) {
                            if ($searchCriterias[$side]) {
                                foreach ($searchCriterias[$side] as $criteria) {
                                    $criteriaData[] = $this->_helperCatalog->stripTags($criteria["name"])." : ".$this->_helperCatalog->stripTags($criteria["value"]);
                                }
                            }
                        }
                        $returnArray["criteriaData"] = $criteriaData;
                        $this->_emulate->stopEnvironmentEmulation($environment);
                        $returnArray["success"] = true;
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

        public function getSearchCriterias($searchCriterias)    {
            $middle = ceil(count($searchCriterias) / 2);
            $left   = array_slice($searchCriterias, 0, $middle);
            $right  = array_slice($searchCriterias, $middle);
            return ["left"=>$left, "right"=>$right];
        }

    }