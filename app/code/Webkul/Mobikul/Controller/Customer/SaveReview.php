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

    class SaveReview extends AbstractCustomer   {

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
                        $title       = $this->_helper->validate($wholeData, "title")      ? $wholeData["title"]      : "";
                        $detail      = $this->_helper->validate($wholeData, "detail")     ? $wholeData["detail"]     : "";
                        $ratings     = $this->_helper->validate($wholeData, "ratings")    ? $wholeData["ratings"]    : "[]";
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $nickname    = $this->_helper->validate($wholeData, "nickname")   ? $wholeData["nickname"]   : "";
                        $productId   = $this->_helper->validate($wholeData, "productId")  ? $wholeData["productId"]  : 0;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $ratings     = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($ratings);
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        if ($customerId == 0)
                            $customerId = NULL;
                        $review = $this->_objectManager->create("\Magento\Review\Model\Review")
                            ->setEntityPkValue($productId)
                            ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)
                            ->setTitle($title)
                            ->setDetail($detail)
                            ->setEntityId(1)
                            ->setStoreId($storeId);
                        if((bool)$this->_helper->getConfigData("catalog/review/allow_guest") && $customerId != 0)
                            $review->setCustomerId($customerId);
                        $review->setNickname($nickname)
                            ->setReviewId($review->getId())
                            ->setStores([$storeId])
                            ->save();
                        foreach ($ratings as $ratingId=>$optionId) {
                            $this->_objectManager->create("\Magento\Review\Model\Rating")
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->setCustomerId($customerId)
                                ->addOptionVote($optionId, $productId);
                        }
                        $review->aggregate();
                        $returnArray["message"] = __("Your review has been accepted for moderation.");
                        $returnArray["success"] = true;
                        $this->_emulate->stopEnvironmentEmulation($environment);
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