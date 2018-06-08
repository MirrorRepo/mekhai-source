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

    class OrderList extends AbstractMarketplace    {

        public function execute()   {
            $returnArray                = [];
            $returnArray["authKey"]     = "";
            $returnArray["success"]     = false;
            $returnArray["message"]     = "";
            $returnArray["orderList"]   = [];
            $returnArray["totalCount"]  = 0;
            $returnArray["orderStatus"] = [];
            $returnArray["manageOrder"] = false;
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
                        $status      = $this->_helper->validate($wholeData, "status")      ? $wholeData["status"]      : "";
                        $dateTo      = $this->_helper->validate($wholeData, "dateTo")      ? $wholeData["dateTo"]      : "";
                        $storeId     = $this->_helper->validate($wholeData, "storeId")     ? $wholeData["storeId"]     : 0;
                        $dateFrom    = $this->_helper->validate($wholeData, "dateFrom")    ? $wholeData["dateFrom"]    : "";
                        $customerId  = $this->_helper->validate($wholeData, "customerId")  ? $wholeData["customerId"]  : 0;
                        $pageNumber  = $this->_helper->validate($wholeData, "pageNumber")  ? $wholeData["pageNumber"]  : 1;
                        $incrementId = $this->_helper->validate($wholeData, "incrementId") ? $wholeData["incrementId"] : "";
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $orderIds    = $this->getOrderIdsArray($customerId, $status);
                        $ids         = $this->getEntityIdsArray($orderIds);
                        $this->_dashboardHelper->_sellerId = $customerId;
                        $orderCollection = $this->_orderCollectionFactory->create()
                            ->addFieldToSelect("*")
                            ->addFieldToFilter("entity_id", ["in"=>$ids]);
                        $to   = null;
                        $from = null;
                        if ($dateTo) {
                            $todate = date_create($dateTo);
                            $to     = date_format($todate, "Y-m-d 23:59:59");
                        }
                        if ($dateFrom) {
                            $fromdate = date_create($dateFrom);
                            $from     = date_format($fromdate, "Y-m-d H:i:s");
                        }
                        if ($incrementId) {
                            $orderCollection->addFieldToFilter("magerealorder_id", ["like"=>"%".$incrementId."%"]);
                        }
                        $orderCollection->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$from, "to"=>$to]);
                        $orderCollection->setOrder("created_at", "desc");
                        $orderList = [];
                        if($pageNumber >= 1) {
                            $returnArray["totalCount"] = $orderCollection->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $orderCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        foreach($orderCollection as $res){
                            $order    = $this->_dashboardHelper->getMainOrder($res["order_id"]);
                            $status   = $order->getStatusLabel();
                            $name     = $order->getCustomerName();
                            $tracking = $this->_marketplaceOrderhelper->getOrderinfo($res["order_id"]);
                            if(!is_array($tracking) && $tracking->getIsCanceled())
                                $status = "Canceled";
                            $eachOrder                 = [];
                            $eachOrder["status"]       = strtoupper($status);
                            $eachOrder["orderId"]      = $res["order_id"];
                            $eachOrder["incrementId"]  = $res["magerealorder_id"];
                            $eachOrder["productNames"] = $this->_dashboardHelper->getpronamebyorder($res["order_id"]);
                            if ($this->_marketplaceHelper->getSellerProfileDisplayFlag()) {
                                $eachOrder["customerDetails"]["name"]          = $name;
                                $eachOrder["customerDetails"]["date"]          = $this->_viewTemplate->formatDate($res["created_at"]);
                                $orderPrice                                    = $this->_dashboardHelper->getPricebyorder($res["order_id"]);
                                $eachOrder["customerDetails"]["baseTotal"]     = $this->_helperCatalog->stripTags($order->formatBasePrice($orderPrice));
                                $eachOrder["customerDetails"]["purchaseTotal"] = $this->_helperCatalog->stripTags($order->formatPrice($this->_dashboardHelper->getOrderedPricebyorder($order, $orderPrice)));
                            }
                            $orderList[] = $eachOrder;
                        }
                        $returnArray["orderList"] = $orderList;
                        $orderStatus = [];
                        $statusColl  = $this->_marketplaceOrderhelper->getOrderStatusData();
                        foreach ($statusColl as $status)
                            $orderStatus[] = $status;
                        $returnArray["orderStatus"] = $orderStatus;
                        $returnArray["manageOrder"] = (bool)$this->_helper->getConfigData("marketplace/general_settings/order_manage");
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

        public function getOrderIdsArray($customerId="", $filterOrderstatus="") {
            $orderids         = [];
            $collectionOrders = $this->_orderCollectionFactory->create()
                ->addFieldToFilter("seller_id", $customerId)
                ->addFieldToSelect("order_id")
                ->distinct(true);
            foreach ($collectionOrders as $collectionOrder) {
                $tracking = $this->getOrderinfo($collectionOrder->getOrderId(), $customerId);
                if ($tracking) {
                    if ($filterOrderstatus) {
                        if ($tracking->getIsCanceled()) {
                            if ($filterOrderstatus == "canceled")
                                array_push($orderids, $collectionOrder->getOrderId());
                        } else {
                            $tracking = $this->_orderRepository->get($collectionOrder->getOrderId());
                            if ($tracking->getStatus() == $filterOrderstatus)
                                array_push($orderids, $collectionOrder->getOrderId());
                        }
                    } else
                        array_push($orderids, $collectionOrder->getOrderId());
                }
            }
            return $orderids;
        }

        public function getEntityIdsArray($orderids = [])    {
            $ids = [];
            foreach ($orderids as $orderid) {
                $collectionIds = $this->_orderCollectionFactory->create()
                    ->addFieldToFilter("order_id", $orderid)
                    ->setOrder("entity_id", "DESC")
                    ->setPageSize(1);
                foreach ($collectionIds as $collectionId) {
                    $autoid = $collectionId->getId();
                    array_push($ids, $autoid);
                }
            }
            return $ids;
        }

        public function getOrderinfo($orderId="", $customerId="")    {
            $data  = [];
            $model = $this->_marketplaceOrders
                ->getCollection()
                ->addFieldToFilter("seller_id", $customerId)
                ->addFieldToFilter("order_id", $orderId);
            $salesOrder = $this->_marketplaceOrderResourceCollection->getTable("sales_order");
            $model->getSelect()->join($salesOrder." as so", "main_table.order_id=so.entity_id", ["order_approval_status"=>"order_approval_status"])
            ->where("so.order_approval_status=1");
            foreach ($model as $tracking) {
                $data = $tracking;
            }
            return $data;
        }

    }