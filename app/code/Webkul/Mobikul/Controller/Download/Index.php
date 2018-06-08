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

    namespace Webkul\Mobikul\Controller\Download;
    use Magento\Store\Model\App\Emulation;
    use Magento\Framework\App\Action\Context;
    use Webkul\Mobikul\Helper\Data as HelperData;
    use Webkul\Mobikul\Helper\Catalog as HelperCatalog;

    class Index extends \Webkul\Mobikul\Controller\ApiController    {

        public function __construct(
            Context $context,
            HelperData $helper,
            Emulation $emulate,
            HelperCatalog $helperCatalog
        ) {
            parent::__construct($helper, $context);
        }

        public function execute()   {
            $wholeData   = $this->getRequest()->getParams();
            $authKey     = $this->getRequest()->getHeader("authKey");
            $apiKey      = $this->getRequest()->getHeader("apiKey");
            $apiPassword = $this->getRequest()->getHeader("apiPassword");
            $authData    = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
            if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                $linkFile          = "";
                $fileName          = "";
                $hash              = $wholeData["hash"];
                $linkPurchasedItem = $this->_objectManager->create("\Magento\Downloadable\Model\Link\Purchased\Item")->load($hash, "link_hash");
                $downloadsLeft     = $linkPurchasedItem->getNumberOfDownloadsBought() - $linkPurchasedItem->getNumberOfDownloadsUsed();
                $status            = $linkPurchasedItem->getStatus();
                if ($status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_AVAILABLE && ($downloadsLeft || $linkPurchasedItem->getNumberOfDownloadsBought() == 0)) {
                    if ($linkPurchasedItem->getLinkType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                        $linkFile  = $this->_objectManager->create("\Magento\Downloadable\Helper\File")->getFilePath($this->_objectManager->create("\Magento\Downloadable\Model\Link")->getBasePath(), $linkPurchasedItem->getLinkFile());
                        $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
                        $helper       = $this->_objectManager->get("Magento\Downloadable\Helper\Download");
                        $helper->setResource($linkFile, $resourceType);
                        $fileName     = $helper->getFilename();
                        $contentType  = $helper->getContentType();
                        $this->getResponse()->setHttpResponseCode(200)
                            ->setHeader("Pragma", "public", true)
                            ->setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0", true)
                            ->setHeader("Content-type", $contentType, true);
                        if ($fileSize = $helper->getFileSize())
                            $this->getResponse()->setHeader("Content-Length", $fileSize);
                        if ($contentDisposition = $helper->getContentDisposition())
                            $this->getResponse()->setHeader("Content-Disposition", $contentDisposition . "; filename=" . $fileName);
                        $this->getResponse()->clearBody();
                        $this->getResponse()->sendHeaders();
                        $helper->output();
                        $linkPurchasedItem->setNumberOfDownloadsUsed($linkPurchasedItem->getNumberOfDownloadsUsed() + 1);
                        if ($linkPurchasedItem->getNumberOfDownloadsBought() != 0 && !($downloadsLeft - 1))
                            $linkPurchasedItem->setStatus(\Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED);
                        $linkPurchasedItem->save();
                    }
                }
                exit(0);
            }
        }

    }