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

    class SearchTermList extends AbstractMobikul    {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["message"]      = "";
            $returnArray["termList"]     = [];
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
                        $storeId     = $this->_helper->validate($wholeData, "storeId") ? $wholeData["storeId"] : 1;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $termBlock   = $this->_objectManager
                            ->create("\Magento\Search\Model\ResourceModel\Query\Collection")
                            ->addFieldToFilter("store_id", [["finset"=>[$storeId]]])
                            ->setPopularQueryFilter($storeId);
                        $maxPopularity = $termBlock->getFirstitem()->getPopularity();
                        $minPopularity = $termBlock->getFirstitem()->getPopularity();
                        $range         = $maxPopularity - $minPopularity;
                        $range         = $range == 0 ? 1 : $range;
                        if (sizeof($termBlock) > 0) {
                            foreach ($termBlock as $term) {
                                $eachTerm                  = [];
                                $eachTerm["ratio"]         = ((($term->getPopularity() - $minPopularity) / $range));
                                if ($eachTerm["ratio"] < 0)
                                    $eachTerm["ratio"]     = 0;
                                else {
                                    $eachTerm["ratio"]     *= 70 ;
                                    $eachTerm["ratio"]     += 75 ;
                                }
                                $eachTerm["term"]          = strip_tags($term->getQueryText());
                                $returnArray["termList"][] = $eachTerm;
                            }
                        }
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
                $returnArray["message"] = __($e->getMessage());
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }