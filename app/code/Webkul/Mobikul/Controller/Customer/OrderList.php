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

    namespace Webkul\Mobikul\Controller\Customer;

    class OrderList extends AbstractCustomer        {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["message"]      = "";
            $returnArray["orderList"]    = [];
            $returnArray["totalCount"]   = 0;
            $returnArray["responseCode"] = 0;
            try {
                $wholeData = $this->getRequest()->getPostValue();
                $this->_headers   = $this->getRequest()->getHeaders();
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
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $pageNumber  = $this->_helper->validate($wholeData, "pageNumber") ? $wholeData["pageNumber"] : 1;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $orderCollection = $this->_objectManager
                            ->create("\Magento\Sales\Model\ResourceModel\Order\Collection")
                            ->addFieldToSelect("*")
                            ->addFieldToFilter("customer_id", $customerId)
                            ->addFieldToFilter("status", ["in"=>$this->_objectManager->create("\Magento\Sales\Model\Order\Config")->getVisibleOnFrontStatuses()])
                            ->setOrder("created_at", "DESC");
// Applying pagination //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($pageNumber >= 1) {
                            $returnArray["totalCount"] = $orderCollection->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $orderCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
// Creating Order List //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $orderList = [];
                        foreach ($orderCollection as $key=>$order) {
                            $eachOrder = [];
                            $eachOrder["id"]          = $key;
                            $eachOrder["order_id"]    = $order->getRealOrderId();
                            $eachOrder["date"]        = $this->_objectManager->get("\Magento\Sales\Block\Order\History")->formatDate($order->getCreatedAt());
                            $eachOrder["ship_to"]     = $order->getShippingAddress() ? $this->_helperCatalog->stripTags($order->getShippingAddress()->getName()) : " ";
                            $eachOrder["order_total"] = $this->_helperCatalog->stripTags($order->formatPrice($order->getGrandTotal()));
                            $eachOrder["state"]       = $order->getState();
                            $eachOrder["status"]      = $order->getStatusLabel();
                            $canReorder               = false;
                            if($this->canReorder($order))
                                $canReorder           = $this->canReorder($order);
                            $eachOrder["canReorder"]  = $canReorder;
                            $orderList[]              = $eachOrder;
                        }
                        $returnArray["orderList"]     = $orderList;
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