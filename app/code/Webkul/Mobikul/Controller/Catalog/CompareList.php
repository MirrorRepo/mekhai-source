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

    class CompareList extends AbstractCatalog    {

        public function execute()   {
            $returnArray                           = [];
            $returnArray["authKey"]                = "";
            $returnArray["message"]                = "";
            $returnArray["productList"]            = [];
            $returnArray["responseCode"]           = 0;
            $returnArray["attributeValueList"]     = [];
            $returnArray["showSwatchOnCollection"] = false;
            try {
                $wholeData            = $this->getRequest()->getPostValue();
                $this->_headers       = $this->getRequest()->getHeaders();
                $this->_helper->log(__CLASS__, "logClass", $wholeData);
                $this->_helper->log($wholeData, "logParams", $wholeData);
                $this->_helper->log($this->_headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $authKey          = $this->getRequest()->getHeader("authKey");
                    $apiKey           = $this->getRequest()->getHeader("apiKey");
                    $apiPassword      = $this->getRequest()->getHeader("apiPassword");
                    $authData         = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $width        = $this->_helper->validate($wholeData, "width")      ? $wholeData["width"]      : 1000;
                        $storeId      = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $customerId   = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment  = $this->_emulate->startEnvironmentEmulation($storeId);
// setting currency /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $baseCurrency = $this->_store->getBaseCurrencyCode();
                        $currency     = $this->_helper->validate($wholeData, "currency") ? $wholeData["currency"] : $baseCurrency;
                        $this->_store->setCurrentCurrencyCode($currency);
// checking is swatch allowed on colletion page /////////////////////////////////////////////////////////////////////////////////
                        $returnArray["showSwatchOnCollection"] = (bool)$this->_helper->getConfigData("catalog/frontend/show_swatches_in_product_list");
// getting compare list data ////////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($this->_items === null) {
                            $this->_items = $this->_compareItemCollectionFactory->create();
                            $this->_items->useProductItem(true)->setStoreId($storeId);
                            if ($customerId != 0)
                                $this->_items->setCustomerId($customerId);
                            else
                                $this->_items->setVisitorId($this->_customerVisitor->getId());
                            $attributes = $this->_catalogConfig->getProductAttributes();
                            $this->_items
                                ->addAttributeToSelect($attributes)
                                ->loadComparableAttributes()
                                ->setVisibility($this->_productVisibility->getVisibleInSiteIds());
                        }
// getting product list /////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $productList = [];
                        foreach ($this->_items as $eachProduct) {
                            $eachProduct = $this->_productFactory->create()->load($eachProduct->getId());
                            $productList[] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                        }
                        $returnArray["productList"] = $productList;
// getting attribute value list /////////////////////////////////////////////////////////////////////////////////////////////////
                        $block = $this->_compareListBlock;
                        $attributeValueList = [];
                        foreach ($this->_items->getComparableAttributes() as $attribute){
                            $eachRow = [];
                            $eachRow["attributeName"] = $this->_escaper->escapeHtml($attribute->getStoreLabel() ? $attribute->getStoreLabel() : __($attribute->getFrontendLabel()));
                            foreach ($this->_items as $item) {
                                $eachItem = "";
                                switch ($attribute->getAttributeCode()) {
                                    case "price":
                                        $eachItem = $this->_helperCatalog->stripTags($this->_pricingHelper->currency($item->getFinalPrice()));
                                        break;
                                    case "small_image":
                                        $eachItem = $block->getImage($item, "product_small_image")->toHtml();
                                        break;
                                    default:
                                        $eachItem = $this->_catalogHelperOutput->productAttribute($item, $block->getProductAttributeValue($item, $attribute), $attribute->getAttributeCode());
                                        break;
                                }
                                $eachRow["value"][] = $eachItem;
                            }
                            $attributeValueList[] = $eachRow;
                        }
                        $returnArray["attributeValueList"] = $attributeValueList;
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