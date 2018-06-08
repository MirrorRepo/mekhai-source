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

    class SearchResult extends AbstractCatalog   {

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
                        $searchQuery = $this->_helper->validate($wholeData, "searchQuery") ? $wholeData["searchQuery"] : "";
                        $filterData  = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($filterData);
                        $sortData    = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($sortData);
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
// setting currency /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $store       = $this->_objectManager->create("\Magento\Store\Model\Store");
                        $currency    = $this->_helper->validate($wholeData, "currency") ? $wholeData["currency"] : $store->getBaseCurrencyCode();
                        $store->setCurrentCurrencyCode($currency);
// checking is swatch allowed on colletion page /////////////////////////////////////////////////////////////////////////////////
                        $returnArray["showSwatchOnCollection"] = (bool)$this->_helper->getConfigData("catalog/frontend/show_swatches_in_product_list");
// searching product by query ///////////////////////////////////////////////////////////////////////////////////////////////////
                        $isFlatEnabled = $this->_objectManager->create("\Magento\Catalog\Model\ResourceModel\Product\Collection")->isEnabledFlat();
                        $this->getRequest()->setParam("q", $searchQuery);
                        $query = $this->_queryFactory->get();
                        $query->setStoreId($storeId);
                        if ($query->getId()){
                            $query->setPopularity($query->getPopularity()+1);
                        }
                        else{
                            $query->setQueryText($searchQuery)->setIsActive(1)->setIsProcessed(1)->setDisplayInTerms(1);
                            $query->setPopularity(1);
                        }
                        $query->prepare()->save();
                    // $collection = $this->_objectManager->create("\Magento\CatalogSearch\Model\Fulltext")->getCollection();
                    // $collection = $this->_objectManager->create("\Magento\CatalogSearch\Model\ResourceModel\Search\Collection")//->getCollection();
                    // $returnArray["table"] = $this->_objectManager->create("\Magento\Catalog\Model\ResourceModel\Product\Flat")->getAllTableColumns();
                        $collection = $this->_objectManager->create("\Magento\CatalogSearch\Model\ResourceModel\Search\Collection")
                                ->addAttributeToSelect("*")
                                ->setStoreId($storeId)
                                ->addMinimalPrice()
                                ->addFinalPrice()
                                ->addAttributeToFilter("status", ["in"=>$this->_productStatus->getVisibleStatusIds()])
                                ->setVisibility($this->_productVisibility->getVisibleInSiteIds());
                        if($isFlatEnabled){
                            // $collection->addSearchFilter($query->getQueryText());
                            // $queryCollection = $this->_objectManager->create("Magento\Search\Model\ResourceModel\Query\CollectionFactory")
                            //     ->create()
                            //     ->setStoreId($storeId)
                            // $collection->addSearchFilter($query->getQueryText());
                            // $collection->setQueryText($searchQuery)->prepare();
                            // $collection->setQueryFilter($query->getQueryText());
                            // $fullText = $this->_objectManager->create("Magento\CatalogSearch\Model\ResourceModel\Fulltext");
                            // $fullText->prepareResult($fullText, $searchQuery, $query);
                            $collection->addAttributeToFilter([
                                ["attribute"=>"short_description", "like"=>"%".$searchQuery."%"],
                                ["attribute"=>"name", "like"=>"%".$searchQuery."%"],
                                ["attribute"=>"description", "like"=>"%".$searchQuery."%"]
                            ]);
                        }
                        else{
                            $collection->addSearchFilter($query->getQueryText());
                        }
                        if($this->_helperCatalog->showOutOfStock() == 0)
                            $this->_objectManager->create("\Magento\CatalogInventory\Helper\Stock")->addInStockFilterToCollection($collection);
// Filtering product collection /////////////////////////////////////////////////////////////////////////////////////////////////
                        if(count($filterData) > 0) {
                            for($i=0; $i<count($filterData[0]); ++$i) {
                                if($filterData[0][$i] != "" && $filterData[1][$i] == "price") {
                                    $priceRange    = explode("-", $filterData[0][$i]);
                                    $to            = $priceRange[1];
                                    $from          = $priceRange[0];
                                    $currencyRate  = $collection->getCurrencyRate();
                                    $fromRange     = ($from - (.01 / 2)) / $currencyRate;
                                    $toRange       = ($to - (.01 / 2)) / $currencyRate;
                                    $select        = $collection->getSelect();
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
                                    $collection->setStoreId($storeId)->addCategoryFilter($categoryToFilter);
                                } else {
                                    $attribute = $this->_eavConfig->getAttribute("catalog_product", $filterData[1][$i]);
                                    $attributeModel = $this->_objectManager->create("\Magento\Catalog\Model\Layer\Filter\Attribute");
                                    $attributeModel->setAttributeModel($attribute);
                                    $filterAtr  = $this->_objectManager->create("\Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute");
                                    $connection = $filterAtr->getConnection();
                                    $tableAlias = $attribute->getAttributeCode()."_idx";
                                    $conditions = [
                                        "{$tableAlias}.entity_id = e.entity_id",
                                        $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
                                        $connection->quoteInto("{$tableAlias}.store_id = ?", $collection->getStoreId()),
                                        $connection->quoteInto("{$tableAlias}.value = ?", $filterData[0][$i]),
                                    ];
                                    $collection->getSelect()->join([$tableAlias=>$filterAtr->getMainTable()],implode(" AND ",$conditions),[]);
                                }
                            }
                        }
// Sorting product collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if(count($sortData) > 0) {
                            $sortBy = $sortData[0];
                            if($sortData[1] == 0)
                                $collection->setOrder($sortBy, "ASC");
                            else
                                $collection->setOrder($sortBy, "DESC");
                        }
// Applying pagination //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($pageNumber >= 1) {
                            $returnArray["totalCount"] = $collection->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $collection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
// Creating product collection //////////////////////////////////////////////////////////////////////////////////////////////////
                        $productList = [];
                        foreach($collection as $eachProduct) {
                            $eachProduct = $this->_productFactory->create()->load($eachProduct->getId());
                            if($eachProduct->isAvailable() || (bool)$this->_helper->getConfigData("cataloginventory/options/show_out_of_stock"))
                                $productList[] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["productList"] = $productList;
// Creating layered attribute collection ////////////////////////////////////////////////////////////////////////////////////////
                        $this->_objectManager->get("\Webkul\Mobikul\Model\Layer")->_customCollection = $collection;
                        $this->_objectManager->get("\Webkul\Mobikul\Model\ResourceModel\Layer\Filter\Price")->_customCollection = $collection;
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
                                    $attributeFilterModel = $this->_objectManager->create("\Magento\Catalog\Model\Layer\Filter\Attribute")->setAttributeModel($filter);
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