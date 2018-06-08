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

    namespace Webkul\Mobikul\Controller\Customer;

    class OrderDetails extends AbstractCustomer     {

        public function execute()   {
            $returnArray                    = [];
            $returnArray["state"]           = "";
            $returnArray["authKey"]         = "";
            $returnArray["success"]         = false;
            $returnArray["message"]         = "";
            $returnArray["orderDate"]       = "";
            $returnArray["orderData"]       = [];
            $returnArray["canReorder"]      = false;
            $returnArray["incrementId"]     = 0;
            $returnArray["statusLabel"]     = "";
            $returnArray["hasInvoices"]     = false;
            $returnArray["invoiceList"]     = [];
            $returnArray["responseCode"]    = 0;
            $returnArray["hasShipments"]    = false;
            $returnArray["shipmentList"]    = [];
            $returnArray["paymentMethod"]   = "";
            $returnArray["hasCreditmemo"]   = false;
            $returnArray["creditmemoList"]  = [];
            $returnArray["shippingMethod"]  = __("No shipping information available");
            $returnArray["billingAddress"]  = "";
            $returnArray["shippingAddress"] = "";
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
                        $storeId     = $this->_helper->validate($wholeData, "storeId")     ? $wholeData["storeId"]     : 1;
                        $incrementId = $this->_helper->validate($wholeData, "incrementId") ? $wholeData["incrementId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $order       = $this->_objectManager->create("\Magento\Sales\Model\Order")->loadByIncrementId($incrementId);
                        $returnArray["hasShipments"] = (bool)$order->hasShipments();
                        $returnArray["hasInvoices"]  = (bool)$order->hasInvoices();
                        if(count($order->getCreditmemosCollection()) > 0)
                            $returnArray["hasCreditmemo"] = true;
                        if (!$order || !$order->getId()) {
                            $returnArray["message"] = __("Invalid Order.");
                            return $this->getJsonResponse($returnArray);
                        }
                        $returnArray["incrementId"] = $order->getRealOrderId();
                        $returnArray["state"]       = $order->getState();
                        $returnArray["statusLabel"] = $order->getStatusLabel();
                        $returnArray["orderDate"]   = $this->_objectManager->get("\Magento\Sales\Block\Order\Info")->formatDate($order->getCreatedAt(), \IntlDateFormatter::LONG);
                        if($this->_objectManager->get("\Magento\Sales\Helper\Reorder")->canReorder($order->getEntityId())){
                            $returnArray["canReorder"] = true;
                        }
                        $items       = $order->getItemsCollection();
                        $itemBlock   = $this->_objectManager->create("\Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer");
                        $priceBlock  = $this->_objectManager->create("\Magento\Weee\Block\Item\Price\Renderer");
// getting Order Item and Order Totals Details //////////////////////////////////////////////////////////////////////////////////
                        $orderData   = [];
                        $itemList    = [];
                        foreach($items as $item){
                            $itemBlock->setItem($item);
                            $priceBlock->setItem($item);
                            if($item->getParentItem())
                                continue;
                            $eachItem = [];
                            $eachItem["name"] = $itemBlock->escapeHtml($item->getName());
                            if ($options = $itemBlock->getItemOptions()){
                                foreach ($options as $option){
                                    $eachOption                  = [];
                                    $eachOption["label"]         = $itemBlock->escapeHtml($option["label"]);
                                    if (!$itemBlock->getPrintStatus()){
                                        $formatedOptionValue     = $itemBlock->getFormatedOptionValue($option);
                                        if (isset($formatedOptionValue["full_view"]))
                                            $eachOption["value"] = $formatedOptionValue["full_view"];
                                        else
                                            $eachOption["value"] = $formatedOptionValue["value"];
                                    }else{
                                        $eachOption["value"]     = nl2br($itemBlock->escapeHtml((isset($option["print_value"]) ? $option["print_value"] : $option["value"])));
                                    }
                                    $eachItem["option"][]        = $eachOption;
                                }
                            }
                            else{
                                $eachItem["option"]      = [];
                            }
                            $eachItem["sku"]             = $itemBlock->prepareSku($itemBlock->getSku());
                            if($priceBlock->displayPriceInclTax() || $priceBlock->displayBothPrices())
                                $eachItem["price"]       = $order->formatPriceTxt($block->getUnitDisplayPriceInclTax());
                            if($priceBlock->displayPriceExclTax() || $priceBlock->displayBothPrices())
                                $eachItem["price"]       = $order->formatPriceTxt($priceBlock->getUnitDisplayPriceExclTax());
                            $eachItem["qty"]["Ordered"]  = $itemBlock->getItem()->getQtyOrdered()*1;
                            $eachItem["qty"]["Shipped"]  = $itemBlock->getItem()->getQtyShipped()*1;
                            $eachItem["qty"]["Canceled"] = $itemBlock->getItem()->getQtyCanceled()*1;
                            $eachItem["qty"]["Refunded"] = $itemBlock->getItem()->getQtyRefunded()*1;
                            if(($priceBlock->displayPriceInclTax() || $priceBlock->displayBothPrices()) && !$item->getNoSubtotal())
                                $eachItem["subTotal"]    = $order->formatPriceTxt($priceBlock->getRowDisplayPriceInclTax());
                            if($priceBlock->displayPriceExclTax() || $priceBlock->displayBothPrices())
                                $eachItem["subTotal"]    = $order->formatPriceTxt($priceBlock->getRowDisplayPriceExclTax());
                            $itemList[]                  = $eachItem;
                        }
                        $orderData["itemList"]           = $itemList;
                        $totalsBlock = $this->_objectManager->create("\Webkul\Mobikul\Block\Sales\Order\Totals");
                        $totalsBlock->setOrder($order);
                        $totalsBlock->_initTotals();
                        $totals = [];
                        foreach ($totalsBlock->getTotals() as $total){
                            $eachTotal                   = [];
                            $eachTotal["code"]           = $total->getCode();
                            $eachTotal["label"]          = $total->getLabel();
                            $eachTotal["value"]          = $this->_helperCatalog->stripTags($total->getValue());
                            $eachTotal["formattedValue"] = $this->_helperCatalog->stripTags($totalsBlock->formatValue($total));
                            $totals[]                    = $eachTotal;
                        }
                        $eachTotal                       = [];
                        $eachTotal["label"]              = __("Tax");
                        $eachTotal["code"]               = "tax";
                        $eachTotal["value"]              = $order->getTaxAmount();
                        $eachTotal["formattedValue"]     = $order->formatPriceTxt($order->getTaxAmount());
                        $totals[]                        = $eachTotal;
                        $orderData["totals"]             = $totals;
                        $returnArray["orderData"]        = $orderData;
// getting Order Invoice and Invoice Totals Details /////////////////////////////////////////////////////////////////////////////
                        $invoiceList     = [];
                        foreach ($order->getInvoiceCollection() as $invoice){
                            $eachInvoice = [];
                            $eachInvoice["incrementId"] = $invoice->getIncrementId();
                            $items       = $invoice->getAllItems();
                            foreach ($items as $item){
                                if ($item->getOrderItem()->getParentItem())
                                    continue;
                                $itemBlock->setItem($item);
                                $priceBlock->setItem($item);
                                $eachItemData = [];
                                $eachItemData["name"] = $itemBlock->escapeHtml($item->getName());
                                if ($options = $itemBlock->getItemOptions()){
                                    foreach ($options as $option){
                                        $eachOption = [];
                                        $eachOption["label"]         = $option["label"];
                                        if (!$itemBlock->getPrintStatus()){
                                            $formatedOptionValue     = $itemBlock->getFormatedOptionValue($option);
                                            if (isset($formatedOptionValue["full_view"]))
                                                $eachOption["value"] = $formatedOptionValue["full_view"];
                                            else
                                                $eachOption["value"] = $formatedOptionValue["value"];
                                        }
                                        else
                                            $eachOption["value"]     = isset($option["print_value"]) ? $option["print_value"] : $option["value"];
                                        $eachItemData["option"][]    = $eachOption;
                                    }
                                }
                                else{
                                    $eachItemData["option"]  = [];
                                }
                                $eachInvoice["items"]        = $eachItemData;
                                $eachInvoice["sku"]          = $itemBlock->prepareSku($itemBlock->getSku());
                                if($priceBlock->displayPriceInclTax() || $priceBlock->displayBothPrices())
                                    $eachInvoice["price"]    = $order->formatPriceTxt($block->getUnitDisplayPriceInclTax());
                                if($priceBlock->displayPriceExclTax() || $priceBlock->displayBothPrices())
                                    $eachInvoice["price"]    = $order->formatPriceTxt($priceBlock->getUnitDisplayPriceExclTax());
                                $eachInvoice["qty"]          = $item->getQty()*1;
                                if(($priceBlock->displayPriceInclTax() || $priceBlock->displayBothPrices()) && !$item->getNoSubtotal())
                                    $eachInvoice["subTotal"] = $order->formatPriceTxt($priceBlock->getRowDisplayPriceInclTax());
                                if($priceBlock->displayPriceExclTax() || $priceBlock->displayBothPrices())
                                    $eachInvoice["subTotal"] = $order->formatPriceTxt($priceBlock->getRowDisplayPriceExclTax());
                            }
                            $invoiceTotalsBlock = $this->_objectManager->create("\Webkul\Mobikul\Block\Sales\Order\Invoice\Totals");
                            $invoiceTotalsBlock->setInvoice($invoice);
                            $invoiceTotalsBlock->setOrder($order);
                            $invoiceTotalsBlock->_initTotals();
                            $totals = [];
                            foreach ($invoiceTotalsBlock->getTotals() as $total){
                                $eachTotal                   = [];
                                $eachTotal["code"]           = $total->getCode();
                                $eachTotal["label"]          = $total->getLabel();
                                $eachTotal["value"]          = $total->getValue();
                                $eachTotal["formattedValue"] = $this->_helperCatalog->stripTags($invoiceTotalsBlock->formatValue($total));
                                $totals[]                    = $eachTotal;
                            }
                            $eachTotal                       = [];
                            $eachTotal["label"]              = __("Tax");
                            $eachTotal["code"]               = "tax";
                            $eachTotal["value"]              = $order->getTaxAmount();
                            $eachTotal["formattedValue"]     = $order->formatPriceTxt($order->getTaxAmount());
                            $totals[]                        = $eachTotal;
                            $eachInvoice["totals"]           = $totals;
                            $invoiceList[]                   = $eachInvoice;
                        }
                        $returnArray["invoiceList"] = $invoiceList;
// getting Order Shipment and Sipment Totals Details ////////////////////////////////////////////////////////////////////////////
                        $shipmentList                                   = [];
                        foreach ($order->getShipmentsCollection() as $shipment){
                            $eachShipment                               = [];
                            $eachShipment["incrementId"]                = $shipment->getIncrementId();
                            $items                                      = $shipment->getAllItems();
                            foreach ($items as $item){
                                if ($item->getOrderItem()->getParentItem())
                                    continue;
                                $eachshipmentItem                       = [];
                                $itemBlock->setItem($item);
                                $eachshipmentItem["name"]               = $itemBlock->escapeHtml($item->getName());
                                if ($options = $itemBlock->getItemOptions()){
                                    foreach ($options as $option){
                                        $eachOption                     = [];
                                        $eachOption["label"]            = $option["label"];
                                        if (!$itemBlock->getPrintStatus()){
                                            $formatedOptionValue        = $itemBlock->getFormatedOptionValue($option);
                                            if (isset($formatedOptionValue["full_view"]))
                                                $eachOption["value"]    = $formatedOptionValue["full_view"];
                                            else
                                                $eachOption["value"]    = $formatedOptionValue["value"];
                                        }else{
                                            $eachOption["value"]        = isset($option["print_value"]) ? $option["print_value"] : $option["value"];
                                        }
                                        $eachshipmentItem["option"][]   = $eachOption;
                                    }
                                }
                                else{
                                    $eachshipmentItem["option"]         = [];
                                }
                                $eachshipmentItem["sku"]                = $itemBlock->prepareSku($itemBlock->getSku());
                                $eachshipmentItem["qty"]                = $item->getQty()*1;
                                $eachShipment["items"]                  = $eachshipmentItem;
                            }
                            $shipmentList[]                             = $eachShipment;
                        }
                        $returnArray["shipmentList"]                    = $shipmentList;
// getting Order CreditMemo and CreditMemo Totals Details ///////////////////////////////////////////////////////////////////////
                        $creditmemoList                                 = [];
                        foreach ($order->getCreditmemosCollection() as $creditmemo){
                            $eachCreditmemo                             = [];
                            $eachCreditmemo["incrementId"]              = $creditmemo->getIncrementId();
                            $items                                      = $creditmemo->getAllItems();
                            foreach ($items as $item){
                                if ($item->getOrderItem()->getParentItem())
                                    continue;
                                $eachcreditmemoItem = [];
                                $itemBlock->setItem($item);
                                $eachcreditmemoItem["name"]             = $itemBlock->escapeHtml($item->getName());
                                if ($options = $itemBlock->getItemOptions()){
                                    foreach ($options as $option){
                                        $eachOption                     = [];
                                        $eachOption["label"]            = $option["label"];
                                        if (!$itemBlock->getPrintStatus()){
                                            $formatedOptionValue        = $itemBlock->getFormatedOptionValue($option);
                                            if (isset($formatedOptionValue["full_view"]))
                                                $eachOption["value"]    = $formatedOptionValue["full_view"];
                                            else
                                                $eachOption["value"]    = $formatedOptionValue["value"];
                                        }else{
                                            $eachOption["value"]        = isset($option["print_value"]) ? $option["print_value"] : $option["value"];
                                        }
                                        $eachcreditmemoItem["option"][] = $eachOption;
                                    }
                                }
                                elseif ($links = $itemBlock->getLinks()){
                                    $eachOption                         = [];
                                    $eachOption["label"]                = $itemBlock->getLinksTitle();
                                    foreach ($links->getPurchasedItems() as $link){
                                        $eachOption["value"]            = $itemBlock->escapeHtml($link->getLinkTitle());
                                    }
                                    $eachcreditmemoItem["option"][]     = $eachOption;
                                }
                                else{
                                    $eachcreditmemoItem["option"]       = [];
                                }
                                $eachcreditmemoItem["sku"]              = $itemBlock->prepareSku($itemBlock->getSku());
                                $priceBlock->setItem($item);
                                if($priceBlock->displayPriceInclTax() || $priceBlock->displayBothPrices())
                                    $eachcreditmemoItem["price"]        = $order->formatPriceTxt($block->getUnitDisplayPriceInclTax());
                                if($priceBlock->displayPriceExclTax() || $priceBlock->displayBothPrices())
                                    $eachcreditmemoItem["price"]        = $order->formatPriceTxt($priceBlock->getUnitDisplayPriceExclTax());
                                $eachcreditmemoItem["qty"]              = $item->getQty()*1;
                                if(($priceBlock->displayPriceInclTax() || $priceBlock->displayBothPrices()) && !$item->getNoSubtotal())
                                    $eachcreditmemoItem["subTotal"]     = $order->formatPriceTxt($priceBlock->getRowDisplayPriceInclTax());
                                if($priceBlock->displayPriceExclTax() || $priceBlock->displayBothPrices())
                                    $eachcreditmemoItem["subTotal"]     = $order->formatPriceTxt($priceBlock->getRowDisplayPriceExclTax());
                                $eachcreditmemoItem["discountAmount"]   = $order->formatPriceTxt(-$item->getDiscountAmount());
                                $eachcreditmemoItem["rowTotal"]         = $order->formatPriceTxt($itemBlock->getTotalAmount($item));
                                $eachCreditmemo["items"]                = $eachcreditmemoItem;
                                $creditMemoTotalsBlock = $this->_objectManager->create("\Webkul\Mobikul\Block\Sales\Order\Creditmemo\Totals");
                                $creditMemoTotalsBlock->setCreditmemo($creditmemo);
                                $creditMemoTotalsBlock->setOrder($order);
                                $creditMemoTotalsBlock->_initTotals();
                                $totals                                 = [];
                                foreach ($creditMemoTotalsBlock->getTotals() as $total){
                                    $eachTotal                          = [];
                                    $eachTotal["code"]                  = $total->getCode();
                                    $eachTotal["label"]                 = $total->getLabel();
                                    $eachTotal["value"]                 = $total->getValue();
                                    $eachTotal["formattedValue"]        = $this->_helperCatalog->stripTags($invoiceTotalsBlock->formatValue($total));
                                    $totals[]                           = $eachTotal;
                                }
                                $eachTotal                              = [];
                                $eachTotal["label"]                     = __("Tax");
                                $eachTotal["code"]                      = "tax";
                                $eachTotal["value"]                     = $order->getTaxAmount();
                                $eachTotal["formattedValue"]            = $order->formatPriceTxt($order->getTaxAmount());
                                $totals[]                               = $eachTotal;
                                $eachCreditmemo["totals"]               = $totals;
                            }
                            $creditmemoList[]                           = $eachCreditmemo;
                        }
                        $returnArray["creditmemoList"]                  = $creditmemoList;
// collecting order information /////////////////////////////////////////////////////////////////////////////////////////////////
                        $infoBlock = $this->_objectManager->create("\Magento\Sales\Block\Order\Info");
                        if($order->getShippingAddress())
                            $returnArray["shippingAddress"]             = $infoBlock->getFormattedAddress($order->getShippingAddress());
                        if ($order->getShippingDescription())
                            $returnArray["shippingMethod"]              = $infoBlock->escapeHtml($order->getShippingDescription());
                        $returnArray["billingAddress"]                  = $infoBlock->getFormattedAddress($order->getBillingAddress());
                        $returnArray["paymentMethod"]                   = $order->getPayment()->getMethodInstance()->getTitle();
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
                $returnArray["message"] = $e->getMessage();
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }