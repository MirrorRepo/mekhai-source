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

    class MassDelete extends \Webkul\Mobikul\Controller\Adminhtml\Bannerimage    {

        public function execute()   {
            $resultRedirect = $this->resultRedirectFactory->create();
            $collection     = $this->_filter->getCollection($this->_collectionFactory->create());
            $bannersDeleted = 0;
            foreach ($collection->getAllIds() as $bannerimageId) {
                if (!empty($bannerimageId)) {
                    try {
                        $this->_bannerimageRepository->deleteById($bannerimageId);
                        $this->messageManager->addSuccess(__("Banner has been deleted."));
                        $bannersDeleted++;
                    } catch (\Exception $exception) {
                        $this->messageManager->addError($exception->getMessage());
                    }
                }
            }
            if ($bannersDeleted)
                $this->messageManager->addSuccess(__("A total of %1 record(s) were deleted.", $bannersDeleted));
            return $resultRedirect->setPath("mobikul/bannerimage/index");
        }

        protected function _isAllowed()     {
            return $this->_authorization->isAllowed("Webkul_Mobikul::bannerimage");
        }

    }