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

    namespace Webkul\Mobikul\Controller\Checkout;

    class SaveOrder extends AbstractCheckout    {

        public function execute()   {
            $returnArray                          = [];
            $returnArray["email"]                 = "";
            $returnArray["authKey"]               = "";
            $returnArray["message"]               = "";
            $returnArray["success"]               = false;
            $returnArray["orderId"]               = 0;
            $returnArray["cartCount"]             = 0;
            $returnArray["canReorder"]            = false;
            $returnArray["incrementId"]           = 0;
            $returnArray["responseCode"]          = 0;
            $returnArray["showCreateAccountLink"] = false;
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
                        $quoteId     = $this->_helper->validate($wholeData, "quoteId")    ? $wholeData["quoteId"]    : 0;
                        $customerId  = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $quote       = new \Magento\Framework\DataObject();
                        if ($customerId != 0) {
                            $quoteCollection = $this->_quoteFactory->create()
                                ->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC");
                            $quote = $quoteCollection->getFirstItem();
                        }
                        if ($quoteId != 0)
                            $quote = $this->_quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                        if($quote->getItemsQty()*1 == 0){
                            $returnArray["message"] = __("Sorry Something went wrong !!");
                            return $this->getJsonResponse($returnArray);
                        }
                        else{
                            $returnArray["cartCount"] = $quote->getItemsQty()*1;
                        }
                        if ($quote->getCheckoutMethod() == "guest" && !$this->_objectManager->create("\Magento\Checkout\Helper\Data")->isAllowedGuestCheckout($quote, $quote->getStoreId())) {
                            $returnArray["message"] = __("Guest Checkout is not Enabled");
                            return $this->getJsonResponse($returnArray);
                        }
                        $isNewCustomer = false;
                        switch ($quote->getCheckoutMethod()) {
                            case "guest":
                                $this->_prepareGuestQuote($quote);
                                break;
                            case "register":
                                $this->_prepareNewCustomerQuote($quote);
                                $isNewCustomer = true;
                                break;
                            default:
                                $this->_prepareCustomerQuote($quote, $customerId);
                                break;
                        }
                        $quote->collectTotals()->save();
                        $order = $this->_objectManager->create("\Magento\Quote\Model\QuoteManagement")->submit($quote);
                        if ($isNewCustomer) {
                            $result = $this->_involveNewCustomer($quote);
                            if(!$result["status"]){
                                $returnArray["message"] = $result["message"];
                                return $this->getJsonResponse($returnArray);
                            }
                        }
                        if ($order) {
                            $this->_eventManager->dispatch("checkout_type_onepage_save_order_after", ["order"=>$order, "quote"=>$quote]);
                            try {
                                $this->_objectManager->create("\Magento\Sales\Model\Order\Email\Sender\OrderSender")->send($order);
                            } catch (\Exception $e) {
                                $returnArray["message"] = $e->getMessage();
                                return $this->getJsonResponse($returnArray);
                            }
                        }
                        $this->_eventManager->dispatch("checkout_submit_all_after", ["order"=>$order, "quote"=>$quote]);
// checknig wheather user is new user or returned customer //////////////////////////////////////////////////////////////////////
                        $userEmail     = $order->getCustomerEmail();
                        $websiteId     = $this->_objectManager->create("\Magento\Store\Model\Store")->load($order->getStoreId())->getWebsiteId();
                        $customerModel = $this->_objectManager->get("\Magento\Customer\Model\CustomerFactory")->create();
                        $customer      = $customerModel->setWebsiteId($websiteId)->loadByEmail($userEmail);
                        if(!$customer->getId())
                            $returnArray["showCreateAccountLink"] = true;
                        $returnArray["email"] = $userEmail;
                        if($order->getCustomerIsGuest())    {
                            $tokenCollection = $this->_deviceToken->getCollection()->addFieldToFilter("token", $token);
                            foreach ($tokenCollection as $eachToken) {
                                $eachToken->setEmail($order->getCustomerEmail());
                                $eachToken->setId($eachToken->getId());
                                $eachToken->save();
                            }
                        }
                        if($this->_objectManager->get("\Magento\Sales\Helper\Reorder")->canReorder($order->getEntityId()))
                            $returnArray["canReorder"] = true;
                        $quote->collectTotals()->setIsActive(0)->setReservedOrderId(null)->save();
                        $returnArray["orderId"]     = $order->getId();
                        $returnArray["success"]     = true;
                        $returnArray["incrementId"] = $order->getIncrementId();
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
            } catch(\Exception $e)   {
                $returnArray["message"] = $e->getMessage();
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

        public function _prepareGuestQuote($quote) {
            $quote->setCustomerId(null)
                ->setCustomerEmail($quote->getBillingAddress()->getEmail())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
        }

        public function _prepareNewCustomerQuote($quote)   {
            $billing  = $quote->getBillingAddress();
            $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
            $customer = $quote->getCustomer();
            $customerBillingData = $billing->exportCustomerAddress();
            $dataArray = $this->_objectCopyService->getDataFromFieldset("checkout_onepage_quote", "to_customer", $quote);
            $this->dataObjectHelper->populateWithArray($customer, $dataArray, "\Magento\Customer\Api\Data\CustomerInterface");
            $quote->setCustomer($customer)->setCustomerId(true);
            $customerBillingData->setIsDefaultBilling(true);
            if ($shipping) {
                if (!$shipping->getSameAsBilling()) {
                    $customerShippingData = $shipping->exportCustomerAddress();
                    $customerShippingData->setIsDefaultShipping(true);
                    $shipping->setCustomerAddressData($customerShippingData);
// Add shipping address to quote since customer Data Object does not hold address information ///////////////////////////////////
                    $quote->addCustomerAddress($customerShippingData);
                } else {
                    $shipping->setCustomerAddressData($customerBillingData);
                    $customerBillingData->setIsDefaultShipping(true);
                }
            } else {
                $customerBillingData->setIsDefaultShipping(true);
            }
            $billing->setCustomerAddressData($customerBillingData);
            $quote->addCustomerAddress($customerBillingData);
        }

        public function _prepareCustomerQuote($quote, $customerId)  {
            $billing  = $quote->getBillingAddress();
            $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
            $customer = $this->_customerRepository->getById($customerId);
            $hasDefaultBilling  = (bool)$customer->getDefaultBilling();
            $hasDefaultShipping = (bool)$customer->getDefaultShipping();
            if ($shipping && !$shipping->getSameAsBilling() && (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())) {
                $shippingAddress = $shipping->exportCustomerAddress();
                if (!$hasDefaultShipping) {
// Make provided address as default shipping address ////////////////////////////////////////////////////////////////////////////
                    $shippingAddress->setIsDefaultShipping(true);
                    $hasDefaultShipping = true;
                }
                $quote->addCustomerAddress($shippingAddress);
                $shipping->setCustomerAddressData($shippingAddress);
            }
            if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
                $billingAddress = $billing->exportCustomerAddress();
                if (!$hasDefaultBilling) {
// Make provided address as default shipping address ////////////////////////////////////////////////////////////////////////////
                    if (!$hasDefaultShipping) {
// Make provided address as default shipping address ////////////////////////////////////////////////////////////////////////////
                        $billingAddress->setIsDefaultShipping(true);
                    }
                    $billingAddress->setIsDefaultBilling(true);
                }
                $quote->addCustomerAddress($billingAddress);
                $billing->setCustomerAddressData($billingAddress);
            }
        }

        public function _involveNewCustomer($quote)    {
            $customer = $quote->getCustomer();
            $confirmationStatus = $this->_accountManagement->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === \Magento\Customer\Model\AccountManagement::ACCOUNT_CONFIRMATION_REQUIRED)
                return ["status"=>false, "message"=>__("You must confirm your account. Please check your email for the confirmation link.")];
            else
                return ["status"=>true, "message"=>""];
        }

    }