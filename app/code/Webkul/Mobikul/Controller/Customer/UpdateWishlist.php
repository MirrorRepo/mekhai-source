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

    class UpdateWishlist extends AbstractCustomer   {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
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
                        $itemData       = $this->_helper->validate($wholeData, "itemData")   ? $wholeData["itemData"]   : "[]";
                        $customerId     = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $itemData       = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($itemData);
                        $wishlist       = $this->_objectManager->create("\Magento\Wishlist\Model\Wishlist")->loadByCustomerId($customerId, true);
                        $wishlistHelper = $this->_objectManager->create("\Magento\Wishlist\Helper\Data");
                        $updatedItems   = 0;
                        foreach ($itemData as $eachItem) {
                            $item = $this->_objectManager->create("\Magento\Wishlist\Model\Item")->load($eachItem["id"]);
                            if ($item->getWishlistId() != $wishlist->getId())
                                continue;
                            $description = "";
                            if(isset($eachItem["description"]))
                                $description = (string)$eachItem["description"];
                            if ($description == $wishlistHelper->defaultCommentString())
                                $description = "";
                            elseif (!strlen($description))
                                $description = $item->getDescription();
                            $qty = null;
                            if (isset($eachItem["qty"]))
                                $qty = $eachItem["qty"];
                            if (is_null($qty)) {
                                $qty = $item->getQty();
                                if (!$qty)
                                    $qty = 1;
                            } elseif (0 == $qty) {
                                try {
                                    $item->delete();
                                } catch (\Exception $e) {
                                    $returnArray["message"] =__("Can't delete item from wishlist");
                                    return $this->getJsonResponse($returnArray);
                                }
                            }
                            if (($item->getDescription() == $description) && ($item->getQty() == $qty))
                                continue;
                            try {
                                $item->setDescription($description)->setQty($qty)->save();
                                ++$updatedItems;
                            } catch (\Exception $e) {
                                $returnArray["message"] =__("Can't save description %1", $this->_helperCatalog->escapeHtml($description));
                                return $this->getJsonResponse($returnArray);
                            }
                        }
                        if ($updatedItems) {
                            try {
                                $wishlist->save();
                                $wishlistHelper->calculate();
                            } catch (\Exception $e) {
                                $returnArray["message"] = __("Can't update wishlist");
                                return $this->getJsonResponse($returnArray);
                            }
                        }
                        $returnArray["success"] = true;
                        $returnArray["message"] = __("Wishlist updated successfully");
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