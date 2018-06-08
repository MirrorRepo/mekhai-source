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

    class DownloadTransactionList extends AbstractMarketplace    {

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
                        $dateTo        = $this->_helper->validate($wholeData, "dateTo")        ? $wholeData["dateTo"]        : "";
                        $storeId       = $this->_helper->validate($wholeData, "storeId")       ? $wholeData["storeId"]       : 0;
                        $dateFrom      = $this->_helper->validate($wholeData, "dateFrom")      ? $wholeData["dateFrom"]      : "";
                        $pageNumber    = $this->_helper->validate($wholeData, "pageNumber")    ? $wholeData["pageNumber"]    : 1;
                        $transactionId = $this->_helper->validate($wholeData, "transactionId") ? $wholeData["transactionId"] : "";
                        $environment   = $this->_emulate->startEnvironmentEmulation($storeId);
                        $transactionCollection = $this->_transactionCollectionFactory->create()
                            ->addFieldToSelect("*")
                            ->addFieldToFilter("seller_id", $customerId);
                        $to   = null;
                        $from = null;
                        if ($dateTo) {
                            $todate = date_create($dateTo);
                            $to = date_format($todate, "Y-m-d 23:59:59");
                        }
                        if ($dateFrom) {
                            $fromdate = date_create($dateFrom);
                            $from = date_format($fromdate, "Y-m-d H:i:s");
                        }
                        if ($transactionId) {
                            $transactionCollection->addFieldToFilter("transaction_id", ["like"=>"%".$transactionId."%"]);
                        }
                        $transactionCollection->addFieldToFilter("created_at", ["datetime"=>true, "from"=>$from, "to"=>$to]);
                        $transactionCollection->setOrder("created_at", "desc");
                        $transactionList = [];
                        foreach ($transactionCollection as $transaction) {
                            $eachTransaction                        = [];
                            $eachTransaction["Date"]                = $this->_viewTemplate->formatDate($transaction->getCreatedAt());
                            $eachTransaction["Transaction Id"]      = $transaction->getTransactionId();
                            $eachTransaction["Comment Message"]     = __("None");
                            if ($transaction->getCustomNote()) {
                                $eachTransaction["Comment Message"] = $transaction->getCustomNote();
                            }
                            $eachTransaction["Transaction Amount"]  = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($transaction->getTransactionAmount()));
                            $transactionList[] = $eachTransaction;
                        }
                        if (isset($transactionList[0])) {
                            header("Content-Type: text/csv");
                            header("Content-Disposition: attachment; filename=transactionlist.csv");
                            header("Pragma: no-cache");
                            header("Expires: 0");
                            $outstream = fopen("php://output", "w");
                            fputcsv($outstream, array_keys($transactionList[0]));
                            foreach ($transactionList as $result) {
                                fputcsv($outstream, $result);
                            }
                            fclose($outstream);
                            $this->_emulate->stopEnvironmentEmulation($environment);
                            return;
                        }
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