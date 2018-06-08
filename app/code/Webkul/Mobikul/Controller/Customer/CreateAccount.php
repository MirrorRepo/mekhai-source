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

    namespace Webkul\Mobikul\Controller\Customer;

    class CreateAccount extends AbstractCustomer   {

        public function execute()   {
            $returnArray                  = [];
            $returnArray["authKey"]       = "";
            $returnArray["success"]       = false;
            $returnArray["message"]       = "";
            $returnArray["cartCount"]     = 0;
            $returnArray["customerId"]    = 0;
            $returnArray["responseCode"]  = 0;
            $returnArray["customerName"]  = "";
            $returnArray["customerEmail"] = "";
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
                        $dob         = $this->_helper->validate($wholeData, "dob")        ? $wholeData["dob"]        : "";
                        $email       = $this->_helper->validate($wholeData, "email")      ? $wholeData["email"]      : "";
                        $token       = $this->_helper->validate($wholeData, "token")      ? $wholeData["token"]      : "";
                        $mobile      = $this->_helper->validate($wholeData, "mobile")     ? $wholeData["mobile"]     : "";
                        $prefix      = $this->_helper->validate($wholeData, "prefix")     ? $wholeData["prefix"]     : "";
                        $suffix      = $this->_helper->validate($wholeData, "suffix")     ? $wholeData["suffix"]     : "";
                        $taxvat      = $this->_helper->validate($wholeData, "taxvat")     ? $wholeData["taxvat"]     : "";
                        $gender      = $this->_helper->validate($wholeData, "gender")     ? $wholeData["gender"]     : "";
                        $quoteId     = $this->_helper->validate($wholeData, "quoteId")    ? $wholeData["quoteId"]    : 0;
                        $storeId     = $this->_helper->validate($wholeData, "storeId")    ? $wholeData["storeId"]    : 1;
                        $isSocial    = $this->_helper->validate($wholeData, "isSocial")   ? $wholeData["isSocial"]   : 0;
                        $password    = $this->_helper->validate($wholeData, "password")   ? $wholeData["password"]   : "";
                        $lastName    = $this->_helper->validate($wholeData, "lastName")   ? $wholeData["lastName"]   : "";
                        $websiteId   = $this->_helper->validate($wholeData, "websiteId")  ? $wholeData["websiteId"]  : 0;
                        $firstName   = $this->_helper->validate($wholeData, "firstName")  ? $wholeData["firstName"]  : "";
                        $pictureURL  = $this->_helper->validate($wholeData, "pictureURL") ? $wholeData["pictureURL"] : "";
                        $middleName  = $this->_helper->validate($wholeData, "middleName") ? $wholeData["middleName"] : "";
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $emailValidator = new \Zend\Validator\EmailAddress();
                        if (!$emailValidator->isValid($email)) {
                            $returnArray["message"] = __("Invalid email address.");
                            return $this->getJsonResponse($returnArray);
                        }
                        $customerCheck = $this->_customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($email);
                        $customerId = $customerCheck->getId();
                        if ($isSocial == 1) {
                            if ($customerId > 0) {
                                $confirmationStatus = $this->_accountManagement->getConfirmationStatus($customerId);
                                if ($confirmationStatus === \Magento\Customer\Api\AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                                    $returnArray["message"] = __("You must confirm your account. Please check your email for the confirmation link");
                                    return $this->getJsonResponse($returnArray);
                                }
                                $returnArray["success"]       = true;
                                $returnArray["customerId"]    = $customerId;
                                $returnArray["customerName"]  = $customerCheck->getName();
                                $returnArray["customerEmail"] = $customerCheck->getEmail();
                                $returnArray["message"]       = __("Your are now Loggedin");
                                $this->_objectManager->get("\Webkul\Mobikul\Helper\Token")->saveToken($customerId, $token);
                                return $this->getJsonResponse($returnArray);
                            }
                        }
                        else{
                            if ($customerId > 0) {
                                $returnArray["message"] = __("There is already an account with this email address.");
                                return $this->getJsonResponse($returnArray);
                            }
                        }
                        $customer = $this->_customerFactory->create();
                        $customerData = [
                            "dob"        => $dob,
                            "email"      => $email,
                            "prefix"     => $prefix,
                            "suffix"     => $suffix,
                            "taxvat"     => $taxvat,
                            "gender"     => $gender,
                            "lastname"   => $lastName,
                            "password"   => $password,
                            "website_id" => $websiteId,
                            "firstname"  => $firstName,
                            "middlename" => $middleName,
                            "group_id"   => $this->_helper->getConfigData(\Magento\Customer\Model\GroupManagement::XML_PATH_DEFAULT_ID)
                        ];
                        $this->getRequest()->setParams($customerData);
                        $customerObject = $this->_customerExtractor->extract("customer_account_create", $this->_request);
// Creating Customer ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                        $customer      = $this->_accountManagement->createAccount($customerObject, $password, "");
                        $customerId    = $customer->getId();
                        $customer      = $this->_customerFactory->create()->load($customerId);
// Checking for existing mobile number //////////////////////////////////////////////////////////////////////////////////////////
                        // $collection = Mage::getModel("mobikul/customermobile")->getCollection()->addFieldToFilter("mobile", $mobile);
                        // if(count($collection) > 0)  {
                        //     $returnArray["message"] = Mage::helper("mobikul")->__("Mobile number already exist, please provide another number !!");
                        //     return $this->getJsonResponse($returnArray);
                        // }
// Saving Mobile number /////////////////////////////////////////////////////////////////////////////////////////////////////////
                        // Mage::getModel("mobikul/customermobile")->setMobile($mobile)->setCustomerId($customerId)->save();
// Setting Social Data //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if ($isSocial == 1) {
                            $this->_objectManager->create("\Webkul\Mobikul\Model\UserImage")
                                ->setBanner($pictureURL)
                                ->setCustomerId($customerId)
                                ->setIsSocial(1)
                                ->save();
                            // $templateVariable = array();
                            // $emailTemplate = Mage::getModel("core/email_template")->loadDefault("random_generated_password_mail");
                            // $templateVariable["customer_name"]      = $customer->getName();
                            // $templateVariable["generated_password"] = $password;
                            // $templateVariable["contactus_link"]     = Mage::getUrl("contacts");
                            // $emailTemplate->getProcessedTemplate($templateVariable);
                            // $emailTemplate->setSenderName(Mage::getStoreConfig("trans_email/ident_general/name"));
                            // $emailTemplate->setSenderEmail(Mage::getStoreConfig("trans_email/ident_general/email"));
                            // $emailTemplate->send($email, $customer->getName(), $templateVariable);
                            $returnArray["success"]       = true;
                            $returnArray["customerId"]    = $customerId;
                            $returnArray["customerName"]  = $customer->getName();
                            $returnArray["customerEmail"] = $email;
                            $returnArray["message"]       = __("Your Account has been successfully created");
                            return $this->getJsonResponse($returnArray);
                        }
                        $returnArray["customerName"] = $customer->getName();
                        $customer = $this->_customerRepositoryInterface->getById($customerId);
                        if ($quoteId != 0) {
                            $guestQuote = $this->_objectManager->create("\Magento\Quote\Model\Quote")->setStoreId($storeId)->load($quoteId);
                            $customerQuote = $this->_objectManager->create("\Magento\Quote\Model\Quote")->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC")
                                ->getFirstItem();
                            if ($customerQuote->getId() > 0) {
                                $customerQuote->merge($guestQuote)
                                    ->collectTotals()
                                    ->save();
                            } else {
                                $guestQuote->assignCustomer($customer)
                                    ->setCustomer($customer)
                                    ->getShippingAddress()
                                    ->setCollectShippingRates(true);
                                $guestQuote->collectTotals()->save();
                            }
                        }
                        $confirmationStatus = $this->_accountManagement->getConfirmationStatus($customer->getId());
                        if ($confirmationStatus === \Magento\Customer\Api\AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                            $returnArray["message"] = __("You must confirm your account. Please check your email for the confirmation link");
                            return $this->getJsonResponse($returnArray);
                        }
                        $quote = $this->_objectManager->create("\Magento\Quote\Model\Quote")
                            ->getCollection()
                            ->addFieldToFilter("customer_id", $customerId)
                            ->addFieldToFilter("is_active", 1)
                            ->addOrder("updated_at", "DESC")
                            ->getFirstItem();
                        $returnArray["cartCount"]     = $quote->getItemsQty() * 1;
                        $returnArray["success"]       = true;
                        $returnArray["customerId"]    = $customerId;
                        $returnArray["customerEmail"] = $email;
                        $returnArray["message"]       = __("Your Account has been successfully created");
                        $this->_objectManager->get("\Webkul\Mobikul\Helper\Token")->saveToken($customerId, $token);
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