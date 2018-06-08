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

    namespace Webkul\Mobikul\Controller\Extra;

    class Logout extends AbstractMobikul    {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
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
                        $token       = $this->_helper->validate($wholeData, "token")      ? $wholeData["token"]      : "";
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $collection  = $this->_deviceToken
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter("customer_id", $customerId)
                            ->addFieldToFilter("token", $token);
                        foreach($collection as $eachToken)
                            $this->_deviceToken->create()->load($eachToken->getId())->setCustomerId(0)->save();
                        $this->_objectManager->get("\Magento\Customer\Model\Session")->unsetAll();
                        $this->_objectManager->get("\Magento\Framework\Session\SessionManagerInterface")->unsetAll();
// merging compare list /////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $items = $this->_objectManager->get("\Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory")->create();
                        $items->useProductItem(true)->setStoreId($storeId);
                        $items->setVisitorId($this->_objectManager->get("Magento\Customer\Model\Visitor")->getId());
                        $attributes = $this->_objectManager->get("\Magento\Catalog\Model\Config")->getProductAttributes();
                        $items->addAttributeToSelect($attributes)
                            ->loadComparableAttributes()
                            ->addMinimalPrice()
                            ->addTaxPercents()
                            ->setVisibility($this->_objectManager->get("\Magento\Catalog\Model\Product\Visibility")->getVisibleInSiteIds());
                        foreach ($items as $item) {
                            $this->_objectManager->get("\Magento\Catalog\Model\ResourceModel\Product\Compare\Item")->purgeVisitorByCustomer($item);
                            $this->_objectManager->get("\Magento\Catalog\Helper\Product\Compare")->calculate(true);
                        }
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