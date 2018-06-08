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

    namespace Webkul\Mobikul\Controller\Adminhtml\Bannerimage;
    use Webkul\Mobikul\Controller\RegistryConstants;
    use Webkul\Mobikul\Api\Data\BannerimageInterface;
    use Magento\Framework\Exception\NoSuchEntityException;

    class Edit extends \Webkul\Mobikul\Controller\Adminhtml\Bannerimage     {

        public function execute()   {
            $bannerimageId    = $this->initCurrentBanner();
            $isExistingBanner = (bool)$bannerimageId;
            if ($isExistingBanner) {
                try {
                    $mobikulDirPath     = $this->_mediaDirectory->getAbsolutePath("mobikul");
                    $bannerimageDirPath = $this->_mediaDirectory->getAbsolutePath("mobikul/bannerimages");
                    if (!file_exists($mobikulDirPath))
                        mkdir($mobikulDirPath, 0777, true);
                    if (!file_exists($bannerimageDirPath))
                        mkdir($bannerimageDirPath, 0777, true);
                    $baseTmpPath     = "";
                    $target          = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$baseTmpPath;
                    $bannerimageData = [];
                    $bannerimageData["mobikul_bannerimage"] = [];
                    $bannerimage     = null;
                    $bannerimage     = $this->_bannerimageRepository->getById($bannerimageId);
                    $result          = $bannerimage->getData();
                    if (count($result)) {
                        $bannerimageData["mobikul_bannerimage"]                        = $result;
                        $bannerimageData["mobikul_bannerimage"]["filename"]            = [];
                        $bannerimageData["mobikul_bannerimage"]["filename"][0]         = [];
                        $bannerimageData["mobikul_bannerimage"]["filename"][0]["name"] = $result["filename"];
                        $bannerimageData["mobikul_bannerimage"]["filename"][0]["url"]  = $target.$result["filename"];
                        $filePath = $this->_mediaDirectory->getAbsolutePath($baseTmpPath).$result["filename"];
                        if (is_file($filePath))
                            $bannerimageData["mobikul_bannerimage"]["filename"][0]["size"] = filesize($filePath);
                        else
                            $bannerimageData["mobikul_bannerimage"]["filename"][0]["size"] = 0;
                        $bannerimageData["mobikul_bannerimage"][BannerimageInterface::ID]  = $bannerimageId;
                        $this->_getSession()->setBannerimageFormData($bannerimageData);
                    } else {
                        $this->messageManager->addError(__("Requested banner doesn't exist"));
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath("mobikul/bannerimage/index");
                        return $resultRedirect;
                    }
                } catch (NoSuchEntityException $e) {
                    $this->messageManager->addException($e, __("Something went wrong while editing the banner."));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath("mobikul/bannerimage/index");
                    return $resultRedirect;
                }
            }
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->setActiveMenu("Webkul_Mobikul::bannerimage");
            $this->prepareDefaultBannerTitle($resultPage);
            $resultPage->setActiveMenu("Webkul_Mobikul::bannerimage");
            if ($isExistingBanner)
                $resultPage->getConfig()->getTitle()->prepend(__("Edit Item with id %1", $bannerimageId));
            else
                $resultPage->getConfig()->getTitle()->prepend(__("New Banner"));
            return $resultPage;
        }

        protected function initCurrentBanner()  {
            $bannerimageId = (int)$this->getRequest()->getParam("id");
            if ($bannerimageId)
                $this->_coreRegistry->register(RegistryConstants::CURRENT_BANNER_ID, $bannerimageId);
            return $bannerimageId;
        }

        protected function prepareDefaultBannerTitle(\Magento\Backend\Model\View\Result\Page $resultPage) {
            $resultPage->getConfig()->getTitle()->prepend(__("Banner Image"));
        }

    }