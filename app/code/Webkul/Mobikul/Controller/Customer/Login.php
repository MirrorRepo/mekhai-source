<?php
    /**
    * Webkul Software.
    *
    * @category Webkul_Mobikul
    *
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Controller\Customer;

    class Login extends AbstractCustomer    {

        public function execute()   {
            $returnArray                  = [];
            $returnArray["authKey"]       = "";
            $returnArray["success"]       = false;
            $returnArray["message"]       = "";
            $returnArray["cartCount"]     = 0;
            $returnArray["customerId"]    = 0;
            $returnArray["bannerImage"]   = "";
            $returnArray["responseCode"]  = 0;
            $returnArray["customerName"]  = "";
            $returnArray["profileImage"]  = "";
            $returnArray["customerEmail"] = "";
            try {
                $wholeData             = $this->getRequest()->getPostValue();
                $this->_headers        = $this->getRequest()->getHeaders();
                $this->_helper->log(__CLASS__, "logClass", $wholeData);
                $this->_helper->log($wholeData, "logParams", $wholeData);
                $this->_helper->log($this->_headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $authKey           = $this->getRequest()->getHeader("authKey");
                    $apiKey            = $this->getRequest()->getHeader("apiKey");
                    $apiPassword       = $this->getRequest()->getHeader("apiPassword");
                    $authData          = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $width         = $this->_helper->validate($wholeData, "width")     ? $wholeData["width"]     : 1000;
                        $token         = $this->_helper->validate($wholeData, "token")     ? $wholeData["token"]     : "";
                        $mobile        = $this->_helper->validate($wholeData, "mobile")    ? $wholeData["mobile"]    : 0;
                        $storeId       = $this->_helper->validate($wholeData, "storeId")   ? $wholeData["storeId"]   : 0;
                        $quoteId       = $this->_helper->validate($wholeData, "quoteId")   ? $wholeData["quoteId"]   : 0;
                        $mFactor       = $this->_helper->validate($wholeData, "mFactor")   ? $wholeData["mFactor"]   : 1;
                        $username      = $this->_helper->validate($wholeData, "username")  ? $wholeData["username"]  : "";
                        $password      = $this->_helper->validate($wholeData, "password")  ? $wholeData["password"]  : "";
                        $websiteId     = $this->_helper->validate($wholeData, "websiteId") ? $wholeData["websiteId"] : 1;
                        $environment   = $this->_emulate->startEnvironmentEmulation($storeId);
                        $customerModel = $this->_customerFactory->create();
                        // $customer      = $customerModel->setWebsiteId($websiteId)->loadByEmail($username);
                        if ($mobile != 0) {
                            // $collection = Mage::getModel("mobikul/customermobile")->getCollection()->addFieldToFilter("mobile", $mobile);
                            // foreach ($collection as $each) {
                            //     $customer = $customerModel->load($each->getCustomerId());
                            // }
                        } else
                            $customer = $customerModel->setWebsiteId($websiteId)->loadByEmail($username);
                        if($customer->getId() > 0) {
                            $customerId = $customer->getId();
                            $customer = $customerModel->setWebsiteId($websiteId);
                            if($customerModel->getConfirmation() && $customerModel->isConfirmationRequired()) {
                                $returnArray["message"] = __("This account is not confirmed.");
                                return $this->getJsonResponse($returnArray);
                            }
                            $hash = $customerModel->getPasswordHash();
                            $validatePassword = false;
                            if (!$hash)
                                $validatePassword = false;
                            $validatePassword = $this->_encryptor->validateHash($password, $hash);
                            if (!$validatePassword) {
                                $returnArray["message"] = __("Invalid login or password.");
                                return $this->getJsonResponse($returnArray);
                            }
                            $returnArray["customerId"]    = $customerId;
                            $returnArray["customerName"]  = $customer->getName();
                            $returnArray["customerEmail"] = $customer->getEmail();
// Saving Device Token //////////////////////////////////////////////////////////////////////////////////////////////////////////
                            $this->_tokenHelper->saveToken($customer->getId(), $token);
                            $width *= $mFactor;
                            $height = ($width/2)*$mFactor;
                            $profileHeight = $profileWidth = 144 * $mFactor;
                            $collection = $this->_userImage->getCollection()->addFieldToFilter("customer_id", $customer->getId());
                            if ($collection->getSize() > 0) {
                                $time = time();
                                foreach ($collection as $value) {
                                    if ($value->getBanner() != "") {
                                        if ($value->getIsSocial() == 1) {
                                            $returnArray["bannerImage"] = $value->getBanner();
                                        }
                                        else {
                                            $basePath = $this->_baseDir.DS."mobikul".DS."customerpicture".DS.$customerId.DS.$value->getBanner();
                                            $newUrl = "";
                                            if(is_file($basePath)) {
                                                $newPath = $this->_baseDir.DS."mobikul".DS."customerpicture".DS.$customerId.DS.$width."x".$height.DS.$value->getBanner();
                                                $this->_helperCatalog->resizeNCache($basePath, $newPath, $width, $height);
                                                $newUrl = $this->_helper->getUrl("media")."mobikul".DS."customerpicture".DS.$customerId.DS.$width."x".$height.DS.$value->getBanner();
                                            }
                                            $returnArray["bannerImage"] = $newUrl."?".$time;
                                        }
                                    }
                                    if ($value->getProfile() != "") {
                                        if ($value->getIsSocial() == 1) {
                                            $returnArray["profileImage"] = $value->getProfile();
                                        }
                                        else {
                                            $basePath = $this->_baseDir.DS."mobikul".DS."customerpicture".DS.$customerId.DS.$value->getProfile();
                                            $newUrl = "";
                                            if (is_file($basePath)) {
                                                $newPath = $this->_baseDir.DS."mobikul".DS."customerpicture".DS.$customerId.DS.$profileWidth."x".$profileHeight.DS.$value->getProfile();
                                                $this->_helperCatalog->resizeNCache($basePath, $newPath, $profileWidth, $profileHeight);
                                                $newUrl = $this->_helper->getUrl("media")."mobikul".DS."customerpicture".DS.$customerId.DS.$profileWidth."x".$profileHeight.DS.$value->getProfile();
                                            }
                                            $returnArray["profileImage"] = $newUrl."?".$time;
                                        }
                                    }
                                }
                            }
// Merging quest quote with customer quote //////////////////////////////////////////////////////////////////////////////////////
                            $customer = $this->_customerRepositoryInterface->getById($customerId);
                            if($quoteId != 0) {
                                // $guestQuote = $this->_objectManager->create("\Magento\Quote\Model\Quote")->setStoreId($storeId)->load($quoteId);
                                // $customerQuote = $this->_objectManager->create("\Magento\Quote\Model\Quote")
                                $guestQuote = $this->_quote->setStoreId($storeId)->load($quoteId);
                                $customerQuote = $this->_quote
                                    ->getCollection()
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
                            // $quote = $this->_objectManager->create("\Magento\Quote\Model\Quote")
                            $quote = $this->_quote
                                ->getCollection()
                                ->addFieldToFilter("customer_id", $customerId)
                                ->addFieldToFilter("is_active", 1)
                                ->addOrder("updated_at", "DESC")
                                ->getFirstItem();
                            $returnArray["cartCount"] = $quote->getItemsQty() * 1;
// merging compare list /////////////////////////////////////////////////////////////////////////////////////////////////////////
                            $items = $this->_productCollectionFactory->create();
                            $items->useProductItem(true)->setStoreId($storeId);
                            $items->setVisitorId($this->_visitor->getId());
                            $attributes = $this->_catalogConfig->getProductAttributes();
                            $items->addAttributeToSelect($attributes)
                                ->loadComparableAttributes()
                                ->setVisibility($this->_productVisibility->getVisibleInSiteIds());
                            foreach ($items as $item) {
                                $item->setCustomerId($customerId);
                                $this->_compareItem->updateCustomerFromVisitor($item);
                                $this->_productCompare->setCustomerId($customerId)->calculate();
                            }
                            $returnArray["success"] = true;
                        } else {
                            $returnArray["message"] = __("Invalid login or password.");
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