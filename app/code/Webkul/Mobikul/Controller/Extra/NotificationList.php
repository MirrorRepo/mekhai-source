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

    namespace Webkul\Mobikul\Controller\Extra;

    class NotificationList extends AbstractMobikul   {

        public function execute()   {
            $returnArray                     = [];
            $returnArray["success"]          = false;
            $returnArray["authKey"]          = "";
            $returnArray["message"]          = "";
            $returnArray["responseCode"]     = 0;
            $returnArray["notificationList"] = [];
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
                        $width       = $this->_helper->validate($wholeData, "width")   ? $wholeData["width"]   : 1000;
                        $storeId     = $this->_helper->validate($wholeData, "storeId") ? $wholeData["storeId"] : 1;
                        $mFactor     = $this->_helper->validate($wholeData, "mFactor") ? $wholeData["mFactor"] : 1;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $notificationCollection = $this->_mobikulNotification
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter("status", 1)
                            ->addFieldToFilter("store_id", [["finset"=>[$storeId]]])
                            ->setOrder("updated_at", "DESC");
                        foreach ($notificationCollection as $notification) {
                            $eachNotification                     = [];
                            $eachNotification["id"]               = $notification->getId();
                            $eachNotification["content"]          = implode(" ", array_slice(explode(" ", $notification->getContent()), 0, 3));
                            $eachNotification["notificationType"] = $notification->getType();
                            $eachNotification["title"]            = $notification->getTitle();
                            $basePath = $this->_baseDir.DS."mobikul".DS."notification".DS.$notification->getFilename();
                            $height   = ($width/2) * $mFactor;
                            $width    *= $mFactor;
                            if (is_file($basePath)) {
                                $newPath = $this->_baseDir.DS."mobikul".DS."resized".DS."notificationBanner".DS.$width."x".$height.DS.$notification->getFilename();
                                $this->_helperCatalog->resizeNCache($basePath, $newPath, $width, $height);
                                $eachNotification["banner"] = $this->_helper->getUrl("media")."mobikul".DS."resized".DS."notificationBanner".DS.$width."x".$height.DS.$notification->getFilename();
                            }
                            else
                                $eachNotification["banner"] = "";
                            if ($notification->getType() == "category") {
// for category /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                                $category = $this->_categoryFactory->create()->load($notification->getProCatId());
                                $eachNotification["categoryName"] = $category->getName();
                                $eachNotification["categoryId"]   = $notification->getProCatId();
                            } elseif ($notification->getType() == "product") {
// for product //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                                $product = $this->_productFactory->create()->load($notification->getProCatId());
                                $eachNotification["productName"]  = $product->getName();
                                $eachNotification["productType"]  = $product->getTypeId();
                                $eachNotification["productId"]    = $notification->getProCatId();
                            }
                            $returnArray["notificationList"][]    = $eachNotification;
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
                    $returnArray["responseCode"] = 0;
                    $returnArray["message"]      = __("Invalid Request");
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