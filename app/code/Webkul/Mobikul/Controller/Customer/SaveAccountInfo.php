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
    use Magento\Framework\App\ObjectManager;

    class SaveAccountInfo extends AbstractCustomer     {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["responseCode"] = 0;
            $returnArray["customerName"] = "";
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
                        $dob              = $this->_helper->validate($wholeData, "dob")              ? $wholeData["dob"]              : "";
                        $email            = $this->_helper->validate($wholeData, "email")            ? $wholeData["email"]            : "";
                        $mobile           = $this->_helper->validate($wholeData, "mobile")           ? $wholeData["mobile"]           : "";
                        $prefix           = $this->_helper->validate($wholeData, "prefix")           ? $wholeData["prefix"]           : "";
                        $suffix           = $this->_helper->validate($wholeData, "suffix")           ? $wholeData["suffix"]           : "";
                        $taxvat           = $this->_helper->validate($wholeData, "taxvat")           ? $wholeData["taxvat"]           : "";
                        $gender           = $this->_helper->validate($wholeData, "gender")           ? $wholeData["gender"]           : "";
                        $storeId          = $this->_helper->validate($wholeData, "storeId")          ? $wholeData["storeId"]          : 1;
                        $lastName         = $this->_helper->validate($wholeData, "lastName")         ? $wholeData["lastName"]         : "";
                        $firstName        = $this->_helper->validate($wholeData, "firstName")        ? $wholeData["firstName"]        : "";
                        $customerId       = $this->_helper->validate($wholeData, "customerId")       ? $wholeData["customerId"]       : 0;
                        $middleName       = $this->_helper->validate($wholeData, "middleName")       ? $wholeData["middleName"]       : "";
                        $newPassword      = $this->_helper->validate($wholeData, "newPassword")      ? $wholeData["newPassword"]      : "";
                        $doChangeEmail    = $this->_helper->validate($wholeData, "doChangeEmail")    ? $wholeData["doChangeEmail"]    : 0;
                        $confirmPassword  = $this->_helper->validate($wholeData, "confirmPassword")  ? $wholeData["confirmPassword"]  : "";
                        $currentPassword  = $this->_helper->validate($wholeData, "currentPassword")  ? $wholeData["currentPassword"]  : "";
                        $doChangePassword = $this->_helper->validate($wholeData, "doChangePassword") ? $wholeData["doChangePassword"] : 0;
                        $environment      = $this->_emulate->startEnvironmentEmulation($storeId);
                        $currentCustomerDataObject = $this->_customerRepositoryInterface->getById($customerId);
                        $inputData = [
                            "dob"                   => $dob,
                            "email"                 => $email,
                            "prefix"                => $prefix,
                            "suffix"                => $suffix,
                            "taxvat"                => $taxvat,
                            "gender"                => $gender,
                            "lastname"              => $lastName,
                            "firstname"             => $firstName,
                            "middlename"            => $middleName,
                            "password"              => $newPassword,
                            "password_confirmation" => $confirmPassword,
                            "current_password"      => $currentPassword
                        ];
                        $this->_request->setParams($inputData);
                        $storeManager = $this->_objectManager->get("\Magento\Store\Model\StoreManagerInterface");
                        $customerCheck = $this->_customerFactory->create()->setWebsiteId($storeManager->getStore()->getWebsiteId())->loadByEmail($email);
                        $checkCustomerId = $customerCheck->getId();
                        if ($checkCustomerId > 0 && $checkCustomerId != $customerId) {
                            $returnArray["message"] = __("A customer with the same email already exists in an associated website.");
                            return $this->getJsonResponse($returnArray);
                        }
                        $inputData = $this->_request;
                        $customerCandidateDataObject = $this->populateNewCustomerDataObject(
                            $inputData,
                            $currentCustomerDataObject,
                            $doChangeEmail
                        );
                        if($doChangeEmail == 1){
                            try {
                                $this->getAuthentication()->authenticate($currentCustomerDataObject->getId(), $currentPassword);
                            } catch (InvalidEmailOrPasswordException $e) {
                                throw new InvalidEmailOrPasswordException(__("The password doesn't match this account."));
                            }
                            $this->_customerRepositoryInterface->save($customerCandidateDataObject);
                        }
                        $isPasswordChanged = false;
                        if($doChangePassword == 1){
                            if ($newPassword != $confirmPassword)
                                throw new InputException(__("Password confirmation doesn't match entered password."));
                            $isPasswordChanged = $this->_accountManagement->changePassword($email, $currentPassword, $newPassword);
                            $this->_customerRepositoryInterface->save($customerCandidateDataObject);
                        }
                        $this->_customerRepositoryInterface->save($customerCandidateDataObject);
                        $this->getEmailNotification()->credentialsChanged(
                            $customerCandidateDataObject,
                            $currentCustomerDataObject->getEmail(),
                            $isPasswordChanged
                        );
                        $customer = $this->_customerFactory->create()->load($customerId);
                        $returnArray["success"]      = true;
                        $returnArray["customerName"] = $customer->getName();
                        $returnArray["message"]      = __("You saved the account information.");
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
            } catch (InvalidEmailOrPasswordException $e) {
                $returnArray["message"] = $e->getMessage();
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            } catch (UserLockedException $e) {
                $returnArray["message"] = __("The account is locked. Please wait and try again or contact %1.", $this->getScopeConfig()->getValue("contact/email/recipient_email"));
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            } catch (InputException $e) {
                $message = [];
                $message[] = $e->getMessage();
                foreach ($e->getErrors() as $error)
                    $message[] = $error->getMessage();
                $returnArray["message"] = implode(",", $message);
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $returnArray["message"] = $e->getMessage();
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            } catch (\Exception $e) {
                $returnArray["message"] = __("We can't save the customer.");
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

        private function populateNewCustomerDataObject(
            \Magento\Framework\App\RequestInterface $inputData,
            \Magento\Customer\Api\Data\CustomerInterface $currentCustomerData,
            $doChangeEmail
        ) {
            $attributeValues = $this->getCustomerMapper()->toFlatArray($currentCustomerData);
            $customerDto     = $this->_customerExtractor->extract(
                "customer_account_edit",
                $inputData,
                $attributeValues
            );
            $customerDto->setId($currentCustomerData->getId());
            if (!$customerDto->getAddresses()) {
                $customerDto->setAddresses($currentCustomerData->getAddresses());
            }
            if (!$doChangeEmail) {
                $customerDto->setEmail($currentCustomerData->getEmail());
            }
            return $customerDto;
        }

        private function getScopeConfig()   {
            if (!($this->_scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface))
                return ObjectManager::getInstance()->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
            else
                return $this->_scopeConfig;
        }

        private function dispatchSuccessEvent(\Magento\Customer\Api\Data\CustomerInterface $customerCandidateDataObject)    {
            $this->_eventManager->dispatch("customer_account_edited", ["email"=>$customerCandidateDataObject->getEmail()]);
        }

        private function getCustomerMapper()    {
            if ($this->_customerMapper === null)
                $this->_customerMapper = ObjectManager::getInstance()->get("Magento\Customer\Model\Customer\Mapper");
            return $this->_customerMapper;
        }

        private function getAuthentication()    {
            if (!($this->_authentication instanceof \Magento\Customer\Model\AuthenticationInterface))
                return ObjectManager::getInstance()->get(\Magento\Customer\Model\AuthenticationInterface::class);
            else
                return $this->_authentication;
        }

        private function getEmailNotification()     {
            if (!($this->_emailNotification instanceof \Magento\Customer\Model\EmailNotificationInterface))
                return ObjectManager::getInstance()->get(\Magento\Customer\Model\EmailNotificationInterface::class);
            else
                return $this->_emailNotification;
        }

    }