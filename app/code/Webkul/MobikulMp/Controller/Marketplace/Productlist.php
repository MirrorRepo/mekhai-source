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

    class Productlist extends AbstractMarketplace    {

        public function execute()   {
            $returnArray                       = [];
            $returnArray["authKey"]            = "";
            $returnArray["success"]            = false;
            $returnArray["message"]            = "";
            $returnArray["totalCount"]         = 0;
            $returnArray["productList"]        = [];
            $returnArray["enabledStatusText"]  = "";
            $returnArray["disabledStatusText"] = "";
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
                        $toDate        = $this->_helper->validate($wholeData, "toDate")        ? $wholeData["toDate"]        : "";
                        $storeId       = $this->_helper->validate($wholeData, "storeId")       ? $wholeData["storeId"]       : 0;
                        $fromDate      = $this->_helper->validate($wholeData, "fromDate")      ? $wholeData["fromDate"]      : "";
                        $pageNumber    = $this->_helper->validate($wholeData, "pageNumber")    ? $wholeData["pageNumber"]    : 1;
                        $customerId    = $this->_helper->validate($wholeData, "customerId")    ? $wholeData["customerId"]    : 0;
                        $productName   = $this->_helper->validate($wholeData, "productName")   ? $wholeData["productName"]   : "";
                        $productStatus = $this->_helper->validate($wholeData, "productStatus") ? $wholeData["productStatus"] : 0;
                        $environment   = $this->_emulate->startEnvironmentEmulation($storeId);
                        $to   = null;
                        $from = null;
                        if ($toDate) {
                            $todate = date_create($toDate);
                            $to = date_format($todate, "Y-m-d 23:59:59");
                        }
                        if (!$to) {
                            $to = date("Y-m-d 23:59:59");
                        }
                        if ($fromDate) {
                            $fromdate = date_create($fromDate);
                            $from = date_format($fromdate, "Y-m-d H:i:s");
                        }
                        $proAttId                    = $this->_eavAttribute->getIdByCode("catalog_product", "name");
                        $proStatusAttId              = $this->_eavAttribute->getIdByCode("catalog_product", "status");
                        $catalogProductEntity        = $this->_marketplaceProductResource->getTable("catalog_product_entity");
                        $catalogProductEntityInt     = $this->_marketplaceProductResource->getTable("catalog_product_entity_int");
                        $catalogProductEntityVarchar = $this->_marketplaceProductResource->getTable("catalog_product_entity_varchar");
                        $storeCollection             = $this->_marketplaceProduct
                            ->getCollection()
                            ->addFieldToFilter("seller_id", $customerId)
                            ->addFieldToSelect("mageproduct_id");
                        $storeCollection->getSelect()->join($catalogProductEntityVarchar." as cpev","main_table.mageproduct_id=cpev.entity_id")
                            ->where("cpev.store_id=".$storeId." AND cpev.value like '%".$productName."%' AND cpev.attribute_id=".$proAttId);
                        $storeCollection->getSelect()->join($catalogProductEntityInt.' as cpei','main_table.mageproduct_id = cpei.entity_id')
                            ->where("cpei.store_id=".$storeId." AND cpei.attribute_id = ".$proStatusAttId);
                        if ($productStatus != 0) {
                            $storeCollection->getSelect()->where("cpei.value=".$productStatus);
                        }
                        $storeCollection->getSelect()->join($catalogProductEntity." as cpe","main_table.mageproduct_id=cpe.entity_id");
                        if ($from && $to) {
                            $storeCollection->getSelect()->where("cpe.created_at BETWEEN '".$from."' AND '".$to."'");
                        }
                        $storeCollection->getSelect()->group("mageproduct_id");
                        $storeProductIDs      = $storeCollection->getAllIds();
                        $adminStoreCollection = $this->_marketplaceProduct
                            ->getCollection()
                            ->addFieldToFilter("seller_id", $customerId)
                            ->addFieldToSelect("mageproduct_id");
                        $adminStoreCollection->getSelect()->join($catalogProductEntityVarchar." as cpev","main_table.mageproduct_id=cpev.entity_id")
                            ->where("cpev.store_id=0 AND cpev.value like '%".$productName."%' AND cpev.attribute_id=".$proAttId);
                        $adminStoreCollection->getSelect()->join($catalogProductEntityInt.' as cpei','main_table.mageproduct_id = cpei.entity_id')
                            ->where("cpei.store_id=0 AND cpei.attribute_id=".$proStatusAttId);
                        if ($productStatus != 0) {
                            $adminStoreCollection->getSelect()->where("cpei.value=".$productStatus);
                        }
                        $adminStoreCollection->getSelect()->join($catalogProductEntity." as cpe","main_table.mageproduct_id=cpe.entity_id");
                        if ($from && $to) {
                            $adminStoreCollection->getSelect()->where("cpe.created_at BETWEEN '".$from."' AND '".$to."'");
                        }
                        $adminStoreCollection->getSelect()->group("mageproduct_id");
                        $adminProductIDs = $adminStoreCollection->getAllIds();
                        $productIDs      = array_merge($storeProductIDs, $adminProductIDs);
                        $collection      = $this->_marketplaceProduct
                            ->getCollection()
                            ->addFieldToFilter("seller_id", $customerId)
                            ->addFieldToFilter("mageproduct_id", ["in"=>$productIDs])
                            ->setOrder("mageproduct_id");
                        $enabledStatusText  = __("Enabled");
                        $disabledStatusText = __("Disabled");
                        if ($this->_marketplaceHelper->getIsProductApproval() || $this->_marketplaceHelper->getIsProductEditApproval()) {
                            $enabledStatusText  = __("Approved");
                            $disabledStatusText = __("Pending");
                        }
                        $returnArray["enabledStatusText"]  = $enabledStatusText;
                        $returnArray["disabledStatusText"] = $disabledStatusText;
                        if($pageNumber >= 1) {
                            $returnArray["totalCount"] = $collection->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $collection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        $productList = [];
                        foreach($collection as $products){
                            $eachProduct = [];
                            $product     = $this->getProductData($products->getMageproductId());
                            $eachProduct["productId"] = $products->getMageproductId();
                            $eachProduct["image"] = $this->_imageHelper->init($product, "product_page_image_small")->setImageFile($product->getImage())->getUrl();
                            $eachProduct["openable"] = false;
                            if($product->getStatus() == 1 && $product->getVisibility() != 1)
                                $eachProduct["openable"] = true;
                            $eachProduct["name"] = $product->getName();
                            $eachProduct["productPrice"] = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice(0));
                            if($product->getPrice()*1){
                                $eachProduct["productPrice"] = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($product->getPrice()));
                            }
                            $eachProduct["productType"] = $product->getTypeId();
                            if($product->getStatus() == 2 && ($this->_marketplaceHelper->getIsProductApproval() || $this->_marketplaceHelper->getIsProductEditApproval())) {
                                $eachProduct["status"]       = $disabledStatusText;
                                $eachProduct["qtySold"]      = __("Pending");
                                $eachProduct["qtyPending"]   = __("Pending");
                                $eachProduct["qtyConfirmed"] = __("Pending");
                                $eachProduct["earnedAmount"] = __("Pending");
                            }
                            else{
                                if ($product->getStatus() == 2)
                                    $eachProduct["status"]   = $disabledStatusText;
                                else
                                    $eachProduct["status"]   = $enabledStatusText;
                                $salesdetail                 = $this->getSalesdetail($products->getMageproductId());
                                $eachProduct["qtySold"]      = $salesdetail["quantitysold"];
                                $eachProduct["qtyPending"]   = $salesdetail["quantitysoldpending"];
                                $eachProduct["qtyConfirmed"] = $salesdetail["quantitysoldconfirmed"];
                                $eachProduct["earnedAmount"] = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($salesdetail["amountearned"]));
                            }
                            $productList[] = $eachProduct;
                        }
                        $returnArray["productList"] = $productList;
                        $returnArray["success"]     = true;
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