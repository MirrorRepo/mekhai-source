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

    class ManageOrder extends AbstractMarketplace    {

        public function execute()   {
            $returnArray                           = [];
            $returnArray["tax"]                    = "";
            $returnArray["date"]                   = "";
            $returnArray["success"]                = false;
            $returnArray["authKey"]                = "";
            $returnArray["message"]                = "";
            $returnArray["canShip"]                = true;
            $returnArray["itemList"]               = [];
            $returnArray["subTotal"]               = "";
            $returnArray["shipping"]               = "";
            $returnArray["discount"]               = "";
            $returnArray["buyerName"]              = "";
            $returnArray["orderTotal"]             = "";
            $returnArray["buyerEmail"]             = "";
            $returnArray["orderStatus"]            = "";
            $returnArray["incrementId"]            = "";
            $returnArray["mpcodcharge"]            = "";
            $returnArray["vendorTotal"]            = "";
            $returnArray["paymentMethod"]          = "";
            $returnArray["shippingMethod"]         = "";
            $returnArray["billingAddress"]         = "";
            $returnArray["mpCODAvailable"]         = false;
            $returnArray["orderBaseTotal"]         = "";
            $returnArray["vendorBaseTotal"]        = "";
            $returnArray["adminCommission"]        = "";
            $returnArray["shippingAddress"]        = "";
            $returnArray["adminBaseCommission"]    = "";
            $returnArray["showBuyerInformation"]   = true;
            $returnArray["showAddressInformation"] = true;
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
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 0;
                        $orderId     = $this->_helper->validate($wholeData, "orderId")    ? $wholeData["orderId"]    : 0;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $this->_customerSession->setCustomerId($customerId);
//                         $order            = $this->_order->load($orderId);
//                         $orderStatusLabel = $order->getStatusLabel();
//                         $orderCollection  = $this->_marketplaceOrders->getCollection()
//                             ->addFieldToFilter("order_id", $orderId)
//                             ->addFieldToFilter("seller_id", $customerId);
//                         if(count($orderCollection)){
//                             $this->_dashboardHelper->_sellerId = $customerId;
//                             $paymentCode = "";
//                             $payment_method = "";
//                             if($order->getPayment()){
//                                 $paymentCode = $order->getPayment()->getMethod();
//                                 $payment_method = $order->getPayment()->getMethodInstance()->getTitle();
//                             }
//                             $tracking = $this->_dashboardHelper->getOrderinfo($orderId);
//                             if($tracking != ""){
//                                 if($paymentCode == "mpcashondelivery"){
//                                     $returnArray["mpCODAvailable"] = true;
//                                     // $codcharges = $tracking->getCodCharges();
//                                 }
//                             }
//                             $isCanceled = $tracking->getIsCanceled();
//                             if($isCanceled)
//                                 $orderStatusLabel = "Canceled";
//                             $returnArray["orderStatus"] = $orderStatusLabel;
//                             $returnArray["incrementId"] = $order->getRealOrderId();
//                             $returnArray["date"] = $this->_viewTemplate->formatDate($order->getCreatedAt(), \IntlDateFormatter::MEDIUM, true, $this->_viewTemplate->getTimezoneForStore($order->getStore()));
//                             $_items           = $order->getItemsCollection();
//                             $_count           = $_items->count();
//                             $subtotal         = 0;
//                             $totaltax         = 0;
//                             $couponamount     = 0;
//                             $admin_subtotal   = 0;
//                             $shippingamount   = 0;
//                             $vendor_subtotal  = 0;
//                             $codcharges_total = 0;
//                             $itemList         = [];
//                             foreach ($_items as $_item) {
//                                 $eachItem     = [];
//                                 if ($_item->getParentItem()) {
//                                     continue;
//                                 }
//                                 $row_total              = 0;
//                                 $itemPrice              = 0;
//                                 $couponcharges          = 0;
//                                 $shippingcharges        = 0;
//                                 $seller_item_cost       = 0;
//                                 $totaltax_peritem       = 0;
//                                 $codcharges_peritem     = 0;
//                                 $available_seller_item  = 0;
//                                 $seller_item_commission = 0;
//                                 $seller_orderslist      = $this->_orderViewBlock->getSellerOrdersList($orderId, $_item->getProductId(), $_item->getItemId());
//                                 foreach($seller_orderslist as $seller_item){
//                                     $itemPrice              = $seller_item->getMageproPrice();
//                                     $totalamount            = $seller_item->getTotalAmount();
//                                     $couponcharges          = $seller_item->getAppliedCouponAmount();
//                                     $shippingcharges        = $seller_item->getShippingCharges();
//                                     $seller_item_cost       = $seller_item->getActualSellerAmount();
//                                     $totaltax_peritem       = $seller_item->getTotalTax();
//                                     $available_seller_item  = 1;
//                                     $seller_item_commission = $seller_item->getTotalCommission();
//                                     if($paymentCode == "mpcashondelivery")
//                                         $codcharges_peritem = $seller_item->getCodCharges();
//                                 }
//                                 if($available_seller_item == 1){
//                                     $row_total        = $itemPrice*$_item->getQtyOrdered();
//                                     $vendor_subtotal  = $vendor_subtotal+$seller_item_cost;
//                                     $subtotal         = $subtotal+$row_total;
//                                     $admin_subtotal   = $admin_subtotal+$seller_item_commission;
//                                     $totaltax         = $totaltax+$totaltax_peritem;
//                                     $codcharges_total = $codcharges_total+$codcharges_peritem;
//                                     $shippingamount   = $shippingamount+$shippingcharges;
//                                     $couponamount     = $couponamount+$couponcharges;
//                                     $result           = [];
//                                     if ($options = $_item->getProductOptions()) {
//                                         if (isset($options["options"]))
//                                             $result = array_merge($result, $options["options"]);
//                                         if (isset($options["additional_options"]))
//                                             $result = array_merge($result, $options["additional_options"]);
//                                         if (isset($options["attributes_info"]))
//                                             $result = array_merge($result, $options["attributes_info"]);
//                                     }
// // for bundle product ///////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                     $bundleitems  = array_merge([$_item], $_item->getChildrenItems());
//                                     $_count       = count ($bundleitems);
//                                     $_index       = 0;
//                                     $prevOptionId = "";
//                                     if($_item->getProductType() != "bundle")    {
//                                         $eachItem["productName"]             = $this->_viewTemplate->escapeHtml($_item->getName());
//                                         $eachItem["customOption"]            = [];
//                                         $eachItem["downloadableOptionLable"] = "";
//                                         $eachItem["downloadableOptionValue"] = [];
//                                         if($_item->getProductType() == "downloadable")  {
//                                             if ($options = $result)   {
//                                                 $customOption = [];
//                                                 foreach ($options as $option) {
//                                                     $eachOption = [];
//                                                     $eachOption["label"] = $this->_viewTemplate->escapeHtml($option["label"]);
//                                                     if (!$this->_viewTemplate->getPrintStatus())  {
//                                                         $formatedOptionValue = $this->_orderViewBlock->getFormatedOptionValue($option);
//                                                         if (isset($formatedOptionValue["full_view"]))
//                                                             $eachOption["value"] = $formatedOptionValue["full_view"];
//                                                         else
//                                                             $eachOption["value"] = $formatedOptionValue["value"];
//                                                     }else{
//                                                         $eachOption["value"] = $this->_viewTemplate->escapeHtml((isset($option["print_value"]) ? $option["print_value"] : $option["value"]));
//                                                     }
//                                                     $customOption[] = $eachOption;
//                                                 }
//                                                 $eachItem["customOption"] = $customOption;
//                                             }
// // downloadable /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                             if ($links = $this->_orderViewBlock->getDownloadableLinks($_item->getId())){
//                                                 $eachItem["downloadableOptionLable"] = $this->_orderViewBlock->getLinksTitle($_item->getId());
//                                                 foreach ($links->getPurchasedItems() as $link)
//                                                     $eachItem["downloadableOptionValue"][] = $this->_viewTemplate->escapeHtml($link->getLinkTitle());
//                                             }
//                                             // $addInfoBlock = $block->getProductAdditionalInformationBlock();
//                                             // if ($addInfoBlock)  {
//                                             //     $addInfoBlock->setItem($_item->getOrderItem())->toHtml();
//                                             // }
//                                             // $this->_viewTemplate->escapeHtml($_item->getDescription());
//                                         }else{
//                                             if($options = $result) {
//                                                 $customOption = [];
//                                                 foreach ($options as $option) {
//                                                     $eachOption = [];
//                                                     $eachOption["label"] = $this->_viewTemplate->escapeHtml($option["label"]);
//                                                     if (!$this->_viewTemplate->getPrintStatus()){
//                                                         $formatedOptionValue = $this->_orderViewBlock->getFormatedOptionValue($option);
//                                                         if (isset($formatedOptionValue["full_view"]))
//                                                             $eachOption["value"] = $formatedOptionValue["full_view"];
//                                                         else
//                                                             $eachOption["value"] = $formatedOptionValue["value"];
//                                                     }else{
//                                                         $eachOption["value"] = $this->_viewTemplate->escapeHtml((isset($option["print_value"]) ? $option["print_value"] : $option["value"]));
//                                                     }
//                                                     $customOption[] = $eachOption;
//                                                 }
//                                                 $eachItem["customOption"] = $customOption;
//                                             }
//                                         }
//                                         $eachItem["sku"]   = $_item->getSku();
//                                         $eachItem["price"] = $order->formatPrice($_item->getPrice());
//                                         $itemQtys = [];
//                                         if ($_item->getQtyOrdered() > 0){
//                                             $orderedQty          = [];
//                                             $orderedQty["label"] = __("Ordered");
//                                             $orderedQty["value"] = $_item->getQtyOrdered()*1;
//                                             $itemQtys[]          = $orderedQty;
//                                         }
//                                         if ($_item->getQtyInvoiced() > 0){
//                                             $invoicedQty          = [];
//                                             $invoicedQty["label"] = __("Invoiced");
//                                             $invoicedQty["value"] = $_item->getQtyInvoiced()*1;
//                                             $itemQtys[]           = $invoicedQty;
//                                         }
//                                         if ($_item->getQtyShipped() > 0){
//                                             $shippedQty          = [];
//                                             $shippedQty["label"] = __("Shipped");
//                                             $shippedQty["value"] = $_item->getQtyShipped()*1;
//                                             $itemQtys[]          = $shippedQty;
//                                         }
//                                         if ($_item->getQtyCanceled() > 0){
//                                             $canceledQty          = [];
//                                             $canceledQty["label"] = __("Canceled");
//                                             $canceledQty["value"] = $_item->getQtyCanceled()*1;
//                                             $itemQtys[]           = $canceledQty;
//                                         }
//                                         if ($_item->getQtyRefunded() > 0){
//                                             $refundedQty          = [];
//                                             $refundedQty["label"] = __("Refunded");
//                                             $refundedQty["value"] = $_item->getQtyRefunded()*1;
//                                             $itemQtys[]           = $refundedQty;
//                                         }
//                                         $eachItem["qty"]        = $itemQtys;
//                                         $eachItem["totalPrice"] = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $row_total));
//                                         $eachItem["mpcodprice"] = "";
//                                         if($paymentCode == "mpcashondelivery"){
//                                             $eachItem["mpcodprice"] = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $codcharges_peritem));
//                                         }
//                                         $eachItem["adminCommission"] = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $seller_item_commission));
//                                         $eachItem["vendorTotal"] = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $seller_item_cost));
//                                         $eachItem["subTotal"] = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $row_total));
//                                     }
//                                     else{
//                                         foreach ($bundleitems as $_bundleitem)  {
//                                             $attributes_option = $block->getSelectionAttribute($_bundleitem);
//                                             if ($_bundleitem->getParentItem())  {
//                                                 $attributes = $attributes_option;
//                                                 if ($prevOptionId != $attributes["option_id"]){
//                                                     $eachItem["productName"] = $attributes["option_label"];
//                                                     $prevOptionId = $attributes["option_id"];
//                                                 }
//                                             }
//                                             if (!$_bundleitem->getParentItem()) {
//                                                 $eachItem["productName"] = $this->_viewTemplate->escapeHtml($_bundleitem->getName());
//                                                 $eachItem["sku"] = $_bundleitem->getSku();
//                                                 $eachItem["price"] = $order->formatPrice($_item->getPrice());
//                                                 $itemQtys = [];
//                                                 if ($_item->getQtyOrdered() > 0)    {
//                                                     $orderedQty          = [];
//                                                     $orderedQty["label"] = __("Ordered");
//                                                     $orderedQty["value"] = $_item->getQtyOrdered()*1;
//                                                     $itemQtys[]          = $orderedQty;
//                                                 }
//                                                 $eachItem["qty"]         = $itemQtys;
//                                             } else {
//                                                 $row_total              = 0;
//                                                 $itemPrice              = 0;
//                                                 $couponcharges          = 0;
//                                                 $shippingcharges        = 0;
//                                                 $seller_item_cost       = 0;
//                                                 $totaltax_peritem       = 0;
//                                                 $codcharges_peritem     = 0;
//                                                 $available_seller_item  = 0;
//                                                 $seller_item_commission = 0;
//                                                 $seller_orderslist      = $block->getSellerOrdersList($orderId, $_bundleitem->getProductId(), $_bundleitem->getItemId());
//                                                 foreach($seller_orderslist as $seller_item){
//                                                     $available_seller_item  = 1;
//                                                     $totalamount            = $seller_item->getTotalAmount();
//                                                     $seller_item_cost       = $seller_item->getActualSellerAmount();
//                                                     $seller_item_commission = $seller_item->getTotalCommission();
//                                                     $shippingcharges        = $seller_item->getShippingCharges();
//                                                     $couponcharges          = $seller_item->getAppliedCouponAmount();
//                                                     $itemPrice              = $seller_item->getMageproPrice();
//                                                     $totaltax_peritem       = $seller_item->getTotalTax();
//                                                     if($paymentCode == "mpcashondelivery")
//                                                         $codcharges_peritem = $seller_item->getCodCharges();
//                                                 }
//                                                 $seller_item_qty  = $_bundleitem->getQtyOrdered();
//                                                 $row_total        = $itemPrice*$seller_item_qty;
//                                                 $vendor_subtotal  = $vendor_subtotal+$seller_item_cost;
//                                                 $subtotal         = $subtotal+$row_total;
//                                                 $admin_subtotal   = $admin_subtotal+$seller_item_commission;
//                                                 $totaltax         = $totaltax+$totaltax_peritem;
//                                                 $codcharges_total = $codcharges_total+$codcharges_peritem;
//                                                 $shippingamount   = $shippingamount+$shippingcharges;
//                                                 $couponamount     = $couponamount+$couponcharges;
//                                                 $eachItem["productName"] = $this->_orderViewBlock->getValueHtml($_bundleitem);
//                                                 // $addInfoBlock = $block->getOrderItemAdditionalInfoBlock();
//                                                 // if ($addInfoBlock)
//                                                 //     $addInfoBlock->setItem($_bundleitem)->toHtml();
//                                                 $eachItem["sku"] = $_bundleitem->getSku();
//                                                 $eachItem["price"] = $order->formatPrice($_bundleitem->getPrice());
//                                                 $itemQtys = [];
//                                                 if ($_bundleitem->getQtyOrdered() > 0){
//                                                     $orderedQty          = [];
//                                                     $orderedQty["label"] = __("Ordered");
//                                                     $orderedQty["value"] = $_bundleitem->getQtyOrdered()*1;
//                                                     $itemQtys[]          = $orderedQty;
//                                                 }
//                                                 if ($_bundleitem->getQtyInvoiced() > 0){
//                                                     $invoicedQty          = [];
//                                                     $invoicedQty["label"] = __("Invoiced");
//                                                     $invoicedQty["value"] = $_bundleitem->getQtyInvoiced()*1;
//                                                     $itemQtys[]           = $invoicedQty;
//                                                 }
//                                                 if ($_bundleitem->getQtyShipped() > 0){
//                                                     $shippedQty          = [];
//                                                     $shippedQty["label"] = __("Shipped");
//                                                     $shippedQty["value"] = $_bundleitem->getQtyShipped()*1;
//                                                     $itemQtys[]          = $shippedQty;
//                                                 }
//                                                 if ($_bundleitem->getQtyCanceled() > 0){
//                                                     $canceledQty          = [];
//                                                     $canceledQty["label"] = __("Canceled");
//                                                     $canceledQty["value"] = $_bundleitem->getQtyCanceled()*1;
//                                                     $itemQtys[]           = $canceledQty;
//                                                 }
//                                                 if ($_bundleitem->getQtyRefunded() > 0){
//                                                     $refundedQty          = [];
//                                                     $refundedQty["label"] = __("Refunded");
//                                                     $refundedQty["value"] = $_bundleitem->getQtyRefunded()*1;
//                                                     $itemQtys[]           = $refundedQty;
//                                                 }
//                                                 $eachItem["qty"]             = $itemQtys;
//                                                 $eachItem["totalPrice"]      = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $row_total));
//                                                 $eachItem["mpcodprice"]      = "";
//                                                 if($paymentCode == "mpcashondelivery"){
//                                                     $eachItem["mpcodprice"]  = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $codcharges_peritem));
//                                                 }
//                                                 $eachItem["adminCommission"] = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $seller_item_commission));
//                                                 $eachItem["vendorTotal"]     = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $seller_item_cost));
//                                                 $eachItem["subTotal"]        = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $row_total));
//                                             }
//                                         }
//                                     }
//                                 }
//                                 $itemList[] = $eachItem;
//                             }
//                             $returnArray["itemList"] = $itemList;
// // getting totals data //////////////////////////////////////////////////////////////////////////////////////////////////////////
//                             $taxToSeller                = $this->_marketplaceHelper->getConfigTaxManage();
//                             $totalTaxAmount             = 0;
//                             $totalCouponAmount          = 0;
//                             $refundedShippingAmount     = 0;
//                             foreach($orderCollection as $tracking){
//                                 $taxToSeller            = $tracking["tax_to_seller"];
//                                 $totalTaxAmount         = $tracking->getTotalTax();
//                                 $shippingamount         = $tracking->getShippingCharges();
//                                 $totalCouponAmount      = $tracking->getCouponAmount();
//                                 $refundedShippingAmount = $tracking->getRefundedShippingCharges();
//                             }
//                             $returnArray["subTotal"] = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $subtotal));
//                             $returnArray["shipping"] = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $shippingamount));
//                             $returnArray["discount"] = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $totalCouponAmount));
//                             $returnArray["tax"]      = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $totaltax));
//                             $admintotaltax           = 0;
//                             $vendortotaltax          = 0;
//                             if (!$taxToSeller)
//                                 $admintotaltax = $totaltax;
//                             else
//                                 $vendortotaltax = $totaltax;
//                             $returnArray["mpcodcharge"] = "";
//                             if($paymentCode == "mpcashondelivery"){
//                                 $returnArray["mpcodcharge"] = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, $codcharges_total));
//                             }
//                             $returnArray["orderTotal"] = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, ($subtotal+$shippingamount+$codcharges_total+$totaltax-$totalCouponAmount)));
//                             $returnArray["orderBaseTotal"] = "";
//                             if ($order->isCurrencyDifferent()) {
//                                 $returnArray["orderBaseTotal"] = $order->formatBasePrice($subtotal+$shippingamount+$codcharges_total+$totaltax-$totalCouponAmount);
//                             }
//                             $returnArray["vendorTotal"] = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, ($vendor_subtotal+$shippingamount+$codcharges_total+$vendortotaltax-$refundedShippingAmount-$couponamount)));
//                             $returnArray["vendorBaseTotal"] = "";
//                             if ($order->isCurrencyDifferent()) {
//                                 $returnArray["vendorBaseTotal"] = $order->formatPrice($vendor_subtotal+$shippingamount+$codcharges_total+$vendortotaltax-$refundedShippingAmount-$couponamount);
//                             }
//                             $returnArray["adminCommission"] = $order->formatPrice($this->_orderViewBlock->getOrderedPricebyorder($order, ($admin_subtotal+$admintotaltax)));
//                             $returnArray["adminBaseCommission"] = "";
//                             if ($order->isCurrencyDifferent()) {
//                                 $returnArray["adminBaseCommission"] = $order->formatBasePrice($admin_subtotal+$admintotaltax);
//                             }
//                         }
// // getting buyer information ////////////////////////////////////////////////////////////////////////////////////////////////////
//                         if ($this->_marketplaceHelper->getSellerProfileDisplayFlag()) {
//                             $returnArray["showBuyerInformation"] = true;
//                             $returnArray["buyerName"]  = $order->getCustomerName();
//                             $returnArray["buyerEmail"] = $order->getCustomerEmail();
//                         }
// // getting order information ////////////////////////////////////////////////////////////////////////////////////////////////////
//                         if ($this->_marketplaceHelper->getSellerProfileDisplayFlag()) {
//                             $returnArray["showAddressInformation"] = true;
//                             if ($this->_orderViewBlock->isOrderCanShip($order)){
//                                 $returnArray["canShip"] = true;
//                                 $returnArray["shippingAddress"] = $block->getFormattedAddress($order->getShippingAddress());
//                                 if ($order->getShippingDescription())
//                                     $returnArray["shippingMethod"] = $this->_viewTemplate->escapeHtml($order->getShippingDescription());
//                                 else
//                                     $returnArray["shippingMethod"] = __("No shipping information available");
//                             }
//                             $returnArray["billingAddress"] = $this->_viewTemplate->getFormattedAddress($order->getBillingAddress());
//                         }
//                         $returnArray["paymentMethod"] = $payment_method;
                        $returnArray["success"]   = true;
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