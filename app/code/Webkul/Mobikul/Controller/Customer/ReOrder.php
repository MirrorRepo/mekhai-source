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

    class ReOrder extends AbstractCustomer  {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["success"]      = false;
            $returnArray["message"]      = __("Product(s) has been added to cart.");
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
                        $storeId     = $this->_helper->validate($wholeData, "storeId")     ? $wholeData["storeId"]     : 1;
                        $customerId  = $this->_helper->validate($wholeData, "customerId")  ? $wholeData["customerId"]  : 0;
                        $incrementId = $this->_helper->validate($wholeData, "incrementId") ? $wholeData["incrementId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $order       = $this->_objectManager->create("\Magento\Sales\Model\Order")->loadByIncrementId($incrementId);
                        if($order->getCustomerId() != $customerId) {
                            $returnArray["message"] = __("Invalid Order.");
                            return $this->getJsonResponse($returnArray);
                        }
                        $outOfStockSignal = false;
                        $outOfStockItems  = [];
                        $quoteCollection  = $this->_objectManager->create("\Magento\Quote\Model\Quote")->getCollection()
                            ->addFieldToFilter("customer_id", $customerId)
                            ->addFieldToFilter("is_active", 1)
                            ->addOrder("updated_at", "DESC");
                        $quote = $quoteCollection->getFirstItem();
                        $quoteId = $quote->getId();
                        if ($quote->getId() < 0 || !$quoteId) {
                            $quote = $this->_quoteFactory->create()
                                ->setStoreId($storeId)
                                ->setIsActive(true)
                                ->setIsMultiShipping(false)
                                ->save();
                            $quoteId = (int) $quote->getId();
                            $customer = $this->_customerRepositoryInterface->getById($customerId);
                            $quote->assignCustomer($customer);
                            $quote->setCustomer($customer);
                            $quote->getBillingAddress();
                            $quote->getShippingAddress()->setCollectShippingRates(true);
                            $quote->collectTotals()->save();
                        }
                        $cart = $this->_objectManager->create("\Magento\Checkout\Model\CartFactory")->create()->setQuote($quote);
                        foreach ($order->getItemsCollection() as $item) {
                            if (is_null($item->getParentItem())) {
                                $productId = $item->getProductId();
                                $product   = $this->_objectManager->create("\Magento\Catalog\Model\Product")->setStoreId($storeId)->load($productId);
                                $info      = $item->getProductOptionByCode("info_buyRequest");
                                $stockItem = new \Magento\Framework\DataObject();
                                if($product->getTypeId() == "configurable")     {
                                    $sku = $item->getSku();
                                    $configurableProduct = $this->_productRepository->get($sku);
                                    $stockItem           = $this->_stockRegistry->getStockItem($configurableProduct->getId())->getQty();
                                }
                                else
                                    $stockItem = $this->_stockRegistry->getStockItem($productId)->getQty();;
                                if ($stockItem < $item->getQtyOrdered()) {
                                    $outOfStockItems[] = $item->getName();
                                    $outOfStockSignal  = true;
                                    continue;
                                }
                                $cart->addOrderItem($item);
                            }
                        }
                        $cart->save();
                        $returnArray["cartCount"]   = $quote->getItemsQty() * 1;
                        if ($outOfStockSignal) {
                            $outOfStockMessage      = implode(", ", $outOfStockItems);
                            $returnArray["message"] = __("Following Products")."(".$outOfStockMessage.") ".__("can't be added to cart as they are Out Of Stock");
                            return $this->getJsonResponse($returnArray);
                        }
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
                $returnArray["message"] = $e->getMessage();
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }