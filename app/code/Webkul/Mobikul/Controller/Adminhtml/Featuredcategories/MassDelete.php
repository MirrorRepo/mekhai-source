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
    use Magento\Backend\App\Action\Context;
    use Magento\Ui\Component\MassAction\Filter;
    use Magento\Framework\Controller\ResultFactory;
    use Webkul\Mobikul\Api\FeaturedcategoriesRepositoryInterface;
    use Webkul\Mobikul\Model\ResourceModel\Featuredcategories\CollectionFactory;

    class MassDelete extends \Magento\Backend\App\Action        {

        protected $filter;
        protected $collectionFactory;
        protected $_featuredcategoriesRepository;

        public function __construct(
            Filter $filter,
            Context $context,
            CollectionFactory $collectionFactory,
            FeaturedcategoriesRepositoryInterface $featuredcategoriesRepository
        ) {
            $this->filter                        = $filter;
            $this->collectionFactory             = $collectionFactory;
            $this->_featuredcategoriesRepository = $featuredcategoriesRepository;
            parent::__construct($context);
        }

        public function execute()       {
            $resultRedirect             = $this->resultRedirectFactory->create();
            $collection                 = $this->filter->getCollection($this->collectionFactory->create());
            $featuredcategoriessDeleted = 0;
            foreach ($collection->getAllIds() as $featuredcategoriesId) {
                if (!empty($featuredcategoriesId)) {
                    try {
                        $this->_featuredcategoriesRepository->deleteById($featuredcategoriesId);
                        $featuredcategoriessDeleted++;
                    } catch (\Exception $exception) {
                        $this->messageManager->addError($exception->getMessage());
                    }
                }
            }
            if ($featuredcategoriessDeleted)
                $this->messageManager->addSuccess(__("A total of %1 Featuredcategory(ies) were deleted.", $featuredcategoriessDeleted));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath("mobikul/featuredcategories/index");
        }

        protected function _isAllowed() {
            return $this->_authorization->isAllowed('Webkul_Mobikul::featuredcategories');
        }

    }