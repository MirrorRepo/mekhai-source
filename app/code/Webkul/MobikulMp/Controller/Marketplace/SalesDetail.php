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

    class SalesDetail extends AbstractMarketplace    {

        public function execute()   {
            $returnArray              = [];
            $returnArray["authKey"]   = "";
            $returnArray["success"]   = false;
            $returnArray["message"]   = "";
            $returnArray["salesList"] = [];
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
                        $productId   = $this->_helper->validate($wholeData, "productId")  ? $wholeData["productId"]  : 0;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $customer    = $this->_customer->load($customerId);
                        $this->_customerSession->setCustomer($customer);
                        $this->_customerSession->setCustomerId($customerId);
                        $collectionOrders = $this->_marketplaceSaleList
                            ->getCollection()
                            ->addFieldToFilter("seller_id", $customerId)
                            ->addFieldToFilter("mageproduct_id", $productId)
                            ->addFieldToFilter("magequantity", ["neq"=>0])
                            ->addFieldToSelect("order_id")
                            ->distinct(true);
                        $collection = $this->_marketplaceOrders
                            ->getCollection()
                            ->addFieldToFilter("order_id", ["in"=>$collectionOrders->getData()])
                            ->setOrder("entity_id", "desc");
                        $salesList = [];
                        foreach($collection as $marketplaceOrder)  {
                            $eachSale   = [];
                            $orderId    = $marketplaceOrder->getOrderId();
                            $order      = $this->_order->load($eachSale["orderId"]);
                            $shipmentId = 0;
                            $invoiceId  = 0;
                            $shipmentId = $marketplaceOrder->getShipmentId();
                            $invoiceId  = $marketplaceOrder->getInvoiceId();
                            $eachSale["orderId"]       = $orderId;
                            $eachSale["buyerName"]     = "";
                            $eachSale["incrementId"]   = $order["increment_id"];
                            if ($this->_marketplaceHelper->getSellerProfileDisplayFlag())
                                $eachSale["buyerName"] = $order->getCustomerName();
                            $eachSale["date"]          = $this->_viewTemplate->formatDate($marketplaceOrder->getCreatedAt());
                            $eachSale["invoiceId"]     = $invoiceId;
                            $eachSale["shipmentId"]    = $shipmentId;
////////////////////////////////////////////////////////////
///// hit url of core mp in the mentioned below format /////
////////////////////////////////////////////////////////////
// $block->getUrl('marketplace/order_shipment/printpdf', ['order_id'=>$order_id,'shipment_id'=>$shipment_id, '_secure'=>$this->getRequest()->isSecure()]);
// $block->getUrl('marketplace/order_invoice/printpdf', ['order_id'=>$order_id,'invoice_id'=>$invoice_id, '_secure' => $this->getRequest()->isSecure()]);
                            $salesList[] = $eachSale;
                        }
                        $returnArray["salesList"] = $salesList;
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