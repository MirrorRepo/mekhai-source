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
    use Magento\Framework\Exception\LocalizedException;

    class WishlistToCart extends AbstractCustomer    {

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
                        $qty         = $this->_helper->validate($wholeData, "qty")        ? $wholeData["qty"]        : 1;
                        $itemId      = $this->_helper->validate($wholeData, "itemId")     ? $wholeData["itemId"]     : 0;
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $productId   = $this->_helper->validate($wholeData, "productId")  ? $wholeData["productId"]  : 0;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $item = $this->_objectManager->create("\Magento\Wishlist\Model\Item")->load($itemId);
                        if (!$item || !$item->getId())
                            throw new LocalizedException(__("Item id is invalid"));
                        $wishlist = $this->_wishlistProvider->load($item->getWishlistId());
                        if ($wishlist->getCustomerId() != $customerId)
                            throw new LocalizedException(__("Invalid data."));
                        $options = $this->_objectManager->create("\Magento\Wishlist\Model\Item\Option")->getCollection()->addItemFilter([$itemId]);
                        $item->setOptions($options->getOptionsByItem($itemId));
                        $buyRequest = $this->_objectManager->create("\Magento\Catalog\Helper\Product")->addParamsToBuyRequest(
                            ["item"=>$itemId, "qty"=>[$itemId=>$qty]],
                            ["current_config"=>$item->getBuyRequest()]
                        );
                        $item->mergeBuyRequest($buyRequest);
                        $quoteCollection = $this->_objectManager->create("\Magento\Quote\Model\Quote")->getCollection()
                            ->addFieldToFilter("customer_id", $customerId)
                            ->addFieldToFilter("is_active", 1)
                            ->addOrder("updated_at", "DESC");
                        $quote = $quoteCollection->getFirstItem();
                        $this->_cart->setQuote($quote);
                        $item->addToCart($this->_cart, true);
                        $this->_cart->save()->getQuote()->collectTotals();
                        $wishlist->save();
                        $returnArray["cartCount"] = $this->_cart->getQuote()->getItemsQty() * 1;
                        $returnArray["success"] = true;
                        $returnArray["message"] = __("Product(s) has successfully moved to cart.");
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
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_helper->printLog($e);
                $returnArray["message"] = $e->getCustomMessage();
                $this->getResponse()->setBody(Mage::helper("core")->jsonEncode($returnArray));
                return;
            } catch (\Exception $e) {
                $returnArray["message"] = $e->getMessage();
                $this->_helper->printLog($returnArray);
                return $this->getJsonResponse($returnArray);
            }
        }

    }