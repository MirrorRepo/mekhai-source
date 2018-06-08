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
    use Magento\Framework\Controller\ResultFactory;
    use Webkul\Mobikul\Controller\RegistryConstants;
    use Webkul\Mobikul\Api\Data\FeaturedcategoriesInterface;

    class Delete extends \Webkul\Mobikul\Controller\Adminhtml\Featuredcategories    {

        public function execute()   {
            $resultRedirect = $this->resultRedirectFactory->create();
            $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
            $isPost         = $this->getRequest()->isPost();
            if (!$formKeyIsValid || !$isPost) {
                $this->messageManager->addError(__("Featuredcategories could not be deleted."));
                return $resultRedirect->setPath("mobikul/featuredcategories/index");
            }
            $featuredcategoriesId = $this->initCurrentFeaturedcategories();
            if (!empty($featuredcategoriesId)) {
                try {
                    $this->_featuredcategoriesRepository->deleteById($featuredcategoriesId);
                    $this->messageManager->addSuccess(__("Featuredcategories has been deleted."));
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
            return $resultRedirect->setPath("mobikul/featuredcategories/index");
        }

        protected function initCurrentFeaturedcategories()  {
            $featuredcategoriesId = (int)$this->getRequest()->getParam("id");
            if ($featuredcategoriesId)
                $this->_coreRegistry->register(RegistryConstants::CURRENT_FEATUREDCATEGORIES_ID, $featuredcategoriesId);
            return $featuredcategoriesId;
        }

    }