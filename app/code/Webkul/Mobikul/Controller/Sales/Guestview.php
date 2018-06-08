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

    namespace Webkul\Mobikul\Controller\Sales;
    use Magento\Store\Model\App\Emulation;
    use Magento\Framework\App\Action\Context;
    use Webkul\Mobikul\Helper\Data as HelperData;

    class Guestview extends \Webkul\Mobikul\Controller\ApiController    {

        protected $_emulate;

        public function __construct(
            Context $context,
            Emulation $emulate,
            HelperData $helper
        ) {
            $this->_emulate = $emulate;
            parent::__construct($helper, $context);
        }

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["message"]      = "";
            $returnArray["success"]      = false;
            $returnArray["responseCode"] = 0;
            try {
                $wholeData           = $this->getRequest()->getPostValue();
                $this->_headers      = $this->getRequest()->getHeaders();
                $this->_helper->log(__CLASS__, "logClass", $wholeData);
                $this->_helper->log($wholeData, "logParams", $wholeData);
                $this->_helper->log($this->_headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $authKey         = $this->getRequest()->getHeader("authKey");
                    $apiKey          = $this->getRequest()->getHeader("apiKey");
                    $apiPassword     = $this->getRequest()->getHeader("apiPassword");
                    $authData        = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $type        = $this->_helper->validate($wholeData, "type")        ? $wholeData["type"]        : "";
                        $email       = $this->_helper->validate($wholeData, "email")       ? $wholeData["email"]       : "";
                        $storeId     = $this->_helper->validate($wholeData, "storeId")     ? $wholeData["storeId"]     : 1;
                        $zip         = $this->_helper->validate($wholeData, "zipCode")     ? $wholeData["zipCode"]     : 0;
                        $lastName    = $this->_helper->validate($wholeData, "lastName")    ? $wholeData["lastName"]    : "";
                        $incrementId = $this->_helper->validate($wholeData, "incrementId") ? $wholeData["incrementId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $errors = false;
                        $order  = $this->_objectManager->get("\Magento\Sales\Model\OrderFactory")->create();
                        if (!empty($wholeData) && $incrementId && $type) {
                            if (empty($incrementId) || empty($lastName) || empty($type) || empty($storeId) || !in_array($type, ["email", "zip"]) || $type == "email" && empty($email) || $type == "zip" && empty($zip)
                            )
                                $errors = true;
                            if (!$errors) {
                                $order = $order->loadByIncrementIdAndStoreId($incrementId, $storeId);
                            }
                            $errors = true;
                            if ($order->getId()) {
                                $billingAddress = $order->getBillingAddress();
                                if (strtolower($lastName) == strtolower($billingAddress->getLastname()) && ($type == "email" && strtolower($email) == strtolower($billingAddress->getEmail()) || $type == "zip" && strtolower($zip) == strtolower($billingAddress->getPostcode())))
                                    $errors = false;
                            }
                        }
                        if (!$errors && $order->getId())
                            $returnArray["success"] = true;
                        else
                            $returnArray["message"] = __("You entered incorrect data. Please try again.");
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