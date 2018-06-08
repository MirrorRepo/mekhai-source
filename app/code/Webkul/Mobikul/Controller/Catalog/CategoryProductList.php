<?php
    /**
    * Webkul Software.
    *
    * @category Webkul
    *
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Controller\Catalog;

    class CategoryProductList extends AbstractCatalog       {

        public function execute()   {
            $returnArray                           = [];
            $returnArray["authKey"]                = "";
            $returnArray["success"]                = false;
            $returnArray["message"]                = "";
            $returnArray["cartCount"]              = 0;
            $returnArray["totalCount"]             = 0;
            $returnArray["bannerImage"]            = "";
            $returnArray["productList"]            = [];
            $returnArray["layeredData"]            = [];
            $returnArray["sortingData"]            = [];
            $returnArray["responseCode"]           = 0;
            $returnArray["showSwatchOnCollection"] = false;
            try {
                $wholeData           = $this->getRequest()->getPostValue();
                $this->_headers      = $this->getRequest()->getHeaders();
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
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 0;
                        $quoteId     = $this->_helper->validate($wholeData, "quoteId")    ? $wholeData["quoteId"]    : 0;
                        $mFactor     = $this->_helper->validate($wholeData, "mFactor")    ? $wholeData["mFactor"]    : 1;
                        $sortData    = $this->_helper->validate($wholeData, "sortData")   ? $wholeData["sortData"]   : "[]";
                        $pageNumber  = $this->_helper->validate($wholeData, "pageNumber") ? $wholeData["pageNumber"] : 1;
                        $categoryId  = $this->_helper->validate($wholeData, "categoryId") ? $wholeData["categoryId"] : 0;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $filterData  = $this->_helper->validate($wholeData, "filterData") ? $wholeData["filterData"] : "[]";
                        $sortData    = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($sortData);
                        $filterData  = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($filterData);
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
// setting currency /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $store       = $this->_objectManager->create("\Magento\Store\Model\Store");
                        $currency    = $this->_helper->validate($wholeData, "currency") ? $wholeData["currency"] : $store->getBaseCurrencyCode();
                        $store->setCurrentCurrencyCode($currency);
// checking is swatch allowed on colletion page /////////////////////////////////////////////////////////////////////////////////
                        $returnArray["showSwatchOnCollection"] = (bool)$this->_helper->getConfigData("catalog/frontend/show_swatches_in_product_list");
// creating product collection //////////////////////////////////////////////////////////////////////////////////////////////////
                        $category          = $this->_objectManager->create("\Magento\Catalog\Model\Category")->setStoreId($storeId)->load($categoryId);
                        $this->_coreRegistry->register("current_category", $category);
                        $categoryBlock     = $this->_listProduct;
                        $productCollection   = $this->_objectManager->create("\Magento\Catalog\Model\ResourceModel\Product\Collection")
                        ->addCategoryFilter($category);
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
                                    if($from !== "")
                                        $productCollection->addAttributeToFilter("price", ["gteq"=>$fromRange]);
                                    if($to !== "")
                                        $productCollection->addAttributeToFilter("price", ["lteq"=>$toRange]);
                                } elseif($filterData[0][$i] != "" && $filterData[1][$i] == "cat") {
                                    $categoryToFilter = $this->_objectManager->create("\Magento\Catalog\Model\Category")->load($filterData[0][$i]);
                                    $productCollection->setStoreId($storeId)->addCategoryFilter($categoryToFilter);
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
                        else
                            $productCollection->setOrder("position", "ASC");
// Applying pagination //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($pageNumber >= 1) {
                            $returnArray["totalCount"] = $productCollection->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $productCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
// Creating product collection //////////////////////////////////////////////////////////////////////////////////////////////////
                        $productList = [];
                        foreach($productCollection as $eachProduct) {
                            $eachProduct   = $this->_productFactory->create()->load($eachProduct->getId());
                            $productList[] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["productList"] = $productList;
// Creating filter attribute collection /////////////////////////////////////////////////////////////////////////////////////////
                        $layeredData = [];
                        // $this->_objectManager->get("\Webkul\Mobikul\Model\Layer")->_customCollection = $productCollection;
                        // $this->_objectManager->get("\Webkul\Mobikul\Model\ResourceModel\Layer\Filter\Price")->_customCollection = $productCollection;
                        $doCategory  = true;
                        if(count($filterData) > 0) {
                            if(in_array("cat", $filterData[1]))
                                $doCategory = false;
                        }
                        if($doCategory) {
                            $categoryFilterModel = $this->_objectManager->create("\Magento\Catalog\Model\Layer\Filter\Category");
                            if($categoryFilterModel->getItemsCount()) {
                                $each            = [];
                                $each["code"]    = "cat";
                                $each["label"]   = $categoryFilterModel->getName();
                                $each["options"] = $this->addCountToCategories($productCollection, $category->getChildrenCategories(), $storeId);
                                $layeredData[]   = $each;
                            }
                        }
                        $doPrice = true;
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
                                    $attributeFilterModel = $this->_objectManager
                                        ->create("\Magento\Catalog\Model\Layer\Filter\Attribute")
                                        ->setAttributeModel($filter);
                                    if ($attributeFilterModel->getItemsCount()) {
                                        $each            = [];
                                        $each["code"]    = $filter->getAttributeCode();
                                        $each["label"]   = $filter->getFrontendLabel();
                                        $each["options"] = $this->_helperCatalog->getAttributeFilter($attributeFilterModel, $filter);
                                        $layeredData[]   = $each;
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
// Cart Count ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($quoteId != 0) {
                            $returnArray["cartCount"] = $this->_objectManager
                                ->create("\Magento\Quote\Model\Quote")
                                ->setStoreId($storeId)
                                ->load($quoteId)->getItemsQty() * 1;
                        }
                        if ($customerId != 0) {
                            $quoteCollection = $this->_objectManager->create("\Magento\Quote\Model\Quote")->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                // ->addFieldToFilter("store_id", $storeId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC");
                            $quote = $quoteCollection->getFirstItem();
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
                        }
// Getting category banner image ////////////////////////////////////////////////////////////////////////////////////////////////
                        $categoryImageCollection = $this->_objectManager
                            ->create("\Webkul\Mobikul\Model\CategoryimagesFactory")
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter("category_id", $categoryId);
                        $bannerWidth  = $width * $mFactor;
                        $bannerHeight = ($width/2)*$mFactor;
                        foreach($categoryImageCollection as $categoryImage) {
                            if($categoryImage->getBanner() != "") {
                                $basePath = $this->_baseDir.DS."mobikul".DS."categoryimages".DS."banner".DS.$categoryImage->getBanner();
                                $newUrl = "";
                                if (is_file($basePath)) {
                                    $newPath = $this->_baseDir.DS."mobikul".DS."categoryimages".DS.$bannerWidth."x".$bannerHeight.DS.$categoryImage->getBanner();
                                    $this->_helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $bannerHeight);
                                    $newUrl = $this->_helper->getUrl("media")."mobikul".DS."categoryimages".DS.$bannerWidth."x".$bannerHeight.DS.$categoryImage->getBanner();
                                }
                                $returnArray["bannerImage"] = $newUrl;
                            }
                        }
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
                $this->_helper->printLog($returnArray);
                $this->_helper->log($returnArray, "logResponse", $wholeData);
                return $this->getJsonResponse($returnArray);
            }
        }

        private function getAggregator()    {
            return $this->_objectManager->create("\Magento\Framework\App\CacheInterface");
        }

        public function addCountToCategories($productCollection, $categoryCollection, $storeId){
            $isAnchor    = [];
            $isNotAnchor = [];
            foreach ($categoryCollection as $category) {
                if ($category->getIsAnchor())
                    $isAnchor[] = $category->getId();
                else
                    $isNotAnchor[] = $category->getId();
            }
            $productCounts = [];
            if ($isAnchor || $isNotAnchor) {
                $select = $this->getProductCountSelect($productCollection, $storeId);
                $this->_objectManager->get("\Magento\Framework\Event\ManagerInterface")->dispatch("catalog_product_collection_before_add_count_to_categories", ["collection" => $productCollection]);
                if ($isAnchor) {
                    $anchorStmt = clone $select;
                    $anchorStmt->limit();
                    $anchorStmt->where("count_table.category_id IN (?)", $isAnchor);
                    $productCounts += $productCollection->getConnection()->fetchPairs($anchorStmt);
                    $anchorStmt = null;
                }
                if ($isNotAnchor) {
                    $notAnchorStmt = clone $select;
                    $notAnchorStmt->limit();
                    $notAnchorStmt->where("count_table.category_id IN (?)", $isNotAnchor);
                    $notAnchorStmt->where("count_table.is_parent = 1");
                    $productCounts += $productCollection->getConnection()->fetchPairs($notAnchorStmt);
                    $notAnchorStmt = null;
                }
                $select = null;
                $this->_productCountSelect = null;
            }
            $data = [];
            foreach ($categoryCollection as $category) {
                $_count = 0;
                if (isset($productCounts[$category->getId()])) {
                    $_count = $productCounts[$category->getId()];
                }
                if($category->getIsActive() && $_count > 0) {
                    $data[] = [
                        "label" => html_entity_decode($this->_helperCatalog->stripTags($category->getName())),
                        "id"    => $category->getId(),
                        "count" => $_count
                    ];
                }
            }
            return $data;
        }

        public function getProductCountSelect($productCollection, $storeId){
            $this->_productCountSelect = clone $productCollection->getSelect();
            $this->_productCountSelect->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->reset(\Magento\Framework\DB\Select::GROUP)
                ->reset(\Magento\Framework\DB\Select::ORDER)
                ->distinct(false)
                ->join(["count_table" => $productCollection->getTable("catalog_category_product_index")],
                "count_table.product_id = e.entity_id", ["count_table.category_id", "product_count" => new \Zend_Db_Expr("COUNT(DISTINCT count_table.product_id)")])
                ->where("count_table.store_id = ?", $storeId)
                ->group("count_table.category_id");
            return $this->_productCountSelect;
        }

    }