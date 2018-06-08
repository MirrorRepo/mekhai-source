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

    class ViewTransaction extends AbstractMarketplace    {

        public function execute()   {
            $returnArray                  = [];
            $returnArray["date"]          = "";
            $returnArray["type"]          = "";
            $returnArray["method"]        = "";
            $returnArray["amount"]        = "";
            $returnArray["authKey"]       = "";
            $returnArray["success"]       = false;
            $returnArray["message"]       = "";
            $returnArray["comment"]       = __("None");
            $returnArray["orderList"]     = [];
            $returnArray["transactionId"] = "";
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
                        $storeId       = $this->_helper->validate($wholeData, "storeId")       ? $wholeData["storeId"]       : 0;
                        $customerId    = $this->_helper->validate($wholeData, "customerId")    ? $wholeData["customerId"]    : 0;
                        $transactionId = $this->_helper->validate($wholeData, "transactionId") ? $wholeData["transactionId"] : 0;
                        $environment   = $this->_emulate->startEnvironmentEmulation($storeId);
                        $transaction                  = $this->_sellerTransaction->load($transactionId);
                        $returnArray["transactionId"] = $transaction->getTransactionId();
                        $returnArray["date"]          = $this->_viewTemplate->formatDate($transaction->getCreatedAt(), \IntlDateFormatter::LONG);
                        $returnArray["type"]          = $transaction->getType();
                        $returnArray["method"]        = $transaction->getMethod();
                        $returnArray["amount"]        = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($transaction->getTransactionAmount()));
                        if($transaction->getCustomNote()){
                            $returnArray["comment"]   = $transaction->getCustomNote();
                        }
// getting Order list ///////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $orderCollection = $this->_orderCollectionFactory->create()
                            ->addFieldToFilter("seller_id", $customerId)
                            ->addFieldToFilter("trans_id", $transactionId)
                            ->addFieldToFilter("order_id", ["neq"=>0]);
                        $orderList = [];
                        foreach($orderCollection as $order)  {
                            $sellerId      = $order->getSellerId();
                            $mageorderid   = $order->getOrderId();
                            $totalShipping = 0;
                            if ($order->getIsShipping()) {
                                $totalShipping = $this->sellerOrderShippingAmount($sellerId, $mageorderid);
                            }
                            $eachOrder                = [];
                            $eachOrder["qty"]         = $order->getMagequantity();
                            $eachOrder["price"]       = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($order->getMageproPrice()));
                            $eachOrder["totalTax"]    = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($order->getTotalTax()));
                            $eachOrder["shipping"]    = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($totalShipping));
                            $eachOrder["totalPrice"]  = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($order->getTotalAmount()));
                            $eachOrder["commission"]  = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($order->getTotalCommission()));
                            $eachOrder["incrementId"] = $order->getMagerealorderId();
                            $eachOrder["productName"] = $order["magepro_name"];
                            $admintotaltax         = 0;
                            $vendortotaltax        = 0;
                            if(!$this->_marketplaceHelper->getConfigTaxManage())
                                $admintotaltax     = $order->getTotalTax();
                            else
                                $vendortotaltax    = $order->getTotalTax();
                            $eachOrder["subTotal"] = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($order->getActualSellerAmount()+$vendortotaltax+$totalShipping));
                            $orderList[] = $eachOrder;
                        }
                        $returnArray["orderList"] = $orderList;
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

        public function sellerOrderShippingAmount($sellerId, $orderId)  {
            $coll = $this->_marketplaceOrderResourceCollection
                ->addFieldToFilter("seller_id", $sellerId)
                ->addFieldToFilter("order_id", $orderId);
            $shippingAmount = 0;
            foreach ($coll as $key => $value) {
                $shippingAmount = $value->getShippingCharges();
            }
            return $shippingAmount;
        }

    }