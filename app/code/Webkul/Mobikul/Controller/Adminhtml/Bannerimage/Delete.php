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

    class Delete extends \Webkul\Mobikul\Controller\Adminhtml\Bannerimage   {

        public function execute()   {
            $resultRedirect = $this->resultRedirectFactory->create();
            $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
            $isPost = $this->getRequest()->isPost();
            if (!$formKeyIsValid || !$isPost) {
                $this->messageManager->addError(__("Banner could not be deleted."));
                return $resultRedirect->setPath("mobikul/bannerimage/index");
            }
            $bannerimageId = $this->initCurrentBanner();
            if (!empty($bannerimageId)) {
                try {
                    $this->_bannerimageRepository->deleteById($bannerimageId);
                    $this->messageManager->addSuccess(__("Banner has been deleted."));
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
            return $resultRedirect->setPath("mobikul/bannerimage/index");
        }

        protected function initCurrentBanner()  {
            $bannerimageId = (int)$this->getRequest()->getParam("id");
            if ($bannerimageId)
                $this->_coreRegistry->register(RegistryConstants::CURRENT_BANNER_ID, $bannerimageId);
            return $bannerimageId;
        }

    }