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

    class Save extends \Webkul\Mobikul\Controller\Adminhtml\Bannerimage     {

        public function execute()   {
            $returnToEdit        = false;
            $originalRequestData = $this->getRequest()->getPostValue();
            $bannerimageId       = isset($originalRequestData["mobikul_bannerimage"]["id"]) ? $originalRequestData["mobikul_bannerimage"]["id"] : null;
            if ($originalRequestData) {
                try {
                    $bannerimageData  = $originalRequestData["mobikul_bannerimage"];
                    $imageName        = $this->getBannerImageName($bannerimageData);
                    if(strpos($imageName, "mobikul/bannerimages/") !== false)
                        $bannerimageData["filename"] = $imageName;
                    else
                        $bannerimageData["filename"] = "mobikul/bannerimages/".$imageName;
                    $bannerimageData["store_id"] = $this->getBannerStoreId($bannerimageData);
                    $request          = $this->getRequest();
                    $isExistingBanner = (bool) $bannerimageId;
                    $bannerimage      = $this->_bannerimageDataFactory->create();
                    if ($isExistingBanner) {
                        $currentBanner = $this->_bannerimageRepository->getById($bannerimageId);
                        $bannerimageData["id"] = $bannerimageId;
                    }
                    $bannerimage->setData($bannerimageData);
// Save banner //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    if ($isExistingBanner)
                        $this->_bannerimageRepository->save($bannerimage);
                    else {
                        $bannerimage   = $this->_bannerimageRepository->save($bannerimage);
                        $bannerimageId = $bannerimage->getId();
                    }
                    $this->_getSession()->unsBannerimageFormData();
// Done Saving bannerimage, finish save action //////////////////////////////////////////////////////////////////////////////////
                    $this->_coreRegistry->register(RegistryConstants::CURRENT_BANNER_ID, $bannerimageId);
                    $this->messageManager->addSuccess(__("You saved the banner."));
                    $returnToEdit = (bool) $this->getRequest()->getParam("back", false);
                } catch (\Magento\Framework\Validator\Exception $exception) {
                    $messages = $exception->getMessages();
                    if (empty($messages))
                        $messages = $exception->getMessage();
                    $this->_addSessionErrorMessages($messages);
                    $this->_getSession()->setBannerimageFormData($originalRequestData);
                    $returnToEdit = true;
                } catch (\Exception $exception) {
                    $this->messageManager->addException($exception, __("Something went wrong while saving the banner. %1", $exception->getMessage()));
                    $this->_getSession()->setBannerimageFormData($originalRequestData);
                    $returnToEdit = true;
                }
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            if ($returnToEdit) {
                if ($bannerimageId)
                    $resultRedirect->setPath("mobikul/bannerimage/edit", ["id"=>$bannerimageId, "_current"=>true]);
                else
                    $resultRedirect->setPath("mobikul/bannerimage/new", ["_current"=>true]);
            } else
                $resultRedirect->setPath("mobikul/bannerimage/index");
            return $resultRedirect;
        }

        private function getBannerImageName($bannerimageData)   {
            if (isset($bannerimageData["filename"][0]["name"])) {
                if (isset($bannerimageData["filename"][0]["name"]))
                    return $bannerimageData["filename"] = $bannerimageData["filename"][0]["name"];
                else
                    throw new \Magento\Framework\Exception\LocalizedException(__("Please upload banner image."));
            } else
                throw new \Magento\Framework\Exception\LocalizedException(__("Please upload banner image."));
        }

        private function getBannerStoreId($bannerimageData)     {
            if (isset($bannerimageData["store_id"]))
                return $bannerimageData["store_id"] = implode(",", $bannerimageData["store_id"]);
            else
                return $bannerimageData["store_id"] = 0;
        }

    }