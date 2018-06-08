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

    class SaveAddress extends AbstractCustomer  {

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
                        $storeId     = $this->_helper->validate($wholeData, "storeId")     ? $wholeData["storeId"]     : 1;
                        $addressId   = $this->_helper->validate($wholeData, "addressId")   ? $wholeData["addressId"]   : 0;
                        $customerId  = $this->_helper->validate($wholeData, "customerId")  ? $wholeData["customerId"]  : 0;
                        $addressData = $this->_helper->validate($wholeData, "addressData") ? $wholeData["addressData"] : "[]";
                        $addressData = $this->_objectManager->create("Magento\Framework\Json\Helper\Data")->jsonDecode($addressData);
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $addressDataArr = [];
                        foreach ($addressData as $key=>$addressValue)
                            $addressDataArr[$key] = $addressValue;
                        $customer = $this->_customerFactory->create()->load($customerId);
                        $customerSession = $this->_objectManager->get("\Magento\Customer\Model\Session")->setCustomer($customer);
                        $address  = $this->_objectManager->create("\Magento\Customer\Model\Address");
                        if ($addressId != 0) {
                            $existsAddress = $customer->getAddressById($addressId);
                            if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                                $address->setId($existsAddress->getId());
                            }
                        }
                        $errors = [];
                        $addressForm = $this->_objectManager->create("\Magento\Customer\Model\Form");
                        $addressForm->setFormCode("customer_address_edit")->setEntity($address);
                        $addressErrors  = $addressForm->validateData($addressDataArr);
                        if ($addressErrors !== true)
                            $errors = $addressErrors;
                        $addressForm->compactData($addressDataArr);
                        $address->setCustomerId($customerId)
                            ->setIsDefaultBilling($addressDataArr["default_billing"])
                            ->setIsDefaultShipping($addressDataArr["default_shipping"]);
                        $addressErrors = $address->validate();
                        $address->save();
                        $returnArray["message"] = __("The address has been saved.");
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