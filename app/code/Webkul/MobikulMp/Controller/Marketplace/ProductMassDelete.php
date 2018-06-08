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

    class ProductMassDelete extends AbstractMarketplace    {

        public function execute()   {
            $returnArray            = [];
            $returnArray["authKey"] = "";
            $returnArray["success"] = false;
            $returnArray["message"] = "";
            try {
                $wholeData       = $this->getRequest()->getPostValue();
                $this->_headers  = $this->getRequest()->getHeaders();
                $this->_helper->log(__CLASS__, "logClass", $wholeData);
                $this->_helper->log($wholeData, "logParams", $wholeData);
                $this->_helper->log($this->_headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 0;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $productIds  = $this->_helper->validate($wholeData, "productIds") ? $wholeData["productIds"] : "[]";
                        $productIds  = $this->_jsonHelper->jsonDecode($productIds);
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $this->_coreRegistry->register("isSecureArea", 1);
                        $deletedIds = [];
                        $sellerProducts = $this->_sellerProductCollectionFactory
                            ->create()
                            ->addFieldToFilter("mageproduct_id", ["in" => $productIds])
                            ->addFieldToFilter("seller_id", $customerId);
                        foreach ($sellerProducts as $sellerProduct) {
                            array_push($deletedIds, $sellerProduct["mageproduct_id"]);
                            $this->_eventManager->dispatch("mp_delete_product", [$sellerProduct["mageproduct_id"]]);
                            $sellerProduct->delete();
                        }
                        $mageProducts = $this->_productCollectionFactory->create()->addFieldToFilter("entity_id", ["in"=>$deletedIds]);
                        foreach ($mageProducts as $mageProduct) {
                            $mageProduct->delete();
                        }
                        $unauthIds = array_diff($productIds, $deletedIds);
                        $this->_coreRegistry->unregister("isSecureArea");
                        if (!count($unauthIds))
                            $returnArray["message"] = __("Products are successfully deleted from your account.");
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