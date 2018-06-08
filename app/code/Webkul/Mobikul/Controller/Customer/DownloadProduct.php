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

    class DownloadProduct extends AbstractCustomer      {

        public function execute()   {
            $returnArray                 = [];
            $returnArray["url"]          = "";
            $returnArray["authKey"]      = "";
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["fileName"]     = "";
            $returnArray["mimeType"]     = "";
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
                        $hash              = $this->_helper->validate($wholeData, "hash")       ? $wholeData["hash"]       : "";
                        $customerId        = $this->_helper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                        $linkPurchasedItem = $this->_objectManager->create("\Magento\Downloadable\Model\Link\Purchased\Item")->load($hash, "link_hash");
                        if (!$linkPurchasedItem->getId()) {
                            $returnArray["message"]  = __("Requested link does not exist.");
                            return $this->getJsonResponse($returnArray);
                        }
                        $downloadableHelper = $this->_objectManager->create("\Magento\Downloadable\Helper\Data");
                        if (!$downloadableHelper->getIsShareable($linkPurchasedItem)) {
                            $linkPurchased  = $this->_objectManager->create("\Magento\Downloadable\Model\Link\Purchased")->load($linkPurchasedItem->getPurchasedId());
                            if ($linkPurchased->getCustomerId() != $customerId) {
                                $returnArray["message"] = __("Requested link does not exist.");
                                return $this->getJsonResponse($returnArray);
                            }
                        }
                        $downloadableHelperFile = $this->_objectManager->create("\Magento\Downloadable\Helper\File");
                        $downloadsLeft          = $linkPurchasedItem->getNumberOfDownloadsBought() - $linkPurchasedItem->getNumberOfDownloadsUsed();
                        $status                 = $linkPurchasedItem->getStatus();
                        if ($status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_AVAILABLE && ($downloadsLeft || $linkPurchasedItem->getNumberOfDownloadsBought() == 0)) {
                            $resource     = "";
                            $resourceType = "";
                            if ($linkPurchasedItem->getLinkType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_URL) {
                                $returnArray["url"]      = $linkPurchasedItem->getLinkUrl();
                                $buffer                  = file_get_contents($returnArray["url"]);
                                $fileInfo                = new finfo(FILEINFO_MIME_TYPE);
                                $returnArray["mimeType"] = $fileInfo->buffer($buffer);
                                $fileArray               = explode(DS, $returnArray["url"]);
                                $returnArray["fileName"] = end($fileArray);
                            } elseif ($linkPurchasedItem->getLinkType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                                $linkFile = $downloadableHelperFile->getFilePath($this->_objectManager->create("\Magento\Downloadable\Model\Link")->getBasePath(), $linkPurchasedItem->getLinkFile());
                                $linkFile = $this->_helperCatalog->getBasePath().DS.$linkFile;
                                if (file_exists($linkFile)) {
                                    $returnArray["mimeType"] = mime_content_type($linkFile);
                                    $returnArray["url"]      = $this->_url->getUrl("mobikulhttp/download/index", ["hash"=>$hash]);
                                    $linkFileArr             = explode(DS, $linkFile);
                                    $returnArray["fileName"] = end($linkFileArr);
                                } else {
                                    $returnArray["message"]  = __("An error occurred while getting the requested content. Please contact the store owner.");
                                    return $this->getJsonResponse($returnArray);
                                }
                            }
                        } elseif ($status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED) {
                            $returnArray["message"]  = __("The link has expired.");
                            return $this->getJsonResponse($returnArray);
                        } elseif ($status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING || $status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PAYMENT_REVIEW) {
                            $returnArray["message"]  = __("The link is not available.");
                            return $this->getJsonResponse($returnArray);
                        } else {
                            $returnArray["message"]  = __("An error occurred while getting the requested content. Please contact the store owner.");
                            return $this->getJsonResponse($returnArray);
                        }
                        $returnArray["success"]      = true;
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