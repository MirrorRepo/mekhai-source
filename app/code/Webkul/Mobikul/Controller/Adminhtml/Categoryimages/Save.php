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

    class Save extends \Webkul\Mobikul\Controller\Adminhtml\Categoryimages      {

        public function execute()   {
            $returnToEdit        = false;
            $originalRequestData = $this->getRequest()->getPostValue();
            $categoryimagesId    = isset($originalRequestData["mobikul_categoryimages"]["id"]) ? $originalRequestData["mobikul_categoryimages"]["id"] : null;
            if ($originalRequestData) {
                try {
                    $categoryimagesData                  = $originalRequestData["mobikul_categoryimages"];
                    $categoryimagesData["icon"]          = $this->getCategoryIconImageName($categoryimagesData);
                    $categoryimagesData["banner"]        = $this->getCategoryBannerImageName($categoryimagesData);
                    $categoryData                        = $this->getCategoryData($categoryimagesData);
                    $categoryimagesData["category_name"] = $categoryData->getName();
                    $request                             = $this->getRequest();
                    $isExistingCategoryimages            = (bool) $categoryimagesId;
                    $categoryimages                      = $this->_categoryimagesDataFactory->create();
                    if ($isExistingCategoryimages) {
                        $currentCategoryimages = $this->_categoryimagesRepository->getById($categoryimagesId);
                        $categoryimagesData["id"] = $categoryimagesId;
                    }
                    $categoryimagesData["updated_at"] = $this->_date->gmtDate();
                    if (!$isExistingCategoryimages)
                        $categoryimagesData["created_at"] = $this->_date->gmtDate();
                    $categoryimages->setData($categoryimagesData);
// Save categoryimages //////////////////////////////////////////////////////////////////////////////////////////////////////////
                    if ($isExistingCategoryimages)
                        $this->_categoryimagesRepository->save($categoryimages);
                    else {
                        $categoryimages = $this->_categoryimagesRepository->save($categoryimages);
                        $categoryimagesId = $categoryimages->getId();
                    }
                    $this->_getSession()->unsCategoryimagesFormData();
// Done Saving categoryimages, finish save action ///////////////////////////////////////////////////////////////////////////////
                    $this->_coreRegistry->register(RegistryConstants::CURRENT_FEATUREDCATEGORIES_ID, $categoryimagesId);
                    $this->messageManager->addSuccess(__("You saved the category image."));
                    $returnToEdit = (bool) $this->getRequest()->getParam("back", false);
                } catch (\Magento\Framework\Validator\Exception $exception) {
                    $messages = $exception->getMessages();
                    if (empty($messages))
                        $messages = $exception->getMessage();
                    $this->_addSessionErrorMessages($messages);
                    $this->_getSession()->setCategoryimagesFormData($originalRequestData);
                    $returnToEdit = true;
                } catch (\Exception $exception) {
                    $this->messageManager->addException($exception, __("Something went wrong while saving the category images. %1", $exception->getMessage()));
                    $this->_getSession()->setCategoryimagesFormData($originalRequestData);
                    $returnToEdit = true;
                }
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            if ($returnToEdit) {
                if ($categoryimagesId)
                    $resultRedirect->setPath("mobikul/categoryimages/edit", ["id"=>$categoryimagesId, "_current"=>true]);
                else
                    $resultRedirect->setPath("mobikul/categoryimages/new",["_current"=>true]);
            } else
                $resultRedirect->setPath("mobikul/categoryimages/index");
            return $resultRedirect;
        }

        private function getCategoryIconImageName($categoryimagesData)  {
            if (isset($categoryimagesData["icon"][0]["name"])) {
                if (isset($categoryimagesData["icon"][0]["name"]))
                    return $categoryimagesData["icon"] = $categoryimagesData["icon"][0]["name"];
                else
                    throw new \Magento\Framework\Exception\LocalizedException(__("Please upload category icon image."));
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("Please upload category icon image."));
            }
        }

        private function getCategoryBannerImageName($categoryimagesData)    {
            if (isset($categoryimagesData["banner"][0]["name"])) {
                if (isset($categoryimagesData["banner"][0]["name"]))
                    return $categoryimagesData["banner"] = $categoryimagesData["banner"][0]["name"];
                else
                    throw new \Magento\Framework\Exception\LocalizedException(__("Please upload category banner image."));
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("Please upload category banner image."));
            }
        }

        private function getCategoryData($categoryimagesData)   {
            if (isset($categoryimagesData["category_id"])) {
                if ($categoryimagesData["category_id"]) {
                    try {
                        return $this->_categoryRepository->get($categoryimagesData["category_id"]);
                    } catch (\Exception $exception) {
                        throw new \Magento\Framework\Exception\LocalizedException(__("Requested category doesn\'t exist"));
                    }
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("Category id should be set."));
            }
        }

    }