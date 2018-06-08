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

    class ForgotPassword extends AbstractCustomer   {

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
                        $email          = $this->_helper->validate($wholeData, "email")     ? $wholeData["email"]     : "";
                        $storeId        = $this->_helper->validate($wholeData, "storeId")   ? $wholeData["storeId"]   : 1;
                        $websiteId      = $this->_helper->validate($wholeData, "websiteId") ? $wholeData["websiteId"] : 0;
                        $environment    = $this->_emulate->startEnvironmentEmulation($storeId);
                        $emailValidator = new \Zend\Validator\EmailAddress();
                        if (!$emailValidator->isValid($email)) {
                            $returnArray["message"] = __("Invalid email address.");
                            return $this->getJsonResponse($returnArray);
                        }
                        $customer = $this->_customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($email);
                        if ($customer->getId() > 0) {
                            $this->_accountManagement->initiatePasswordReset(
                                $email,
                                \Magento\Customer\Model\AccountManagement::EMAIL_REMINDER,
                                $customer->getWebsiteId()
                            );
                        }
                        $environment            = $this->_emulate->startEnvironmentEmulation($storeId);
                        $returnArray["success"] = true;
                        $returnArray["message"] = sprintf(__("If there is an account associated with %s you will receive an email with a link to reset your password."), $email);
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
                $this->_helper->printLog($e, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }