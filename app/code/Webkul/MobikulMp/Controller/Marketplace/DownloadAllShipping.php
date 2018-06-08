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
    use Magento\Framework\App\Filesystem\DirectoryList;

    class DownloadAllShipping extends AbstractMarketplace    {

        public function execute()   {
            $returnArray            = [];
            $returnArray["authKey"] = "";
            $returnArray["message"] = "";
            try {
                $wholeData       = $this->getRequest()->getParams();
                $this->_headers  = $this->getRequest()->getHeaders();
                $this->_helper->log(__CLASS__, "logClass", $wholeData);
                $this->_helper->log($wholeData, "logParams", $wholeData);
                $this->_helper->log($this->_headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $customerId  = $this->getRequest()->getHeader("customerId");
                    $authData    = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $dateTo      = $this->_helper->validate($wholeData, "dateTo")     ? $wholeData["dateTo"]     : "";
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 0;
                        $dateFrom    = $this->_helper->validate($wholeData, "dateFrom")   ? $wholeData["dateFrom"]   : "";
                        // $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $customer    = $this->_customer->load($customerId);
                        $to          = date_format(date_create($dateTo), "Y-m-d H:i:s");
                        $from        = date_format(date_create($dateFrom), "Y-m-d H:i:s");
                        $shipmentIds = [];
                        $this->_customerSession->setCustomer($customer);
                        $this->_customerSession->setCustomerId($customerId);
                        try {
                            $collection = $this->_marketplaceSaleList
                                ->getCollection()
                                ->addFieldToFilter("seller_id", $customerId)
                                ->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$from, "to"=>$to])
                                ->addFieldToSelect("order_id")
                                ->distinct(true);
                            $shippingColl = $this->_marketplaceOrders
                                ->getCollection()
                                ->addFieldToFilter("order_id", $collection->getData())
                                ->addFieldToFilter("seller_id", $customerId);
                            $shipmentIds = $shippingColl->getData();

                            if (!empty($shipmentIds)) {
                                $shipments = $this->_shipmentCollection
                                    ->addAttributeToSelect("*")
                                    ->addAttributeToFilter("entity_id", ["in"=>$shipmentIds])
                                    ->load();
                                if (!$shipments->getSize()) {
                                    $returnArray["message"] = __("There are no printable documents related to selected date range.");
                                    $this->_emulate->stopEnvironmentEmulation($environment);
                                    $this->_helper->log($returnArray, "logResponse", $wholeData);
                                    return $this->getJsonResponse($returnArray);
                                }
                                $pdf = $this->_shipmentPdf->getPdf($shipments);
                                $date = $this->_dateTime->date("Y-m-d_H-i-s");
                                return $this->_fileFactory->create("packingslip".$date.".pdf", $pdf->render(), DirectoryList::VAR_DIR, "application/pdf");
                            } else {
                                $returnArray["message"] = __("There are no printable documents related to selected date range.");
                            }
                        } catch (\Magento\Framework\Exception\LocalizedException $e) {
                            $returnArray["message"] = $e->getMessage();
                        } catch (\Exception $e) {
                            $returnArray["message"] = __("We can't print the shipment right now.");
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