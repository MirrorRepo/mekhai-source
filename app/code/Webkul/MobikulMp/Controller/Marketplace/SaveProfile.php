<?php
    /**
     * Webkul Software.
     *
     * @category  Webkul
     * @package   Webkul_MobikulMp
     * @author    Webkul
     * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
     * @license   https://store.webkul.com/license.html
     */

     namespace Webkul\MobikulMp\Controller\Marketplace;

    class SaveProfile extends AbstractMarketplace    {

        public function execute()   {
            $returnArray            = [];
            $returnArray["authKey"] = "";
            $returnArray["message"] = "";
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
                        $taxvat             = $this->_helper->validate($wholeData, "taxvat")             ? $wholeData["taxvat"]             : "";
                        $storeId            = $this->_helper->validate($wholeData, "storeId")            ? $wholeData["storeId"]            : 0;
                        $gplusId            = $this->_helper->validate($wholeData, "gplusId")            ? $wholeData["gplusId"]            : "";
                        $country            = $this->_helper->validate($wholeData, "country")            ? $wholeData["country"]            : "";
                        $vimeoId            = $this->_helper->validate($wholeData, "vimeoId")            ? $wholeData["vimeoId"]            : "";
                        $twActive           = $this->_helper->validate($wholeData, "twActive")           ? $wholeData["twActive"]           : 0;
                        $fbActive           = $this->_helper->validate($wholeData, "fbActive")           ? $wholeData["fbActive"]           : 0;
                        $twitterId          = $this->_helper->validate($wholeData, "twitterId")          ? $wholeData["twitterId"]          : "";
                        $youtubeId          = $this->_helper->validate($wholeData, "youtubeId")          ? $wholeData["youtubeId"]          : "";
                        $shopTitle          = $this->_helper->validate($wholeData, "shopTitle")          ? $wholeData["shopTitle"]          : "";
                        $customerId         = $this->_helper->validate($wholeData, "customerId")         ? $wholeData["customerId"]         : 0;
                        $facebookId         = $this->_helper->validate($wholeData, "facebookId")         ? $wholeData["facebookId"]         : "";
                        $instagramId        = $this->_helper->validate($wholeData, "instagramId")        ? $wholeData["instagramId"]        : "";
                        $pinterestId        = $this->_helper->validate($wholeData, "pinterestId")        ? $wholeData["pinterestId"]        : "";
                        $metaKeyword        = $this->_helper->validate($wholeData, "metaKeyword")        ? $wholeData["metaKeyword"]        : "";
                        $gplusActive        = $this->_helper->validate($wholeData, "gplusActive")        ? $wholeData["gplusActive"]        : 0;
                        $vimeoActive        = $this->_helper->validate($wholeData, "vimeoActive")        ? $wholeData["vimeoActive"]        : 0;
                        $returnPolicy       = $this->_helper->validate($wholeData, "returnPolicy")       ? $wholeData["returnPolicy"]       : "";
                        $contactNumber      = $this->_helper->validate($wholeData, "contactNumber")      ? $wholeData["contactNumber"]      : "";
                        $youtubeActive      = $this->_helper->validate($wholeData, "youtubeActive")      ? $wholeData["youtubeActive"]      : 0;
                        $shippingPolicy     = $this->_helper->validate($wholeData, "shippingPolicy")     ? $wholeData["shippingPolicy"]     : "";
                        $paymentDetails     = $this->_helper->validate($wholeData, "paymentDetails")     ? $wholeData["paymentDetails"]     : "";
                        $companyLocality    = $this->_helper->validate($wholeData, "companyLocality")    ? $wholeData["companyLocality"]    : "";
                        $backgroundColor    = $this->_helper->validate($wholeData, "backgroundColor")    ? $wholeData["backgroundColor"]    : "";
                        $metaDescription    = $this->_helper->validate($wholeData, "metaDescription")    ? $wholeData["metaDescription"]    : "";
                        $instagramActive    = $this->_helper->validate($wholeData, "instagramActive")    ? $wholeData["instagramActive"]    : 0;
                        $pinterestActive    = $this->_helper->validate($wholeData, "pinterestActive")    ? $wholeData["pinterestActive"]    : 0;
                        $companyDescription = $this->_helper->validate($wholeData, "companyDescription") ? $wholeData["companyDescription"] : "";
                        $environment        = $this->_emulate->startEnvironmentEmulation($storeId);
                        $errors             = $this->validateprofiledata($wholeData);
                        if (empty($errors)) {
                            $id = 0;
                            $collection = $this->_seller->getCollection()
                                ->addFieldToFilter("seller_id", $customerId)
                                ->addFieldToFilter("store_id", $storeId);
                            if (!count($collection)) {
                                $collection = $this->_seller->getCollection()
                                    ->addFieldToFilter("seller_id", $customerId)
                                    ->addFieldToFilter("store_id", 0);
                            }
                            foreach ($collection as $eachSeller)
                                $id = $eachSeller->getId();
// Save seller data for current store ///////////////////////////////////////////////////////////////////////////////////////////
                            $seller = $this->_seller->load($id);
                            $profileData = [
                                "taxvat"              => $taxvat,
                                "gplus_id"            => $gplusId,
                                "vimeo_id"            => $vimeoId,
                                "tw_active"           => $twActive,
                                "fb_active"           => $fbActive,
                                "twitter_id"          => $twitterId,
                                "youtube_id"          => $youtubeId,
                                "shop_title"          => $shopTitle,
                                "facebook_id"         => $facebookId,
                                "country_pic"         => $country,
                                "instagram_id"        => $instagramId,
                                "pinterest_id"        => $pinterestId,
                                "meta_keyword"        => $metaKeyword,
                                "gplus_active"        => $gplusActive,
                                "vimeo_active"        => $vimeoActive,
                                "return_policy"       => $returnPolicy,
                                "youtube_active"      => $youtubeActive,
                                "contact_number"      => $contactNumber,
                                "payment_source"      => $paymentDetails,
                                "shipping_policy"     => $shippingPolicy,
                                "company_locality"    => $companyLocality,
                                "instagram_active"    => $instagramActive,
                                "pinterest_active"    => $pinterestActive,
                                "background_width"    => $backgroundColor,
                                "meta_description"    => $metaDescription,
                                "company_description" => $companyDescription
                            ];
                            $seller->addData($profileData);
                            if (!$id) {
                                $seller->setCreatedAt($this->_date->gmtDate());
                            }
                            $seller->setUpdatedAt($this->_date->gmtDate());
                            $seller->save();
                            if ($companyDescription) {
                                $companyDescription = str_replace("script", "", $companyDescription);
                            }
                            $seller->setCompanyDescription($companyDescription);
                            if ($returnPolicy) {
                                $returnPolicy = str_replace("script", "", $returnPolicy);
                                $seller->setReturnPolicy($returnPolicy);
                            }
                            if ($shippingPolicy) {
                                $shippingPolicy = str_replace("script", "", $shippingPolicy);
                                $seller->setShippingPolicy($shippingPolicy);
                            }
                            $seller->setMetaDescription($metaDescription);
                            if ($taxvat) {
                                $customer = $this->_customer->load($customerId);
                                $customer->setTaxvat($taxvat);
                                $customer->setId($customerId)->save();
                            }
                            $target = $this->_mediaDirectory->getAbsolutePath("avatar/");
                            try {
                                $uploader = $this->_fileUploaderFactory->create(["fileId"=>"banner_pic"]);
                                $uploader->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
                                $uploader->setAllowRenameFiles(true);
                                $result = $uploader->save($target);
                                if ($result["file"]) {
                                    $seller->setBannerPic($result["file"]);
                                }
                            } catch (\Exception $e) {
                                if ($e->getMessage() != "The file was not uploaded.") {
                                    $errors[] = $e->getMessage();
                                }
                            }
                            try {
                                $uploaderLogo = $this->_fileUploaderFactory->create(["fileId"=>"logo_pic"]);
                                $uploaderLogo->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
                                $uploaderLogo->setAllowRenameFiles(true);
                                $resultLogo = $uploaderLogo->save($target);
                                if ($resultLogo["file"]) {
                                    $seller->setLogoPic($resultLogo["file"]);
                                }
                            } catch (\Exception $e) {
                                if ($e->getMessage() != "The file was not uploaded.") {
                                    $errors[] = $e->getMessage();
                                }
                            }
                            $seller->setCountryPic($country);
                            $seller->setStoreId($storeId);
                            $errorInFileUpload = false;
                            $target = $this->_mediaDirectory->getAbsolutePath("avatar/");
                            $files  = $this->getRequest()->getFiles();
                            if(isset($files["banner"])){
                                try {
                                    $uploader = $this->_fileUploaderFactory->create(["fileId" => "banner"]);
                                    $uploader->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
                                    $uploader->setAllowRenameFiles(true);
                                    $result = $uploader->save($target);
                                    if ($result["file"]) {
                                        $seller->setBannerPic($result["file"]);
                                    }
                                } catch (\Exception $e) {
                                    if ($e->getMessage() != "The file was not uploaded.") {
                                        $errorInFileUpload = true;
                                        $returnArray["message"] = $e->getMessage();
                                    }
                                }
                            }
                            if(isset($files["logo"])){
                                try {
                                    $uploader = $this->_fileUploaderFactory->create(["fileId"=>"logo"]);
                                    $uploader->setAllowedExtensions(["jpg", "jpeg", "gif", "png"]);
                                    $uploader->setAllowRenameFiles(true);
                                    $result = $uploader->save($target);
                                    if ($result["file"]) {
                                        $seller->setLogoPic($result["file"]);
                                    }
                                } catch (\Exception $e) {
                                    if ($e->getMessage() != "The file was not uploaded.") {
                                        $errorInFileUpload = true;
                                        $returnArray["message"] = $e->getMessage();
                                    }
                                }
                            }
                            $seller->save();
                            $returnArray["success"] = true;
                            if($errorInFileUpload)
                                $returnArray["message"] = __("Profile information was successfully saved, except image(s).");
                            else
                                $returnArray["message"] = __("Profile information was successfully saved.");
                        } else {
                            $returnArray["message"] = implode(", ", $errors);
                        }
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

        protected function validateprofiledata(&$fields)    {
            $errors = [];
            $data   = [];
            foreach ($fields as $code => $value) {
                switch ($code) :
                    case "twitterId":
                        if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                            $errors[] = __("Twitterid cannot contain space and special characters, allowed special carecters are @,#,_,-");
                        } else {
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $fields[$code] = $value;
                        }
                        break;
                    case "facebookId":
                        if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                            $errors[] = __("Facebookid cannot contain space and special characters, allowed special carecters are @,#,_,-");
                        } else {
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $fields[$code] = $value;
                        }
                        break;
                    case "instagramId":
                        if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                            $errors[] = __("Instagram ID cannot contain space and special characters, allowed special carecters are @,#,_,-");
                        } else {
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $fields[$code] = $value;
                        }
                        break;
                    case "gplusId":
                        if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                            $errors[] = __("Google Plus ID cannot contain space and special characters, allowed special carecters are @,#,_,-");
                        } else {
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $fields[$code] = $value;
                        }
                        break;
                    case "youtubeId":
                        if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                            $errors[] = __("Youtube ID cannot contain space and special characters, allowed special carecters are @,#,_,-");
                        } else {
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $fields[$code] = $value;
                        }
                        break;
                    case "vimeoId":
                        if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                            $errors[] = __("Vimeo ID cannot contain space and special characters, allowed special carecters are @,#,_,-");
                        } else {
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $fields[$code] = $value;
                        }
                        break;
                    case "pinterestId":
                        if (trim($value) != "" && preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)) {
                            $errors[] = __("Pinterest ID cannot contain space and special characters, allowed special carecters are @,#,_,-");
                        } else {
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $fields[$code] = $value;
                        }
                        break;
                    case "taxvat":
                        if (trim($value) != "" && preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)) {
                            $errors[] = __("Tax/VAT Number cannot contain space and special characters");
                        } else {
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $fields[$code] = $value;
                        }
                        break;
                    case "shopTitle":
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $fields[$code] = $value;
                        break;
                    case "contactNumber":
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $fields[$code] = $value;
                        break;
                    case "companyLocality":
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $fields[$code] = $value;
                        break;
                    case "companyDescription":
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $value = $this->_marketplaceHelper->validateXssString($value);
                            $fields[$code] = $value;
                        break;
                    case "metaKeyword":
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $value = $this->_marketplaceHelper->validateXssString($value);
                            $fields[$code] = $value;
                        break;
                    case "metaDescription":
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $value = $this->_marketplaceHelper->validateXssString($value);
                            $fields[$code] = $value;
                        break;
                    case "shippingPolicy":
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $value = $this->_marketplaceHelper->validateXssString($value);
                            $fields[$code] = $value;
                        break;

                    case "returnPolicy":
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $value = $this->_marketplaceHelper->validateXssString($value);
                            $fields[$code] = $value;
                        break;
                    case "backgroundColor":
                        if (trim($value) != "" && strlen($value) != 6 && substr($value, 0, 1) != "#") {
                            $errors[] = __("Invalid Background Color");
                        } else {
                            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                            $fields[$code] = $value;
                        }
                endswitch;
            }
            return $errors;
        }

    }