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

    class WishlistFromCart extends AbstractCheckout     {

        public function execute()   {
            $returnArray                                  = [];
            $returnArray["authKey"]                       = "";
            $returnArray["success"]                       = false;
            $returnArray["message"]                       = "";
            $returnArray["cartCount"]                     = 0;
            $returnArray["responseCode"]                  = 0;
            $returnArray["tax"]["title"]                  = "";
            $returnArray["tax"]["value"]                  = "";
            $returnArray["subtotal"]["title"]             = "";
            $returnArray["subtotal"]["value"]             = "";
            $returnArray["discount"]["title"]             = "";
            $returnArray["discount"]["value"]             = "";
            $returnArray["shipping"]["title"]             = "";
            $returnArray["shipping"]["value"]             = "";
            $returnArray["grandtotal"]["title"]           = "";
            $returnArray["grandtotal"]["value"]           = "";
            $returnArray["tax"]["unformatedValue"]        = 0.0;
            $returnArray["subtotal"]["unformatedValue"]   = 0.0;
            $returnArray["discount"]["unformatedValue"]   = 0.0;
            $returnArray["shipping"]["unformatedValue"]   = 0.0;
            $returnArray["grandtotal"]["unformatedValue"] = 0.0;
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
                        $itemId      = $this->_helper->validate($wholeData, "itemId")     ? $wholeData["itemId"]     : 0;
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $quoteCollection = $this->_quoteFactory->create()->getCollection()
                            ->addFieldToFilter("customer_id", $customerId)
                            // ->addFieldToFilter("store_id", $storeId)
                            ->addFieldToFilter("is_active", 1)
                            ->addOrder("updated_at", "DESC");
                        $quote      = $quoteCollection->getFirstItem();
                        $wishlist   = $this->_objectManager->create("\Magento\Wishlist\Model\Wishlist")->loadByCustomerId($customerId, true);
                        $item       = $quote->getItemById($itemId);
                        $productId  = $item->getProductId();
                        $buyRequest = $item->getBuyRequest();
                        $wishlist->addNewItem($productId, $buyRequest);
                        $quote->removeItem($itemId);
                        $quote->getShippingAddress()->setCollectShippingRates(true);
                        $quote->collectTotals()->save();
                        if($quote->getItemsQty()*1 > 0){
                            $totals = [];
                            if ($quote->isVirtual())
                                $totals = $quote->getBillingAddress()->getTotals();
                            else
                                $totals = $quote->getShippingAddress()->getTotals();
                            $catalogHelper  = $this->_helperCatalog;
                            $checkoutHelper = $this->_objectManager->get("\Magento\Checkout\Helper\Data");
                            $subtotal = []; $discount = []; $grandtotal = [];$shipping = [];
                            if(isset($totals["subtotal"])){
                                $subtotal                         = $totals["subtotal"];
                                $returnArray["subtotal"]["title"] = $subtotal->getTitle();
                                $returnArray["subtotal"]["value"] = $catalogHelper->stripTags($checkoutHelper->formatPrice($subtotal->getValue()));
                                $returnArray["subtotal"]["unformatedValue"] = $subtotal->getValue();
                            }
                            if(isset($totals["discount"])){
                                $discount                         = $totals["discount"];
                                $returnArray["discount"]["title"] = $discount->getTitle();
                                $returnArray["discount"]["value"] = $catalogHelper->stripTags($checkoutHelper->formatPrice($discount->getValue()));
                                $returnArray["discount"]["unformatedValue"] = $discount->getValue();
                            }
                            if(isset($totals["shipping"])){
                                $shipping                         = $totals["shipping"];
                                $returnArray["shipping"]["title"] = $shipping->getTitle();
                                $returnArray["shipping"]["value"] = $catalogHelper->stripTags($checkoutHelper->formatPrice($shipping->getValue()));
                                $returnArray["shipping"]["unformatedValue"] = $shipping->getValue();
                            }
                            if(isset($totals["tax"])){
                                $tax                         = $totals["tax"];
                                $returnArray["tax"]["title"] = $tax->getTitle();
                                $returnArray["tax"]["value"] = $catalogHelper->stripTags($checkoutHelper->formatPrice($tax->getValue()));
                                $returnArray["tax"]["unformatedValue"] = $tax->getValue();
                            }
                            if(isset($totals["grand_total"])){
                                $grandtotal                         = $totals["grand_total"];
                                $returnArray["grandtotal"]["title"] = $grandtotal->getTitle();
                                $returnArray["grandtotal"]["value"] = $catalogHelper->stripTags($checkoutHelper->formatPrice($grandtotal->getValue()));
                                $returnArray["grandtotal"]["unformatedValue"] = $grandtotal->getValue();
                            }
                        }
                        // $customer   = $this->_customerFactory->create()->load($customerId);
                        // $collection = $wishlist->getItemCollection()->setInStockFilter(true);
                        // if($this->_helper->getConfigData("wishlist/wishlist_link/use_qty"))
                        //     $count  = $collection->getItemsQty();
                        // else
                        //     $count  = $collection->getSize();
                        // $session    = $this->_customerSession->setCustomer($customer)
                        //     ->setWishlistDisplayType($this->_helper->getConfigData("wishlist/wishlist_link/use_qty"))
                        //     ->setDisplayOutOfStockProducts($this->_helper->getStoreConfig("cataloginventory/options/show_out_of_stock"))
                        //     ->setWishlistItemCount($count);
                        $wishlist->save();
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