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

    namespace Webkul\Mobikul\Controller\Adminhtml\Featuredcategories;
    use Webkul\Mobikul\Controller\RegistryConstants;
    use Magento\Framework\Exception\NoSuchEntityException;
    use Webkul\Mobikul\Api\Data\FeaturedcategoriesInterface;

    class Edit extends \Webkul\Mobikul\Controller\Adminhtml\Featuredcategories  {

        public function execute()   {
            $featuredcategoriesId         = $this->initCurrentFeaturedcategories();
            $isExistingFeaturedcategories = (bool)$featuredcategoriesId;
            if ($isExistingFeaturedcategories) {
                try {
                    $mobikulDirPath            = $this->_mediaDirectory->getAbsolutePath("mobikul");
                    $featuredcategoriesDirPath = $this->_mediaDirectory->getAbsolutePath("mobikul/featuredcategories");
                    if (!file_exists($mobikulDirPath))
                        mkdir($mobikulDirPath, 0777, true);
                    if (!file_exists($featuredcategoriesDirPath))
                        mkdir($featuredcategoriesDirPath, 0777, true);
                    $baseTmpPath            = "";
                    $target                 = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$baseTmpPath;
                    $featuredcategoriesData = [];
                    $featuredcategoriesData["mobikul_featuredcategories"] = [];
                    $featuredcategories     = null;
                    $featuredcategories     = $this->_featuredcategoriesRepository->getById($featuredcategoriesId);
                    $result                 = $featuredcategories->getData();
                    if (count($result)) {
                        $featuredcategoriesData["mobikul_featuredcategories"] = $result;
                        $featuredcategoriesData["mobikul_featuredcategories"]["filename"] = [];
                        $featuredcategoriesData["mobikul_featuredcategories"]["filename"][0] = [];
                        $featuredcategoriesData["mobikul_featuredcategories"]["filename"][0]["name"] =
                        $result["filename"];
                        $featuredcategoriesData["mobikul_featuredcategories"]["filename"][0]["url"] =
                        $target.$result["filename"];
                        $filePath = $this->_mediaDirectory->getAbsolutePath($baseTmpPath).$result["filename"];
                        if (file_exists($filePath)) {
                            $featuredcategoriesData["mobikul_featuredcategories"]["filename"][0]["size"] =
                            filesize($filePath);
                        } else {
                            $featuredcategoriesData["mobikul_featuredcategories"]["filename"][0]["size"] = 0;
                        }
                        $featuredcategoriesData["mobikul_featuredcategories"][FeaturedcategoriesInterface::ID] = $featuredcategoriesId;
                        $this->_getSession()->setFeaturedcategoriesFormData($featuredcategoriesData);
                    } else {
                        $this->messageManager->addError(__("Requested featuredcategories doesn\"t exist"));
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath("mobikul/featuredcategories/index");
                        return $resultRedirect;
                    }
                } catch (NoSuchEntityException $e) {
                    $this->messageManager->addException($e, __("Something went wrong while editing the featuredcategories."));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath("mobikul/featuredcategories/index");
                    return $resultRedirect;
                }
            }
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->setActiveMenu("Webkul_Mobikul::featuredcategories");
            $this->prepareDefaultFeaturedcategoriesTitle($resultPage);
            $resultPage->setActiveMenu("Webkul_Mobikul::featuredcategories");
            if ($isExistingFeaturedcategories)
                $resultPage->getConfig()->getTitle()->prepend(__("Edit Item with id %1", $featuredcategoriesId));
            else
                $resultPage->getConfig()->getTitle()->prepend(__("New Featuredcategories"));
            return $resultPage;
        }

        protected function initCurrentFeaturedcategories()  {
            $featuredcategoriesId = (int)$this->getRequest()->getParam("id");
            if ($featuredcategoriesId)
                $this->_coreRegistry->register(RegistryConstants::CURRENT_FEATUREDCATEGORIES_ID, $featuredcategoriesId);
            return $featuredcategoriesId;
        }

        protected function prepareDefaultFeaturedcategoriesTitle(\Magento\Backend\Model\View\Result\Page $resultPage) {
            $resultPage->getConfig()->getTitle()->prepend(__("Featuredcategories"));
        }

    }