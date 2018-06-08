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

    class DownloadLinkSample extends \Webkul\Mobikul\Controller\ApiController   {

        public function __construct(
            Context $context,
            HelperData $helper,
            Emulation $emulate,
            HelperCatalog $helperCatalog
        ) {
            parent::__construct($helper, $context);
        }

        public function execute()   {
            $linkId             = $this->getRequest()->getParam("linkId");
            $link               = $this->_objectManager->create("\Magento\Downloadable\Model\Link")->load($linkId);
            $sampleLinkFilePath = $this->_objectManager->create("\Magento\Downloadable\Helper\File")->getFilePath($this->_objectManager->create("\Magento\Downloadable\Model\Link")->getBaseSamplePath(), $link->getSampleFile());
            $sampleLinkFilePath = $this->_helperCatalog->getBasePath()."/".$sampleLinkFilePath;
            $resourceType       = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
            $helper             = $this->_objectManager->get("Magento\Downloadable\Helper\Download");
            $helper->setResource($sampleLinkFilePath, $resourceType);
            $fileName           = $helper->getFilename();
            $contentType        = $helper->getContentType();
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
            exit(0);
        }

    }