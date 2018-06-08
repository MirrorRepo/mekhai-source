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

    class MassDisable extends \Magento\Backend\App\Action   {

        protected $_date;
        protected $filter;
        protected $collectionFactory;
        protected $_featuredcategoriesRepository;

        public function __construct(
            Filter $filter,
            Context $context,
            CollectionFactory $collectionFactory,
            \Magento\Framework\Stdlib\DateTime\DateTime $date,
            FeaturedcategoriesRepositoryInterface $featuredcategoriesRepository
        ) {
            $this->_date                         = $date;
            $this->filter                        = $filter;
            $this->collectionFactory             = $collectionFactory;
            $this->_featuredcategoriesRepository = $featuredcategoriesRepository;
            parent::__construct($context);
        }

        public function execute()   {
            $resultRedirect             = $this->resultRedirectFactory->create();
            $collection                 = $this->filter->getCollection($this->collectionFactory->create());
            $featuredcategoriessUpdated = 0;
            $coditionArr                = [];
            foreach ($collection->getAllIds() as $key => $featuredcategoriesId) {
                $currentFeaturedcategories = $this->_featuredcategoriesRepository->getById($featuredcategoriesId);
                $featuredcategoriesData = $currentFeaturedcategories->getData();
                if (count($featuredcategoriesData)) {
                    $condition = "`id`=".$featuredcategoriesId;
                    array_push($coditionArr, $condition);
                    $featuredcategoriessUpdated++;
                }
            }
            $coditionData = implode(" OR ", $coditionArr);
            $collection->setFeaturedcategoriesData($coditionData, ["status"=>0]);
            if ($featuredcategoriessUpdated)
                $this->messageManager->addSuccess(__("A total of %1 record(s) were disabled.", $featuredcategoriessUpdated));
            return $resultRedirect->setPath("mobikul/featuredcategories/index");
        }

        protected function _isAllowed() {
            return $this->_authorization->isAllowed("Webkul_Mobikul::featuredcategories");
        }

    }