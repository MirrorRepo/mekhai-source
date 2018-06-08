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

    class Dashboard extends AbstractMarketplace    {

        public function execute()   {
            $returnArray                               = [];
            $returnArray["authKey"]                    = "";
            $returnArray["success"]                    = false;
            $returnArray["message"]                    = "";
            $returnArray["reviewList"]                 = [];
            $returnArray["totalPayout"]                = "";
            $returnArray["lifetimeSale"]               = "";
            $returnArray["categoryChart"]              = "";
            $returnArray["remainingAmount"]            = "";
            $returnArray["recentOrderList"]            = [];
            $returnArray["dailySalesStats"]            = "";
            $returnArray["yearlySalesStats"]           = "";
            $returnArray["weeklySalesStats"]           = "";
            $returnArray["monthlySalesStats"]          = "";
            $returnArray["topSellingProducts"]         = [];
            $returnArray["dailySalesLocationReport"]   = "";
            $returnArray["yearlySalesLocationReport"]  = "";
            $returnArray["weeklySalesLocationReport"]  = "";
            $returnArray["monthlySalesLocationReport"] = "";
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
                        $mFactor     = $this->_helper->validate($wholeData, "mFactor")    ? $wholeData["mFactor"]    : 1;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
// world based sales calculation images /////////////////////////////////////////////////////////////////////////////////////////
                        $this->_dashboardHelper->_sellerId = $customerId;
                        $this->_dashboardHelper->_width    = $width*$mFactor;
                        $this->_dashboardHelper->_height   = ($width/2)*$mFactor;
                        $returnArray["dailySalesLocationReport"]   = $this->_dashboardHelper->getMapSales("day");
                        $returnArray["yearlySalesLocationReport"]  = $this->_dashboardHelper->getMapSales("year");
                        $returnArray["weeklySalesLocationReport"]  = $this->_dashboardHelper->getMapSales("week");
                        $returnArray["monthlySalesLocationReport"] = $this->_dashboardHelper->getMapSales("month");
// date wise sales chart images /////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["dailySalesStats"]   = $this->_dashboardHelper->getSellerStatisticsGraphUrl("day");
                        $returnArray["yearlySalesStats"]  = $this->_dashboardHelper->getSellerStatisticsGraphUrl("year");
                        $returnArray["weeklySalesStats"]  = $this->_dashboardHelper->getSellerStatisticsGraphUrl("week");
                        $returnArray["monthlySalesStats"] = $this->_dashboardHelper->getSellerStatisticsGraphUrl("month");
// calculating amount data for seller ///////////////////////////////////////////////////////////////////////////////////////////
                        $totalSaleColl = $this->_saleperPartner
                            ->getCollection()
                            ->addFieldToFilter("seller_id", $customerId);
                        $totalSale       = 0;
                        $totalRemainSale = 0;
                        foreach($totalSaleColl as $value) {
                            $totalSale       = $value->getAmountReceived();
                            $totalRemainSale = $value->getAmountRemain();
                        }
                        $returnArray["totalPayout"]     = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($totalSale));
                        $returnArray["lifetimeSale"]    = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($totalSale+$totalRemainSale));
                        $returnArray["remainingAmount"] = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($totalRemainSale));
// getting top selling products /////////////////////////////////////////////////////////////////////////////////////////////////
                        $topSaleProductColl = $this->_orderCollectionFactory
                            ->create()
                            ->addFieldToFilter("seller_id", $customerId)
                            ->addFieldToFilter("parent_item_id", ["null"=>"true"])
                            ->getAllOrderProducts();
                        $name        = "";
                        $resultData  = [];
                        foreach ($topSaleProductColl as $coll)  {
                            $item    = $this->_orderItemRepository->get($coll["order_item_id"]);
                            $product = $item->getProduct();
                            $eachPro = [];
                            if ($product) {
                                $eachPro["id"]       = $product->getId();
                                $eachPro["qty"]      = $coll["qty"];
                                $eachPro["name"]     = $product->getName();
                                $eachPro["openable"] = true;
                            } else {
                                $eachPro["id"]       = $coll->getId();
                                $eachPro["qty"]      = $coll["qty"];
                                $eachPro["name"]     = $item->getName();
                                $eachPro["openable"] = false;
                            }
                            $resultData[] = $eachPro;
                        }
                        $returnArray["topSellingProducts"] = $resultData;
// getting category chart image /////////////////////////////////////////////////////////////////////////////////////////////////
                        $returnArray["categoryChart"] = $this->_dashboardHelper->getSellerStatisticsCategoryGraph();
// getting latest order history /////////////////////////////////////////////////////////////////////////////////////////////////
                        $orderCollection = $this->_dashboardHelper->getCollection();
                        $recentOrderList = [];
                        foreach($orderCollection as $res){
                            $order    = $this->_dashboardHelper->getMainOrder($res["order_id"]);
                            $status   = $order->getStatusLabel();
                            $name     = $order->getCustomerName();
                            $tracking = $this->_marketplaceOrderhelper->getOrderinfo($res["order_id"]);
                            if(!is_array($tracking) && $tracking->getIsCanceled())
                                $status = "Canceled";
                            $eachOrder                 = [];
                            $eachOrder["orderId"]      = $res["order_id"];
                            $eachOrder["incrementId"]  = $res["magerealorder_id"];
                            $eachOrder["productNames"] = $this->_dashboardHelper->getpronamebyorder($res["order_id"]);
                            $eachOrder["status"]       = strtoupper($status);
                            if ($this->_marketplaceHelper->getSellerProfileDisplayFlag()) {
                                $eachOrder["customerDetails"]["name"]          = $name;
                                $eachOrder["customerDetails"]["date"]          = $this->_viewTemplate->formatDate($res["created_at"]);
                                $orderPrice                                    = $this->_dashboardHelper->getPricebyorder($res["order_id"]);
                                $eachOrder["customerDetails"]["baseTotal"]     = $this->_helperCatalog->stripTags($order->formatBasePrice($orderPrice));
                                $eachOrder["customerDetails"]["purchaseTotal"] = $this->_helperCatalog->stripTags($order->formatPrice($this->_dashboardHelper->getOrderedPricebyorder($order, $orderPrice)));
                            }
                            $recentOrderList[] = $eachOrder;
                        }
                        $returnArray["recentOrderList"] = $recentOrderList;
// getting latest order history /////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($this->_marketplaceHelper->getSellerProfileDisplayFlag()) {
                            $rate             = [];
                            $ratings          = [];
                            $products         = [];
                            $reviewList       = [];
                            $reviewcollection = $this->_dashboardHelper->getReviewcollection();
                            foreach($reviewcollection as $keyed)    {
                                $eachReview                  = [];
                                $eachReview["name"]          = $this->_customer->load($keyed->getBuyerId())->getName();
                                $eachReview["date"]          = $keyed["created_at"];
                                $eachReview["comment"]       = $keyed["feed_review"];
                                $eachReview["priceRating"]   = ceil($keyed["feed_price"]);
                                $eachReview["valueRating"]   = ceil($keyed["feed_value"]);
                                $eachReview["qualityRating"] = ceil($keyed["feed_quality"]);
                                $reviewList[]                = $eachReview;
                            }
                            $returnArray["reviewList"]       = $reviewList;
                        }
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