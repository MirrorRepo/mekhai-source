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

    class SellerCollection extends AbstractMarketplace    {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["message"]      = "";
            $returnArray["success"]      = false;
            $returnArray["totalCount"]   = 0;
            $returnArray["productList"]  = [];
            $returnArray["categoryList"] = [];
            $returnArray["sortingData"]  = [];
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
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 0;
                        $sellerId    = $this->_helper->validate($wholeData, "sellerId")   ? $wholeData["sellerId"]   : 0;
                        $categoryId  = $this->_helper->validate($wholeData, "categoryId") ? $wholeData["categoryId"] : 0;
                        $pageNumber  = $this->_helper->validate($wholeData, "pageNumber") ? $wholeData["pageNumber"] : 1;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $sortData    = $this->_helper->validate($wholeData, "sortData")   ? $wholeData["sortData"]   : "[]";
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
// getting recently added products //////////////////////////////////////////////////////////////////////////////////////////////
                        $sortData    = json_decode($sortData, true);
                        $catalogProductWebsite = $this->_marketplaceProductResource->getTable("catalog_product_website");
                        $querydata = $this->_marketplaceProduct->getCollection()
                            ->addFieldToFilter("seller_id", $sellerId)
                            ->addFieldToFilter("status",  ["neq"=>2])
                            ->addFieldToSelect("mageproduct_id")
                            ->setOrder("mageproduct_id");
                        if ($categoryId == 0)
                            $categoryId = $this->_marketplaceHelper->getRootCategoryIdByStoreId($storeId);
                        $category       = $this->_category->setStoreId($storeId)->load($categoryId);
                        $productCollection = $this->_productModel->getCollection()
                            ->addAttributeToSelect("*")
                            ->addCategoryFilter($category)
                            ->addAttributeToFilter("entity_id", ["in"=>$querydata->getAllIds()])
                            ->addAttributeToFilter("visibility", ["in"=>[4]])
                            ->addAttributeToFilter("status", 1);
                        // Sorting product collection ///////////////////////////////////////////////////////////////////////////////////////////////////
                        if(count($sortData) > 0) {
                            $sortBy = $sortData[0];
                            if($sortData[1] == 0)
                                $productCollection->setOrder($sortBy, "ASC");
                            else
                                $productCollection->setOrder($sortBy, "DESC");
                        } else
                            $productCollection->setOrder("position", "ASC");
                        $productList = [];
                        if($pageNumber >= 1) {
                            $returnArray["totalCount"] = $productCollection->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $productCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        foreach ($productCollection as $eachProduct) {
                            $eachProduct   = $this->_productFactory->create()->load($eachProduct->getId());
                            $productList[] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["productList"] = $productList;
// Creating sort attribute collection ///////////////////////////////////////////////////////////////////////////////////////////
                        $sortingData       = [];
                        $toolbar           = $this->_objectManager->create('\Magento\Catalog\Block\Product\ProductList\Toolbar');
                        foreach($toolbar->getAvailableOrders() as $key=>$order) {
                            $each          = [];
                            $each["code"]  = $key;
                            $each["label"] = $order;
                            $sortingData[] = $each;
                        }
                        $returnArray["sortingData"] = $sortingData;
// getting category /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $collection = $this->_productModel->getCollection()
                            ->addAttributeToSelect("entity_id")
                            ->addAttributeToFilter("entity_id", ["in" => $querydata->getData()])
                            ->addAttributeToFilter("visibility", ["in" => [4]]);
                        $collection->addStoreFilter();
                        $marketplaceProduct = $this->_marketplaceProductResource->getTable("marketplace_product");
                        $collection->getSelect()->join(["mpp"=>$marketplaceProduct], "mpp.mageproduct_id=e.entity_id", ["mageproduct_id"=>"e.entity_id"]);
                        $proAttId = $this->_eavAttribute->getIdByCode("catalog_category", "name");
                        $catalogCategoryProduct = $this->_sellerCollection->getTable("catalog_category_product");
                        $catalogCategoryEntity = $this->_sellerCollection->getTable("catalog_category_entity");
                        $catalogCategoryEntityVarchar = $this->_sellerCollection->getTable("catalog_category_entity_varchar");
                        $collection->getSelect()
                            ->join(["ccp"=>$catalogCategoryProduct], "ccp.product_id=mpp.mageproduct_id", ["category_id"=>"category_id"])
                            ->join(["cce"=>$catalogCategoryEntity], "cce.entity_id=ccp.category_id", ["parent_id"=>"parent_id"])
                            ->where("cce.parent_id='".$categoryId."'")
                            ->columns("COUNT(*) AS countCategory")
                            ->group("category_id")
                            ->join(["ce1"=>$catalogCategoryEntityVarchar], "ce1.entity_id=ccp.category_id", ["catname"=>"value"])
                            ->where("ce1.attribute_id=".$proAttId." AND ce1.store_id=0")
                            ->order("catname");
                        $categoryList = [];
                        foreach($collection as $each){
                            $eachCategory          = [];
                            $eachCategory["id"]    = $each["category_id"];
                            $eachCategory["name"]  = $each["catname"];
                            $eachCategory["count"] = $each["countCategory"];
                            $categoryList[]        = $eachCategory;
                        }
                        $returnArray["categoryList"] = $categoryList;
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