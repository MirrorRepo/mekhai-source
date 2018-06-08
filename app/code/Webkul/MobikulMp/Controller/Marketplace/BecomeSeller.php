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

    class BecomeSeller extends AbstractMarketplace    {

        public function execute()   {
            $returnArray              = [];
            $returnArray["authKey"]   = "";
            $returnArray["success"]   = false;
            $returnArray["message"]   = "";
            $returnArray["isPending"] = false;
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
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 0;
                        $shopUrl     = $this->_helper->validate($wholeData, "shopUrl")    ? $wholeData["shopUrl"]    : "";
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $status      = $this->_marketplaceHelper->getIsPartnerApproval() ? 0 : 1;
                        if($status == 0)
                            $returnArray["isPending"] = true;
                        $model = $this->_seller->getCollection()->addFieldToFilter("shop_url", $shopUrl);
                        if (!count($model)) {
                            $seller     = $this->_seller;
                            $collection = $this->_seller->getCollection()->addFieldToFilter("seller_id", $customerId);
                            foreach ($collection as $value){
                                $seller = $this->_seller->load($value->getId());
                            }
                            $seller->setData("is_seller", $status);
                            $seller->setData("shop_url", $shopUrl);
                            $seller->setData("seller_id", $customerId);
                            $seller->setCreatedAt($this->_date->gmtDate());
                            $seller->setUpdatedAt($this->_date->gmtDate());
                            $seller->setAdminNotification(1);
                            $seller->save();
                            $returnArray["message"] = __("Profile information was successfully saved");
                            $returnArray["success"] = true;
                        } else {
                            $returnArray["message"] = __("Shop URL already exist please set another.");
                        }
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