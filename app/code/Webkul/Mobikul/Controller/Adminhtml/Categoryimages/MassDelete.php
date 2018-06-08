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
    use Magento\Framework\Controller\ResultFactory;

    class MassDelete extends \Webkul\Mobikul\Controller\Adminhtml\Categoryimages    {

        public function execute()   {
            $resultRedirect         = $this->resultRedirectFactory->create();
            $collection             = $this->_filter->getCollection($this->_collectionFactory->create());
            $categoryimagessDeleted = 0;
            foreach ($collection->getAllIds() as $categoryimagesId) {
                if (!empty($categoryimagesId)) {
                    try {
                        $this->_categoryimagesRepository->deleteById($categoryimagesId);
                        $categoryimagessDeleted++;
                    } catch (\Exception $exception) {
                        $this->messageManager->addError($exception->getMessage());
                    }
                }
            }
            if ($categoryimagessDeleted)
                $this->messageManager->addSuccess(__("A total of %1 record(s) were deleted.", $categoryimagessDeleted));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath("mobikul/categoryimages/index");
        }

        protected function _isAllowed()     {
            return $this->_authorization->isAllowed("Webkul_Mobikul::categoryimages");
        }

    }