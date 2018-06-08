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

    class TransactionList extends AbstractMarketplace    {

        public function execute()   {
            $returnArray                               = [];
            $returnArray["authKey"]                    = "";
            $returnArray["success"]                    = false;
            $returnArray["message"]                    = "";
            $returnArray["totalCount"]                 = 0;
            $returnArray["transactionList"]            = [];
            $returnArray["remainingTransactionAmount"] = "";
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
                        $dateTo        = $this->_helper->validate($wholeData, "dateTo")        ? $wholeData["dateTo"]        : "";
                        $storeId       = $this->_helper->validate($wholeData, "storeId")       ? $wholeData["storeId"]       : 0;
                        $dateFrom      = $this->_helper->validate($wholeData, "dateFrom")      ? $wholeData["dateFrom"]      : "";
                        $customerId    = $this->_helper->validate($wholeData, "customerId")    ? $wholeData["customerId"]    : 0;
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
                        if($pageNumber >= 1) {
                            $returnArray["totalCount"] = $transactionCollection->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $transactionCollection->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        $transactionList = [];
                        foreach($transactionCollection as $transaction){
                            $eachTransaction                  = [];
                            $eachTransaction["id"]            = $transaction->getId();
                            $eachTransaction["date"]          = $this->_viewTemplate->formatDate($transaction->getCreatedAt());
                            $eachTransaction["amount"]        = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($transaction->getTransactionAmount()));
                            $eachTransaction["comment"]       = __("None");
                            $eachTransaction["transactionId"] = $transaction->getTransactionId();
                            if($transaction->getCustomNote()){
                                $eachTransaction["comment"]   = $transaction->getCustomNote();
                            }
                            $transactionList[]                = $eachTransaction;
                        }
                        $returnArray["transactionList"] = $transactionList;
                        $collection = $this->_saleperPartner->getCollection()->addFieldToFilter("seller_id", $customerId);
                        $total = 0;
                        foreach ($collection as $key) {
                            $total = $key->getAmountRemain();
                        }
                        if ($total < 0) {
                            $total = 0;
                        }
                        $returnArray["remainingTransactionAmount"] = $this->_helperCatalog->stripTags($this->_checkoutHelper->formatPrice($total));
                        $returnArray["success"] = true;
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