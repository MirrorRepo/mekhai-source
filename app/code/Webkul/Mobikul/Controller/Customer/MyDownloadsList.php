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

    class MyDownloadsList extends AbstractCustomer      {

        public function execute()   {
            $returnArray                  = [];
            $returnArray["authKey"]       = "";
            $returnArray["success"]       = false;
            $returnArray["message"]       = "";
            $returnArray["totalCount"]    = 0;
            $returnArray["responseCode"]  = 0;
            $returnArray["downloadsList"] = [];
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
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $pageNumber  = $this->_helper->validate($wholeData, "pageNumber") ? $wholeData["pageNumber"] : 1;
                        $purchased   = $this->_objectManager
                            ->create("\Magento\Downloadable\Model\ResourceModel\Link\Purchased\Collection")
                            ->addFieldToFilter("customer_id", $customerId)
                            ->addOrder("created_at", "DESC");
                        $purchasedIds       = [];
                        foreach ($purchased as $item)
                            $purchasedIds[] = $item->getId();
                        if (empty($purchasedIds))
                            $purchasedIds   = [null];
                        $purchasedItems     = $this->_objectManager
                            ->create("\Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection")
                            ->addFieldToFilter("purchased_id", ["in"=>$purchasedIds])
                            ->addFieldToFilter("status", ["nin"=>[\Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING_PAYMENT, \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PAYMENT_REVIEW]])
                            ->setOrder("item_id", "desc");
// Applying pagination //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if($pageNumber >= 1) {
                            $returnArray["totalCount"] = $purchasedItems->getSize();
                            $pageSize = $this->_helperCatalog->getPageSize();
                            $purchasedItems->setPageSize($pageSize)->setCurPage($pageNumber);
                        }
                        foreach ($purchasedItems as $item)
                            $item->setPurchased($purchased->getItemById($item->getPurchasedId()));
// Creating Downloads List //////////////////////////////////////////////////////////////////////////////////////////////////////
                        $downloadsList = [];
                        $block = $this->_objectManager->create("\Magento\Downloadable\Block\Customer\Products\ListProducts");
                        foreach ($purchasedItems as $downloads) {
                            $eachDownloads                       = [];
                            $eachDownloads["incrementId"]        = $incrementId = $downloads->getPurchased()->getOrderIncrementId();
                            $order = $this->_objectManager->create("\Magento\Sales\Model\Order")->loadByIncrementId($incrementId);
                            if ($order->getRealOrderId() > 0){
                                $eachDownloads["isOrderExist"]   = true;
                                $eachDownloads["message"]        = "";
                            }
                            else {
                                $eachDownloads["isOrderExist"]   = false;
                                $eachDownloads["message"]        = __("Sorry This Order Does not Exist!!");
                            }
                            $eachDownloads["hash"]               = $downloads->getLinkHash();
                            $eachDownloads["date"]               = $block->formatDate($downloads->getPurchased()->getCreatedAt());
                            $eachDownloads["proName"]            = $this->_helperCatalog->stripTags($downloads->getPurchased()->getProductName());
                            $eachDownloads["status"]             = __(ucfirst($downloads->getStatus()));
                            $eachDownloads["state"]              = $order->getState();
                            $eachDownloads["remainingDownloads"] = $block->getRemainingDownloads($downloads);
                            $canReorder                          = false;
                            if($this->canReorder($order))
                                $canReorder                      = $this->canReorder($order);
                            $eachDownloads["canReorder"]         = $canReorder;
                            $downloadsList[]                     = $eachDownloads;
                        }
                        $returnArray["downloadsList"]            = $downloadsList;
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