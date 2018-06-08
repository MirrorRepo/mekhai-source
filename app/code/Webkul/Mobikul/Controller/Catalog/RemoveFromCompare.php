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

    namespace Webkul\Mobikul\Controller\Catalog;

    class RemoveFromCompare extends AbstractCatalog    {

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
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $productId   = $this->_helper->validate($wholeData, "productId")  ? $wholeData["productId"]  : 0;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        if ($productId != 0) {
                            try {
                                $product = $this->_productRepository->getById($productId, false, $storeId);
                            } catch (NoSuchEntityException $e) {
                                $product = null;
                            }
                            if ($product) {
                                $item = $this->_compareItemFactory->create();
                                if ($customerId != 0)
                                    $item->setCustomerId($customerId);
                                else
                                    $item->addVisitorId($this->_customerVisitor->getId());
                                $item->loadByProduct($product);
                                $helper = $this->_objectManager->get("Magento\Catalog\Helper\Product\Compare");
                                if ($item->getId()) {
                                    $item->delete();
                                    $productName = $this->_objectManager->get("Magento\Framework\Escaper")->escapeHtml($product->getName());
                                    $returnArray["message"] = __("You removed product %1 from the comparison list.", $productName);
                                    $this->_eventManager->dispatch("catalog_product_compare_remove_product", ["product"=>$item]);
                                    $helper->calculate();
                                }
                            }
                        }
                        $returnArray["success"] = true;
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