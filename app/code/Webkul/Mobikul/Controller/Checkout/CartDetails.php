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

    namespace Webkul\Mobikul\Controller\Checkout;

    class CartDetails extends AbstractCheckout   {

        public function execute()   {
            $returnArray                                  = [];
            $returnArray["items"]                         = [];
            $returnArray["authKey"]                       = "";
            $returnArray["message"]                       = "";
            $returnArray["cartCount"]                     = 0;
            $returnArray["isVirtual"]                     = false;
            $returnArray["couponCode"]                    = "";
            $returnArray["responseCode"]                  = 0;
            $returnArray["tax"]["title"]                  = "";
            $returnArray["tax"]["value"]                  = "";
            $returnArray["showThreshold"]                 = false;
            $returnArray["crossSellList"]                 = [];
            $returnArray["subtotal"]["title"]             = "";
            $returnArray["subtotal"]["value"]             = "";
            $returnArray["discount"]["title"]             = "";
            $returnArray["discount"]["value"]             = "";
            $returnArray["shipping"]["title"]             = "";
            $returnArray["shipping"]["value"]             = "";
            $returnArray["grandtotal"]["title"]           = "";
            $returnArray["grandtotal"]["value"]           = "";
            $returnArray["allowMultipleShipping"]         = false;
            $returnArray["tax"]["unformatedValue"]        = 0.0;
            $returnArray["isAllowedGuestCheckout"]        = false;
            $returnArray["subtotal"]["unformatedValue"]   = 0.0;
            $returnArray["discount"]["unformatedValue"]   = 0.0;
            $returnArray["shipping"]["unformatedValue"]   = 0.0;
            $returnArray["canGuestCheckoutDownloadable"]  = false;
            $returnArray["grandtotal"]["unformatedValue"] = 0.0;
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
                        $width        = $this->_helper->validate($wholeData, "width")      ? $wholeData["width"]      : 1000;
                        $quoteId      = $this->_helper->validate($wholeData, "quoteId")    ? $wholeData["quoteId"]    : 0;
                        $storeId      = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $customerId   = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment  = $this->_emulate->startEnvironmentEmulation($storeId);
// setting currency /////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $store        = $this->_objectManager->create("\Magento\Store\Model\Store");
                        $baseCurrency = $store->getBaseCurrencyCode();
                        $currency     = $this->_helper->validate($wholeData, "currency") ? $wholeData["currency"] : $baseCurrency;
                        $store->setCurrentCurrencyCode($currency);
                        $checkoutHelper = $this->_objectManager->get("\Magento\Checkout\Helper\Data");
                        $catalogHelper  = $this->_helperCatalog;
                        if($this->_helper->getConfigData("sales/minimum_order/active")){
                            $returnArray["minimumAmount"] = intval($this->_helper->getConfigData("sales/minimum_order/amount"));
                            $returnArray["minimumFormattedAmount"] = $catalogHelper->stripTags($checkoutHelper->formatPrice($returnArray["minimumAmount"]));
                        }
                        else{
                            $returnArray["minimumAmount"] = intval($this->_helper->getConfigData("sales/minimum_order/amount"));
                            $returnArray["minimumFormattedAmount"] = $catalogHelper->stripTags($checkoutHelper->formatPrice(0));
                        }
                        $returnArray["showThreshold"] = (bool)$this->_helper->getConfigData("cataloginventory/options/product_stock_status");
                        $quote = new \Magento\Framework\DataObject();
                        if ($customerId != 0) {
                            $quoteCollection = $this->_quoteFactory->create()->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                // ->addFieldToFilter("store_id", $storeId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if ($quoteId != 0)
                            $quote = $this->_quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                        if($this->_helper->getConfigData("catalog/downloadable/shareable") == 1 && $this->_helper->getConfigData("catalog/downloadable/disable_guest_checkout") == 0)
                            $returnArray["canGuestCheckoutDownloadable"] = true;
                        $returnArray["allowMultipleShipping"] = (bool)$this->_helper->getConfigData("multishipping/options/checkout_multiple");
                        $cartProductIds = [];
                        if ($customerId != 0 || $quoteId != 0) {
                            $quote->getShippingAddress()->setCollectShippingRates(true);
                            $quote->collectTotals()->save();
                            $itemCollection = $quote->getAllVisibleItems();
                            $items = [];
                            foreach ($itemCollection as $item) {
                                $product = $item->getProduct();
                                if ($product)
                                    $cartProductIds[] = $product->getId();
                                $eachItem                 = [];
                                $eachItem["thresholdQty"] = $this->_helper->getConfigData("cataloginventory/options/stock_threshold_qty");
                                $stockState               = $this->_objectManager->get("\Magento\CatalogInventory\Api\StockStateInterface");
                                $eachItem["remainingQty"] = $stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
                                $eachItem["image"]        = $this->_helperCatalog->getImageUrl($product, $width/2.5, "product_page_image_small");
                                $eachItem["name"]         = html_entity_decode($item->getName());
                                $options                  = $product->getTypeInstance(true)->getOrderOptions($product);
                                if ($product->getTypeId() == "configurable") {
                                    $configurableOptions  = $options["attributes_info"];
                                    foreach ($configurableOptions as $configurableOption) {
                                        $eachConfigurableOption            = [];
                                        $eachConfigurableOption["label"]   = $configurableOption["label"];
                                        $eachConfigurableOption["value"][] = $configurableOption["value"];
                                        $eachItem["options"][]             = $eachConfigurableOption;
                                    }
                                }
                                if ($product->getTypeId() == "bundle") {
                                    $bundleOptions                       = $options["bundle_options"];
                                    foreach ($bundleOptions as $bundleOption) {
                                        $eachBundleOption                = [];
                                        $eachBundleOption["label"]       = $bundleOption["label"];
                                        foreach ($bundleOption["value"] as $bundleOptionValue) {
                                            $price                       = 0;
                                            if ($bundleOptionValue["price"] > 0)
                                                $price                   = $bundleOptionValue["price"]/$bundleOptionValue["qty"];
                                            $price                       = $this->_helperCatalog->stripTags($this->_priceHelper->currency($price));
                                            $eachBundleOptionValue       = $bundleOptionValue["qty"]." x ".$bundleOptionValue["title"]." ".$price;
                                            $eachBundleOption["value"][] = $eachBundleOptionValue;
                                        }
                                        $eachItem["options"][]           = $eachBundleOption;
                                    }
                                }
                                if ($product->getTypeId() == "downloadable") {
                                    $links = $this->_downloadableConfiguration->getLinks($item);
                                    if (count($links) > 0) {
                                        $downloadOption = [];
                                        $titles         = [];
                                        foreach ($links as $linkId)
                                            $titles[]   = $linkId->getTitle();
                                        $downloadOption["label"] = $this->_downloadableConfiguration->getLinksTitle($product);
                                        $downloadOption["value"] = $titles;
                                        $eachItem["options"][]   = $downloadOption;
                                    }
                                }
                                if (isset($options["options"])) {
                                    $customOptions                   = $options["options"];
                                    foreach ($customOptions as $customOption) {
                                        $eachCustomOption            = [];
                                        $eachCustomOption["label"]   = $customOption["label"];
                                        $eachCustomOption["value"][] = $customOption["print_value"];
                                        $eachItem["options"][]       = $eachCustomOption;
                                    }
                                }
                                $eachItem["sku"]       = $this->_helperCatalog->stripTags($item->getSku());
                                $eachItem["price"]     = $this->_helperCatalog->stripTags($checkoutHelper->formatPrice($item->getCalculationPrice()));
                                $eachItem["qty"]       = $item->getQty()*1;
                                $eachItem["productId"] = $item->getProductId();
                                $eachItem["typeId"]    = $item->getProductType();
                                $eachItem["subTotal"]  = $this->_helperCatalog->stripTags($checkoutHelper->formatPrice($item->getRowTotal()));
                                $eachItem["id"]        = $item->getId();
                                $baseMessages          = $item->getMessage(false);
                                if ($baseMessages) {
                                    foreach ($baseMessages as $message) {
                                        $messages = ["text"=>$message, "type"=>$item->getHasError() ? "error" : "notice"];
                                        $eachItem["messages"] = $messages;
                                    }
                                }
                                else
                                    $eachItem["messages"] = new \stdClass();;
                                $returnArray["items"][]   = $eachItem;
                            }
                            if(!is_null($quote->getCouponCode()))
                                $returnArray["couponCode"] = $quote->getCouponCode();
                            if($quote->getIsVirtual())
                                $returnArray["isVirtual"]  = (bool)$quote->getIsVirtual();
// getting cross sell list //////////////////////////////////////////////////////////////////////////////////////////////////////
                            if ($cartProductIds) {
                                $filterProductIds = array_merge(
                                    $cartProductIds,
                                    $this->_objectManager->get("\Magento\Quote\Model\Quote\Item\RelatedProducts")->getRelatedProductIds($quote->getAllItems())
                                );
                                $collection = $this->_objectManager
                                    ->get("\Magento\Catalog\Model\Product\LinkFactory")
                                    ->create()
                                    ->useCrossSellLinks()
                                    ->getProductCollection()
                                    ->setStoreId($storeId)
                                    ->addStoreFilter()
                                    ->setPageSize(4)
                                    ->setVisibility($this->_objectManager->get("\Magento\Catalog\Model\Product\Visibility")->getVisibleInCatalogIds())->addProductFilter($filterProductIds)
                                    ->addExcludeProductFilter($cartProductIds)
                                    ->setPageSize(4 - count($items))
                                    ->setGroupBy()
                                    ->setPositionOrder();
                                $crossSellList = [];
                                foreach ($collection as $eachProduct) {
                                    $eachProduct = $this->_objectManager->get("\Magento\Catalog\Model\ProductFactory")->create()->load($eachProduct->getId());
                                    if ($eachProduct->isAvailable())
                                        $crossSellList[] = $this->_helperCatalog->getOneProductRelevantData($eachProduct, $storeId, $width, $customerId);
                                }
                                $returnArray["crossSellList"] = $crossSellList;
                            }
// getting totals details ///////////////////////////////////////////////////////////////////////////////////////////////////////
                            if($checkoutHelper->isAllowedGuestCheckout($quote))
                                $returnArray["isAllowedGuestCheckout"] = $checkoutHelper->isAllowedGuestCheckout($quote);
                            if($quote->getItemsQty()*1 > 0){
                                $totals = [];
                                if ($quote->isVirtual())
                                    $totals = $quote->getBillingAddress()->getTotals();
                                else
                                    $totals = $quote->getShippingAddress()->getTotals();
                                $subtotal = []; $discount = []; $grandtotal = [];$shipping = [];
                                if(isset($totals["subtotal"])){
                                    $subtotal                         = $totals["subtotal"];
                                    $returnArray["subtotal"]["title"] = __($subtotal->getTitle());
                                    $returnArray["subtotal"]["value"] = $catalogHelper->stripTags($checkoutHelper->formatPrice($subtotal->getValue()));
                                    $returnArray["subtotal"]["unformatedValue"] = $subtotal->getValue();
                                }
                                if(isset($totals["discount"])){
                                    $discount                         = $totals["discount"];
                                    $returnArray["discount"]["title"] = __($discount->getTitle());
                                    $returnArray["discount"]["value"] = $catalogHelper->stripTags($checkoutHelper->formatPrice($discount->getValue()));
                                    $returnArray["discount"]["unformatedValue"] = $discount->getValue();
                                }
                                if(isset($totals["shipping"])){
                                    $shipping                         = $totals["shipping"];
                                    $returnArray["shipping"]["title"] = __($shipping->getTitle());
                                    $returnArray["shipping"]["value"] = $catalogHelper->stripTags($checkoutHelper->formatPrice($shipping->getValue()));
                                    $returnArray["shipping"]["unformatedValue"] = $shipping->getValue();
                                }
                                if(isset($totals["tax"])){
                                    $tax                         = $totals["tax"];
                                    $returnArray["tax"]["title"] = __($tax->getTitle());
                                    $returnArray["tax"]["value"] = $catalogHelper->stripTags($checkoutHelper->formatPrice($tax->getValue()));
                                    $returnArray["tax"]["unformatedValue"] = $tax->getValue();
                                }
                                if(isset($totals["grand_total"])){
                                    $grandtotal                         = $totals["grand_total"];
                                    $returnArray["grandtotal"]["title"] = __($grandtotal->getTitle());
                                    $returnArray["grandtotal"]["value"] = $catalogHelper->stripTags($checkoutHelper->formatPrice($grandtotal->getValue()));
                                    $returnArray["grandtotal"]["unformatedValue"] = $grandtotal->getValue();
                                }
                            }
                            $returnArray["cartCount"] = $quote->getItemsQty()*1;
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