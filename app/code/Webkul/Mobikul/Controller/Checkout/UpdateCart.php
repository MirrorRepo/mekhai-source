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

    namespace Webkul\Mobikul\Controller\Checkout;

    class UpdateCart extends AbstractCheckout   {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["cartCount"]    = 0;
            $returnArray["responseCode"] = 0;
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
                        $quoteId     = $this->_helper->validate($wholeData, "quoteId")    ? $wholeData["quoteId"]    : 0;
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $itemIds     = $this->_helper->validate($wholeData, "itemIds")    ? $wholeData["itemIds"]    : "[]";
                        $itemQtys    = $this->_helper->validate($wholeData, "itemQtys")   ? $wholeData["itemQtys"]   : "[]";
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $itemIds     = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($itemIds);
                        $itemQtys    = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($itemQtys);
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $quote       = new \Magento\Framework\DataObject();
                        if ($customerId != 0) {
                            $quoteCollection = $this->_quoteFactory->create()
                                ->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                // ->addFieldToFilter("store_id", $storeId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if ($quoteId != 0)
                            $quote = $this->_quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                        $cartData = [];
                        foreach($itemIds as $key=>$itemId)
                            $cartData[$itemId] = ["qty"=>$itemQtys[$key]];
                        $filter = new \Magento\Framework\Filter\LocalizedToNormalized(["locale"=>$this->_objectManager->get("\Magento\Framework\Locale\Resolver")->getLocale()]);
                        foreach ($cartData as $index=>$eachData) {
                            if (isset($eachData["qty"]))
                                $cartData[$index]["qty"] = $filter->filter(trim($eachData["qty"]));
                        }
                        foreach ($cartData as $itemId=>$itemInfo) {
                            if (!isset($itemInfo["qty"]))
                                continue;
                            $qty = (float) $itemInfo["qty"];
                            $quoteItem = $quote->getItemById($itemId);
                            if (!$quoteItem)
                                continue;
                            $product = $quoteItem->getProduct();
                            if (!$product)
                                continue;
                            $stockItem = $this->_stockRegistry->getStockItem($product->getId());
                            if (!$stockItem)
                                continue;
                            $quoteItem->setQty($qty)->save();
                        }
                        $quote->getShippingAddress()->setCollectShippingRates(true);
                        $quote->collectTotals()->save();
                        $returnArray["success"]   = true;
                        $returnArray["cartCount"] = $quote->getItemsQty()*1;
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