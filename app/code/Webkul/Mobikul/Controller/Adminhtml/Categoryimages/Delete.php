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

    class Delete extends \Webkul\Mobikul\Controller\Adminhtml\Categoryimages    {

        public function execute()   {
            $resultRedirect = $this->resultRedirectFactory->create();
            $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
            $isPost         = $this->getRequest()->isPost();
            if (!$formKeyIsValid || !$isPost) {
                $this->messageManager->addError(__("Category image record could not be deleted."));
                return $resultRedirect->setPath("mobikul/categoryimages/index");
            }
            $categoryimagesId = $this->initCurrentCategoryimages();
            if (!empty($categoryimagesId)) {
                try {
                    $this->_categoryimagesRepository->deleteById($categoryimagesId);
                    $this->messageManager->addSuccess(__("Category image record has been deleted."));
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
            return $resultRedirect->setPath("mobikul/categoryimages/index");
        }

        protected function initCurrentCategoryimages()  {
            $categoryimagesId = (int)$this->getRequest()->getParam("id");
            if ($categoryimagesId)
                $this->_coreRegistry->register(RegistryConstants::CURRENT_CATEGORYIMAGES_ID, $categoryimagesId);
            return $categoryimagesId;
        }

    }