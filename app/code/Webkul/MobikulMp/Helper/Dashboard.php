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

    namespace Webkul\MobikulMp\Helper;
    use Magento\Sales\Model\OrderRepository;
    use Magento\Catalog\Model\CategoryRepository;
    use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;

    class Dashboard extends \Magento\Framework\App\Helper\AbstractHelper     {

        public $_width;
        public $_height;
        public $_sellerId;
        protected $_orders;
        protected $_region;
        protected $_request;
        protected $_urlHelper;
        protected $_salesList;
        protected $_localeList;
        protected $_orderRepository;
        protected $_marketplaceHelper;
        protected $_marketplaceOrders;
        protected $_categoryRepository;
        protected $_orderItemRepository;
        protected $_orderCollectionFactory;
        protected $_marketplaceFeedbackModel;
        protected $_marketplaceDashboardHelper;
        protected $_marketplaceOrderResourceCollection;

        public function __construct(
            OrderRepository $orderRepository,
            \Magento\Framework\Url $urlHelper,
            \Magento\Sales\Model\Order $order,
            CategoryRepository $categoryRepository,
            \Magento\Directory\Model\Region $region,
            CollectionFactory $orderCollectionFactory,
            \Magento\Framework\App\Request\Http $request,
            \Webkul\Marketplace\Model\Saleslist $salesList,
            \Magento\Framework\App\Helper\Context $context,
            \Webkul\Marketplace\Helper\Data $marketplaceHelper,
            \Webkul\Marketplace\Model\Orders $marketplaceOrders,
            \Magento\Framework\Locale\ListsInterface $localeList,
            \Webkul\Marketplace\Model\Feedback $marketplaceFeedbackModel,
            \Magento\Sales\Model\Order\ItemRepository $orderItemRepository,
            \Webkul\Marketplace\Helper\Dashboard\Data $marketplaceDashboardHelper,
            \Webkul\Marketplace\Model\ResourceModel\Orders\Collection $marketplaceOrderResourceCollection
        ) {
            $this->_orders                             = $order;
            $this->_region                             = $region;
            $this->_request                            = $request;
            $this->_urlHelper                          = $urlHelper;
            $this->_salesList                          = $salesList;
            $this->_localeList                         = $localeList;
            $this->_orderRepository                    = $orderRepository;
            $this->_marketplaceHelper                  = $marketplaceHelper;
            $this->_marketplaceOrders                  = $marketplaceOrders;
            $this->_categoryRepository                 = $categoryRepository;
            $this->_orderItemRepository                = $orderItemRepository;
            $this->_orderCollectionFactory             = $orderCollectionFactory;
            $this->_marketplaceFeedbackModel           = $marketplaceFeedbackModel;
            $this->_marketplaceDashboardHelper         = $marketplaceDashboardHelper;
            $this->_marketplaceOrderResourceCollection = $marketplaceOrderResourceCollection;
            parent::__construct($context);
        }

        public function getMapSales($dateType)    {
            $params = [
                "cht"  => "map:fixed=-60, -180, 85, 180",
                "chma" => "0, 110, 0, 0"
            ];
            $i                    = 0;
            $chmArr               = [];
            $chcoArr              = [];
            $chdlArr              = [];
            $getSale              = $this->getSale($dateType);
            $saleArray            = [];
            $countryArr           = $getSale["country_arr"];
            $totalContrySale      = $getSale["country_sale_arr"];
            $countryRegionArr     = $getSale["country_region_arr"];
            $countryOrderCountArr = $getSale["country_order_count_arr"];
            array_push($chcoArr, "B3BCC0");
            foreach ($countryRegionArr as $key => $value) {
                foreach ($value as $key2 => $value2) {
                    $count  = $countryOrderCountArr[$key][$key2];
                    $amount = $totalContrySale[$key][$key2];
                    $chmVal = "f".$value2.":Orders-".$count." Sales-".$amount.",000000,0,".$i.",10";
                    array_push($chmArr, $chmVal);
                    array_push($chdlArr, $value2);
                    array_push($chcoArr, $this->randString());
                    array_push($saleArray, $totalContrySale[$key][$key2]);
                    $i++;
                }
            }
            $params["chm"]  = implode("|", $chmArr);
            $params["chld"] = implode("|", $chdlArr);
            $params["chdl"] = implode("|", $chdlArr);
            $params["chco"] = implode("|", $chcoArr);
            if (count($saleArray))
                $totalSale = max($saleArray);
            else
                $totalSale = 0;
            if ($totalSale) {
                $a = $totalSale / 10;
                $axisYArr = [];
                for ($i = 1; $i <= 10; ++$i)
                    array_push($axisYArr, $a * $i);
                $axisY = implode("|", $axisYArr);
            } else {
                $axisY = "10|20|30|40|50|60|70|80|90|100";
            }
            $minvalue    = 0;
            $maxvalue    = $totalSale;
            $valueBuffer = [];
// seller statistics graph size
            $params["chs"] = $this->_width."x".$this->_height;
// return the encoded graph image url
            $getParamData         = urlencode(base64_encode(json_encode($params)));
            $getEncryptedHashData = $this->_marketplaceDashboardHelper->getChartEncryptedHashData($getParamData);
            $params               = [
                "param_data"     => $getParamData,
                "encrypted_data" => $getEncryptedHashData
            ];
            return $this->_urlHelper->getUrl("marketplace/account/dashboard_tunnel", ["_query"=>$params, "_secure"=>$this->_request->isSecure()]);
        }

        public function randString($charset="ABC0123456789") {
            $str    = "";
            $count  = strlen($charset);
            $length = 6;
            while ($length--) {
                $str .= $charset[mt_rand(0, $count - 1)];
            }
            return $str;
        }

        public function getSale($dateType = "year")     {
            $data = [];
            if ($dateType == "year") {
                $data = $this->getYearlySaleLocation($this->_sellerId);
            } elseif ($dateType == "month") {
                $data = $this->getMonthlySaleLocation($this->_sellerId);
            } elseif ($dateType == "week") {
                $data = $this->getWeeklySaleLocation($this->_sellerId);
            } elseif ($dateType == "day") {
                $data = $this->getDailySaleLocation($this->_sellerId);
            }
            return $data;
        }

        public function getYearlySaleLocation()     {
            $data     = [];
            $curryear = date("Y");
            $date1    = $curryear."-01-01 00:00:00";
            $date2    = $curryear."-12-31 23:59:59";
            $sellerOrderCollection = $this->_salesList
                ->getCollection()
                ->addFieldToFilter("seller_id", $this->_sellerId)
                ->addFieldToFilter("order_id", ["neq"=>0])
                ->addFieldToFilter("paid_status", ["neq"=>2]);
            $orderSaleArr = [];
            foreach ($sellerOrderCollection as $record) {
                $orderId = $record->getOrderId();
                if (!isset($orderSaleArr[$orderId])) {
                    $orderSaleArr[$orderId] = $record->getActualSellerAmount();
                } else {
                    $orderSaleArr[$orderId] = $orderSaleArr[$orderId] + $record->getActualSellerAmount();
                }
            }
            $orderIds = $sellerOrderCollection->getAllOrderIds();
            $collection = $this->_orders
                ->getCollection()
                ->addFieldToFilter("entity_id", ["in"=>$orderIds])
                ->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$date1, "to"=>$date2]);
            $data = $this->getArrayData($collection, $orderSaleArr);
            return $data;
        }

        public function getMonthlySaleLocation()    {
            $data      = [];
            $currDay   = date("d");
            $curryear  = date("Y");
            $currMonth = date("m");
            $date1     = $curryear."-".$currMonth."-01 00:00:00";
            $date2     = $curryear."-".$currMonth."-".$currDay." 23:59:59";
            $sellerOrderCollection = $this->_salesList
                ->getCollection()
                ->addFieldToFilter("seller_id", $this->_sellerId)
                ->addFieldToFilter("order_id", ["neq"=>0])
                ->addFieldToFilter("paid_status", ["neq"=>2]);
            $orderSaleArr = [];
            foreach ($sellerOrderCollection as $record) {
                $orderId = $record->getOrderId();
                if (!isset($orderSaleArr[$orderId])) {
                    $orderSaleArr[$orderId] = $record->getActualSellerAmount();
                } else {
                    $orderSaleArr[$orderId] = $orderSaleArr[$orderId] + $record->getActualSellerAmount();
                }
            }
            $orderIds   = $sellerOrderCollection->getAllOrderIds();
            $collection = $this->_orders
                ->getCollection()
                ->addFieldToFilter("entity_id", ["in"=>$orderIds])
                ->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$date1, "to"=>$date2]);
            $data = $this->getArrayData($collection, $orderSaleArr);
            return $data;
        }

        public function getWeeklySaleLocation()     {
            $data              = [];
            $curryear          = date("Y");
            $currMonth         = date("m");
            $currDay           = date("d");
            $currWeekDay       = date("N");
            $currWeekStartDay  = $currDay - $currWeekDay;
            $currWeekEndDay    = $currWeekStartDay + 7;
            $currentDayOfMonth = date("j");
            if ($currWeekEndDay > $currentDayOfMonth)
                $currWeekEndDay = $currentDayOfMonth;
            $date1 = $curryear."-".$currMonth."-".abs($currWeekStartDay)." 00:00:00";
            $date2 = $curryear."-".$currMonth."-".abs($currWeekEndDay)." 23:59:59";
            $sellerOrderCollection = $this->_salesList
                ->getCollection()
                ->addFieldToFilter("seller_id", $this->_sellerId)
                ->addFieldToFilter("order_id", ["neq"=>0])
                ->addFieldToFilter("paid_status", ["neq"=>2]);
            $orderSaleArr = [];
            foreach ($sellerOrderCollection as $record) {
                $orderId = $record->getOrderId();
                if (!isset($orderSaleArr[$orderId])) {
                    $orderSaleArr[$orderId] = $record->getActualSellerAmount();
                } else {
                    $orderSaleArr[$orderId] = $orderSaleArr[$orderId] + $record->getActualSellerAmount();
                }
            }
            $orderIds   = $sellerOrderCollection->getAllOrderIds();
            $collection = $this->_orders
                ->getCollection()
                ->addFieldToFilter("entity_id", ["in"=>$orderIds])
                ->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$date1, "to"=>$date2]);
            $data = $this->getArrayData($collection, $orderSaleArr);
            return $data;
        }

        public function getDailySaleLocation()  {
            $data      = [];
            $curryear  = date("Y");
            $currMonth = date("m");
            $currDay   = date("d");
            $date1     = $curryear."-".$currMonth."-".$currDay." 00:00:00";
            $date2     = $curryear."-".$currMonth."-".$currDay." 23:59:59";
            $sellerOrderCollection = $this->_salesList
                ->getCollection()
                ->addFieldToFilter("seller_id", $this->_sellerId)
                ->addFieldToFilter("order_id", ["neq"=>0])
                ->addFieldToFilter("paid_status", ["neq"=>2]);
            $orderSaleArr = [];
            foreach ($sellerOrderCollection as $record) {
                $orderId = $record->getOrderId();
                if (!isset($orderSaleArr[$orderId])) {
                    $orderSaleArr[$orderId] = $record->getActualSellerAmount();
                } else {
                    $orderSaleArr[$orderId] = $orderSaleArr[$orderId] + $record->getActualSellerAmount();
                }
            }
            $orderIds = $sellerOrderCollection->getAllOrderIds();
            $collection = $this->_orders
                ->getCollection()
                ->addFieldToFilter("entity_id", ["in"=>$orderIds])
                ->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$date1, "to"=>$date2]);
            $data = $this->getArrayData($collection, $orderSaleArr);
            return $data;
        }

        public function getArrayData($collection, $orderSaleArr)    {
            $countryArr           = [];
            $countrySaleArr       = [];
            $countryRegionArr     = [];
            $countryOrderCountArr = [];
            foreach ($collection as $record) {
                $addressData = $record->getBillingAddress()->getData();
                $countryId   = $addressData["country_id"];
                $countryName = $this->_localeList->getCountryTranslation($countryId);
                $countryArr[$countryId] = $countryName;
                if (isset($orderSaleArr[$record->getId()])) {
                    if (!isset($countryRegionArr[$countryId])) {
                        $countryRegionArr[$countryId] = [];
                    }
                    if (!isset($countrySaleArr[$countryId])) {
                        $countrySaleArr[$countryId] = [];
                    }
                    if (!isset($countryOrderCountArr[$countryId])) {
                        $countryOrderCountArr[$countryId] = [];
                    }
                    if ($addressData["region_id"]) {
                        $regionId   = $addressData["region_id"];
                        $region     = $this->_region->load($regionId);
                        $regionCode = $region->getCode();
                        $countryRegionArr[$countryId][$regionCode]         = strtoupper($countryId)."-".strtoupper($regionCode);
                        if (!isset($countrySaleArr[$countryId][$regionCode])) {
                            $countrySaleArr[$countryId][$regionCode]       = $orderSaleArr[$record->getId()];
                            $countryOrderCountArr[$countryId][$regionCode] = 1;
                        } else {
                            $countrySaleArr[$countryId][$regionCode]       = $countrySaleArr[$countryId][$regionCode] + $orderSaleArr[$record->getId()];
                            $countryOrderCountArr[$countryId][$regionCode] = $countryOrderCountArr[$countryId][$regionCode] + 1;
                        }
                    } else {
                        $countryRegionArr[$countryId][$countryId]         = strtoupper($countryId);
                        if (!isset($countrySaleArr[$countryId][$countryId])) {
                            $countrySaleArr[$countryId][$countryId]       = $orderSaleArr[$record->getId()];
                            $countryOrderCountArr[$countryId][$countryId] = 1;
                        } else {
                            $countrySaleArr[$countryId][$countryId]       = $countrySaleArr[$countryId][$countryId] + $orderSaleArr[$record->getId()];
                            $countryOrderCountArr[$countryId][$countryId] = $countryOrderCountArr[$countryId][$countryId] + 1;
                        }
                    }
                }
            }
            $data["country_arr"]             = $countryArr;
            $data["country_sale_arr"]        = $countrySaleArr;
            $data["country_region_arr"]      = $countryRegionArr;
            $data["country_order_count_arr"] = $countryOrderCountArr;
            return $data;
        }

        public function getSellerStatisticsGraphUrl($dateType)  {
            $params = [
                "cht"  => "bvs",
                "chm"  => "N,000000,0,-1,11",
                "chf"  => "bg,s,ffffff",
                "chxt" => "x,y",
                "chds" => "a",
                "chbh" => "55",
                "chco" => "ef672f"
            ];
            $getData = $this->getSaleDiagram($dateType);
            $getSale = $getData["values"];
            if (isset($getData["arr"])) {
                $arr            = $getData["arr"];
                $totalChb       = count($arr);
                $indexid        = 0;
                $tmpstring      = implode("|", $arr);
                $valueBuffer[]  = $indexid.":|".$tmpstring;
                $valueBuffer    = implode("|", $valueBuffer);
                $params["chxl"] = $valueBuffer;
            } else {
                $params["chxl"] = $getData['chxl'];
            }
            if (count($getSale)) {
                $totalSale = max($getSale);
            } else {
                $totalSale = 0;
            }
            if ($totalSale) {
                $countMonths = count($getSale)+1;
                if ($countMonths > 7) {
                    $totalChb = (int) (800 / $countMonths);
                    $params["chbh"] = $totalChb;
                } else {
                    $params["chbh"] = 100;
                }
                $a = $totalSale / 10;
                $axisYArr = [];
                for ($i=1; $i<=10; ++$i) {
                    array_push($axisYArr, $a * $i);
                }
                $axisY = implode("|", $axisYArr);
            } else {
                $axisY = "10|20|30|40|50|60|70|80|90|100";
            }
            $minvalue = 0;
            $maxvalue = $totalSale;
            $params["chd"] = "t:".implode(",", $getSale);
            $valueBuffer = [];
// seller statistics graph size
            $params["chs"] = $this->_width."x".$this->_height;
// return the encoded graph image url
            $_sellerDashboardHelperData = $this->_marketplaceDashboardHelper;
            $getParamData = urlencode(base64_encode(json_encode($params)));
            $getEncryptedHashData = $this->_marketplaceDashboardHelper->getChartEncryptedHashData($getParamData);
            $params = [
                "param_data"    => $getParamData,
                "encrypted_data" => $getEncryptedHashData,
            ];
            return $this->_urlHelper->getUrl("marketplace/account/dashboard_tunnel", ["_query"=>$params, "_secure"=>$this->_request->isSecure()]);
        }

        public function getSaleDiagram($dateType="year")     {
            $data = [];
            if ($dateType == "year") {
                $data = $this->getYearlySale();
            } elseif ($dateType == "month") {
                $data = $this->getMonthlySale();
            } elseif ($dateType == "week") {
                $data = $this->getWeeklySale();
            } elseif ($dateType == "day") {
                $data = $this->getDailySale();
            }
            return $data;
        }

        public function getYearlySale()    {
            $data           = [];
            $data["values"] = [];
            $data["chxl"]   = "0:|";
            $curryear       = date("Y");
            $currMonth      = date("m");
            $monthsArr      = [
                "",
                __("January"),
                __("February"),
                __("March"),
                __("April"),
                __("May"),
                __("June"),
                __("July"),
                __("August"),
                __("September"),
                __("October"),
                __("November"),
                __("December")
            ];
            for ($i=1; $i<=$currMonth; ++$i) {
                $date1 = $curryear."-".$i."-01 00:00:00";
                $date2 = $curryear."-".$i."-31 23:59:59";
                $collection = $this->_salesList
                    ->getCollection()
                    ->addFieldToFilter("seller_id", $this->_sellerId)
                    ->addFieldToFilter("order_id", ["neq"=>0]);
                $month = $collection->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$date1, "to"=>$date2]);
                $sum   = [];
                $temp  = 0;
                foreach ($collection as $record) {
                    $temp = $temp + $record->getActualSellerAmount();
                }
                $price = $temp;
                $data["values"][$i] = $price;
                if ($i != $currMonth)
                    $data["chxl"] = $data["chxl"].$monthsArr[$i]."|";
                else
                    $data["chxl"] = $data["chxl"].$monthsArr[$i];
            }
            return $data;
        }

        public function getMonthlySale() {
            $data           = [];
            $data["values"] = [];
            $data["chxl"]   = "0:|";
            $curryear       = date("Y");
            $currMonth      = date("m");
            $currDays       = date("d");
            for ($i=1; $i<=$currDays; ++$i) {
                $date1 = $curryear."-".$currMonth."-".$i." 00:00:00";
                $date2 = $curryear."-".$currMonth."-".$i." 23:59:59";
                $collection = $this->_salesList
                    ->getCollection()
                    ->addFieldToFilter("seller_id", $this->_sellerId)
                    ->addFieldToFilter("order_id", ["neq"=>0]);
                $month = $collection->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$date1, "to"=>$date2]);
                $sum = [];
                $temp = 0;
                foreach ($collection as $record) {
                    $temp = $temp + $record->getActualSellerAmount();
                }
                $price = $temp;
                if ($price * 1 && $i != $currDays) {
                    $data["values"][$i] = $price;
                    $data["chxl"] = $data["chxl"].$currMonth."/".$i."/".$curryear."|";
                } elseif ($i < 5 && $price * 1 == 0 && $i != $currDays) {
                    $data["values"][$i] = $price;
                    $data["chxl"] = $data["chxl"].$currMonth."/".$i."/".$curryear."|";
                }
                if ($i == $currDays) {
                    $data["values"][$i] = $price;
                    $data["chxl"] = $data["chxl"].$currMonth."/".$i."/".$curryear;
                }
            }
            return $data;
        }

        public function getWeeklySale()    {
            $data              = [];
            $data["values"]    = [];
            $data["chxl"]      = "0:|";
            $curryear          = date("Y");
            $currMonth         = date("m");
            $currDays          = date("d");
            $currWeekDay       = date("N");
            $currWeekStartDay  = $currDays - $currWeekDay;
            $currWeekEndDay    = $currWeekStartDay + 7;
            $currentDayOfMonth = date("j");
            if ($currWeekEndDay > $currentDayOfMonth) {
                $currWeekEndDay = $currentDayOfMonth;
            }
            for ($i=$currWeekStartDay+1; $i<=$currWeekEndDay; ++$i) {
                $date1 = $curryear."-".$currMonth."-".$i." 00:00:00";
                $date2 = $curryear."-".$currMonth."-".$i." 23:59:59";
                $collection = $this->_salesList
                    ->getCollection()
                    ->addFieldToFilter("seller_id", $this->_sellerId)
                    ->addFieldToFilter("order_id", ["neq"=>0]);
                $month = $collection->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$date1, "to"=>$date2]);
                $sum   = [];
                $temp  = 0;
                foreach ($collection as $record) {
                    $temp = $temp + $record->getActualSellerAmount();
                }
                $price = $temp;
                if ($i != $currWeekEndDay) {
                    $data["values"][$i] = $price;
                    $data["chxl"] = $data["chxl"].$currMonth."/".$i."/".$curryear."|";
                }
                if ($i == $currWeekEndDay) {
                    $data["values"][$i] = $price;
                    $data["chxl"] = $data["chxl"].$currMonth."/".$i."/".$curryear;
                }
            }
            return $data;
        }

        public function getDailySale()     {
            $data           = [];
            $data["values"] = [];
            $data["chxl"]   = "0:|";
            $curryear       = date("Y");
            $currMonth      = date("m");
            $currDays       = date("d");
            $currTime       = date("G");
            $arr            = [];
            for ($i=0; $i<=23; ++$i) {
                $date1 = $curryear."-".$currMonth."-".$currDays." ".$i.":00:00";
                $date2 = $curryear."-".$currMonth."-".$currDays." ".$i.":59:59";
                $collection = $this->_salesList
                    ->getCollection()
                    ->addFieldToFilter("seller_id", $this->_sellerId)
                    ->addFieldToFilter("order_id", ["neq"=>0]);
                $month = $collection->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$date1, "to"=>$date2]);
                $sum   = [];
                $temp  = 0;
                foreach ($collection as $record) {
                    $temp = $temp + $record->getActualSellerAmount();
                }
                $price = $temp;
                if ($i != 23) {
                    $data["values"][$i] = $price;
                    $data["chxl"] = $data["chxl"].$i."|";
                }
                if ($i == 23) {
                    $data["values"][$i] = $price;
                    $data["values"][14] = 100;
                    $data["chxl"] = $data["chxl"].$i;
                }
            }
            $newdata["values"] = [];
            if ($currTime - 21 >= 0) {
                $arr[0] = ($currTime - 21).":00 AM";
                $newdata["values"][0] = $data["values"][0]+$data["values"][1]+$data["'values"][2];
            }
            if ($currTime - 18 >= 0) {
                $arr[1] = ($currTime - 18).":00 AM";
                $newdata["values"][1] = $data["values"][3]+$data["values"][4]+$data["'values"][5];
            }
            if ($currTime - 15 >= 0) {
                $arr[2] = ($currTime - 15).":00 AM";
                $newdata["values"][2] = $data["values"][6]+$data["values"][7]+$data["'values"][8];
            }
            if ($currTime - 12 >= 0) {
                $arr[3] = ($currTime - 12).":00 AM";
                $newdata["values"][3] = $data["values"][9]+$data["values"][10]+$data["values"][11];
            }
            if ($currTime - 9 >= 0) {
                $arr[4] = ($currTime - 9).':00 PM';
                $newdata["values"][4] = $data["values"][12]+$data["values"][13]+$data["values"][14];
            }
            if ($currTime - 6 >= 0) {
                $arr[5] = ($currTime - 6).":00 PM";
                $newdata["values"][5] = $data["values"][15]+$data["values"][16]+$data["values"][17];
            }
            if ($currTime - 3 >= 0) {
                $arr[6] = ($currTime - 3).":00 PM";
                $newdata["values"][6] = $data["values"][18]+$data["values"][19]+$data["values"][20];
            }
            if ($currTime >= 0) {
                $arr[7] = ($currTime).":00 PM";
                $newdata["values"][7] = $data["values"][21]+$data["values"][22]+$data["values"][23];
            }
            unset($data["values"]);
            $data["values"] = $newdata["values"];
            $data["arr"] = $arr;
            return $data;
        }

        public function getSellerStatisticsCategoryGraph()   {
            $params        = ["cht"=>"p"];
            $getTopSaleCategories = $this->getTopSaleCategories();
            $params["chl"] = implode("|", $getTopSaleCategories["category_arr"]);
            $chcoArr       = [];
            for ($i=1; $i<=count($getTopSaleCategories["category_arr"]); ++$i) {
                array_push($chcoArr, $this->randString());
            }
            $params["chd"]  = "t:".implode(",", $getTopSaleCategories["percentage_arr"]);
            $params["chco"] = implode("|", $chcoArr);
            $params["chdl"] = implode("%|", $getTopSaleCategories["percentage_arr"]);
            $params["chdl"] = $params["chdl"]."%";
            $valueBuffer    = [];
// seller statistics graph size
            $params["chs"]  = $this->_width."x".$this->_height;
// return the encoded graph image url
            $getParamData      = urlencode(base64_encode(json_encode($params)));
            $encryptedHashData = $this->_marketplaceDashboardHelper->getChartEncryptedHashData($getParamData);
            $params            = [
                "param_data"     => $getParamData,
                "encrypted_data" => $encryptedHashData
            ];
            return $this->_urlHelper->getUrl("marketplace/account/dashboard_tunnel", ["_query"=>$params, "_secure"=>$this->_request->isSecure()]);
        }

        public function getTopSaleCategories()  {
            $collection = $this->_orderCollectionFactory->create()
                ->addFieldToFilter("seller_id", $this->_sellerId)
                ->addFieldToFilter("parent_item_id", ["null"=>"true"])
                ->getAllOrderProducts();
            $name       = "";
            $catArr     = [];
            $resultData = [];
            $totalOrderedProducts = 0;
            foreach ($collection as $coll) {
                $totalOrderedProducts = $totalOrderedProducts + $coll["qty"];
            }
            $collection = $this->_orderCollectionFactory->create()
                ->addFieldToFilter("seller_id", $this->_sellerId)
                ->addFieldToFilter("parent_item_id", ["null"=>"true"]);
            foreach ($collection as $coll) {
                $item    = $this->_orderItemRepository->get($coll["order_item_id"]);
                $product = $item->getProduct();
                if ($product) {
                    $productCategories = $product->getCategoryIds();
                    if (isset($productCategories[0])) {
                        if (!isset($catArr[$productCategories[0]])) {
                            $catArr[$productCategories[0]] = $coll["magequantity"];
                        } else {
                            $catArr[$productCategories[0]] = $catArr[$productCategories[0]] + $coll["magequantity"];
                        }
                    }
                }
            }
            $categoryArr   = [];
            $percentageArr = [];
            foreach ($catArr as $key => $value) {
                $categoryArr[$key]   = $this->_categoryRepository->get($key)->getName();
                $percentageArr[$key] = round((($value * 100) / $totalOrderedProducts), 2);
            }
            $resultData["category_arr"]   = $categoryArr;
            $resultData["percentage_arr"] = $percentageArr;
            return $resultData;
        }

        public function getCollection()     {
            $orderids   = $this->getOrderIdsArray($this->_sellerId, "");
            $ids        = $this->getEntityIdsArray($orderids);
            $collection = $this->_orderCollectionFactory->create()
                ->addFieldToSelect("*")
                ->addFieldToFilter("entity_id", ["in"=>$ids])
                ->setOrder("created_at", "desc")
                ->setPageSize(5);
            return $collection;
        }

        public function getOrderIdsArray($customerId="", $filterOrderstatus="") {
            $orderids         = [];
            $collectionOrders = $this->_orderCollectionFactory->create()
                ->addFieldToFilter("seller_id", $this->_sellerId)
                ->addFieldToSelect("order_id")
                ->distinct(true);
            foreach ($collectionOrders as $collectionOrder) {
                $tracking = $this->getOrderinfo($collectionOrder->getOrderId());
                if ($tracking) {
                    if ($filterOrderstatus) {
                        if ($tracking->getIsCanceled()) {
                            if ($filterOrderstatus == "canceled")
                                array_push($orderids, $collectionOrder->getOrderId());
                        } else {
                            $tracking = $this->_orderRepository->create($collectionOrder->getOrderId());
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

        public function getOrderinfo($orderId="")    {
            $data  = [];
            $model = $this->_marketplaceOrders
                ->getCollection()
                ->addFieldToFilter("seller_id", $this->_sellerId)
                ->addFieldToFilter("order_id", $orderId);
            $salesOrder = $this->_marketplaceOrderResourceCollection->getTable("sales_order");
            $model->getSelect()->join($salesOrder." as so", "main_table.order_id=so.entity_id", ["order_approval_status"=>"order_approval_status"])
            ->where("so.order_approval_status=1");
            foreach ($model as $tracking) {
                $data = $tracking;
            }
            return $data;
        }

        public function getMainOrder($orderId)  {
            $collection = $this->_orders
                ->getCollection()
                ->addFieldToFilter("entity_id", $orderId);
            foreach ($collection as $res) {
                return $res;
            }
            return [];
        }

        public function getpronamebyorder($orderId)     {
            $collection = $this->_orderCollectionFactory->create()
                ->addFieldToFilter("seller_id", $this->_sellerId)
                ->addFieldToFilter("order_id", $orderId);
            $productNames = [];
            foreach ($collection as $res) {
                $eachProductName              = [];
                $item                         = $this->_orderItemRepository->get($res->getOrderItemId());
                $eachProductName["qty"]       = intval($res["magequantity"]);
                $eachProductName["name"]      = $res["magepro_name"];
                $eachProductName["productId"] = 0;
                if ($item->getProduct())
                    $eachProductName["productId"] = $item->getProduct()->getId();
                $productNames[] = $eachProductName;
            }
            return $productNames;
        }

        public function getPricebyorder($orderId)   {
            $collection = $this->_orderCollectionFactory->create()
                ->addFieldToFilter("seller_id", $this->_sellerId)
                ->getPricebyorderData();
            $name = "";
            foreach ($collection as $coll) {
                if ($coll->getOrderId() == $orderId) {
                    return $coll->getTotal();
                }
            }
        }

        public function getOrderedPricebyorder($order, $basePrice)  {
            $currentCurrencyCode = $order->getOrderCurrencyCode();
            $baseCurrencyCode = $order->getBaseCurrencyCode();
            $allowedCurrencies = $this->_marketplaceHelper->getConfigAllowCurrencies();
            $rates = $this->_marketplaceHelper->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
            if (empty($rates[$currentCurrencyCode])) {
                $rates[$currentCurrencyCode] = 1;
            }
            return $basePrice * $rates[$currentCurrencyCode];
        }

        public function getReviewcollection($value="")    {
            $collection = $this->_marketplaceFeedbackModel
                ->getCollection()
                ->addFieldToFilter("seller_id", $this->_sellerId)
                ->addFieldToFilter("status", 1)
                ->setOrder("created_at", "desc")
                ->setPageSize(5)
                ->setCurPage(1);
            return $collection;
        }

    }