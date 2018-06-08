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

    namespace Webkul\Mobikul\Controller\Adminhtml\Categoryimages;
    use Webkul\Mobikul\Controller\RegistryConstants;
    use Webkul\Mobikul\Api\Data\CategoryimagesInterface;
    use Magento\Framework\Exception\NoSuchEntityException;

    class Edit extends \Webkul\Mobikul\Controller\Adminhtml\Categoryimages      {

        public function execute()   {
            $categoryimagesId         = $this->initCurrentCategoryimages();
            $isExistingCategoryimages = (bool)$categoryimagesId;
            if ($isExistingCategoryimages) {
                try {
                    $mobikulDirPath             = $this->_mediaDirectory->getAbsolutePath("mobikul");
                    $categoryimagesDirPath      = $this->_mediaDirectory->getAbsolutePath("mobikul/categoryimages");
                    $categoryIconImageDirPath   = $this->_mediaDirectory->getAbsolutePath("mobikul/categoryimages/icon");
                    $categoryBannerImageDirPath = $this->_mediaDirectory->getAbsolutePath("mobikul/categoryimages/banner");
                    if (!file_exists($mobikulDirPath))
                        mkdir($mobikulDirPath, 0777, true);
                    if (!file_exists($categoryimagesDirPath))
                        mkdir($categoryimagesDirPath, 0777, true);
                    if (!file_exists($categoryIconImageDirPath))
                        mkdir($categoryIconImageDirPath, 0777, true);
                    if (!file_exists($categoryBannerImageDirPath))
                        mkdir($categoryBannerImageDirPath, 0777, true);
                    $iconBaseTmpPath    = "mobikul/categoryimages/icon/";
                    $iconTarget         = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$iconBaseTmpPath;
                    $bannerBaseTmpPath  = "mobikul/categoryimages/banner/";
                    $bannerTarget       = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$bannerBaseTmpPath;
                    $categoryimagesData = [];
                    $categoryimagesData["mobikul_categoryimages"] = [];
                    $categoryimages     = null;
                    $categoryimages     = $this->_categoryimagesRepository->getById($categoryimagesId);
                    $result             = $categoryimages->getData();
                    if (count($result)) {
                        $categoryimagesData["mobikul_categoryimages"] = $result;
                        $categoryimagesData["mobikul_categoryimages"]["icon"] = [];
                        $categoryimagesData["mobikul_categoryimages"]["icon"][0] = [];
                        $categoryimagesData["mobikul_categoryimages"]["icon"][0]["name"] = $result["icon"];
                        $categoryimagesData["mobikul_categoryimages"]["icon"][0]["url"] = $iconTarget.$result["icon"];
                        $iconFilePath = $this->_mediaDirectory->getAbsolutePath($iconBaseTmpPath).$result["icon"];
                        if (file_exists($iconFilePath)) {
                            $categoryimagesData["mobikul_categoryimages"]["icon"][0]["size"] =
                            filesize($iconFilePath);
                        } else
                            $categoryimagesData["mobikul_categoryimages"]["icon"][0]["size"] = 0;
                        $categoryimagesData["mobikul_categoryimages"]["banner"]              = [];
                        $categoryimagesData["mobikul_categoryimages"]["banner"][0]           = [];
                        $categoryimagesData["mobikul_categoryimages"]["banner"][0]["name"]   = $result["banner"];
                        $categoryimagesData["mobikul_categoryimages"]["banner"][0]["url"]    = $bannerTarget.$result["banner"];
                        $bannerFilePath = $this->_mediaDirectory->getAbsolutePath($bannerBaseTmpPath).$result["banner"];
                        if (file_exists($bannerFilePath)) {
                            $categoryimagesData["mobikul_categoryimages"]["banner"][0]["size"] =
                            filesize($bannerFilePath);
                        } else
                            $categoryimagesData["mobikul_categoryimages"]["banner"][0]["size"] = 0;
                        $categoryimagesData["mobikul_categoryimages"][CategoryimagesInterface::ID] = $categoryimagesId;
                        $this->_getSession()->setCategoryimagesFormData($categoryimagesData);
                    } else {
                        $this->messageManager->addError(__("Requested categoryimages doesn\"t exist"));
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath("mobikul/categoryimages/index");
                        return $resultRedirect;
                    }
                } catch (NoSuchEntityException $e) {
                    $this->messageManager->addException($e, __("Something went wrong while editing the category image."));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath("mobikul/categoryimages/index");
                    return $resultRedirect;
                }
            }
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->setActiveMenu("Webkul_Mobikul::categoryimages");
            $this->prepareDefaultCategoryimagesTitle($resultPage);
            $resultPage->setActiveMenu("Webkul_Mobikul::categoryimages");
            if ($isExistingCategoryimages)
                $resultPage->getConfig()->getTitle()->prepend(__("Edit Item with id %1", $categoryimagesId));
            else
                $resultPage->getConfig()->getTitle()->prepend(__("New Category Image"));
            return $resultPage;
        }

        protected function initCurrentCategoryimages()  {
            $categoryimagesId = (int)$this->getRequest()->getParam("id");
            if ($categoryimagesId)
                $this->_coreRegistry->register(RegistryConstants::CURRENT_CATEGORYIMAGES_ID, $categoryimagesId);
            return $categoryimagesId;
        }

        protected function prepareDefaultCategoryimagesTitle(\Magento\Backend\Model\View\Result\Page $resultPage) {
            $resultPage->getConfig()->getTitle()->prepend(__("Category Image"));
        }

    }