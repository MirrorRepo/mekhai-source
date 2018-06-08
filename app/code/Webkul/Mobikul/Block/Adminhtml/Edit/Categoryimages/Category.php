<?php
    /**
    * Webkul Software.
    *
    * @category Webkul
    *
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Block\Adminhtml\Edit\Categoryimages;

    use Webkul\Mobikul\Api\CategoryimagesRepositoryInterface;
    use Magento\Catalog\Api\CategoryRepositoryInterface;

    class Category extends \Magento\Backend\Block\Template  {
        /**
        * Block template.
        *
        * @var string
        */
        protected $_template = "categoryimages/categories.phtml";

        /**
        * @var CategoryimagesRepositoryInterface
        */
        protected $_categoryimagesRepository;

        /**
        * @var CategoryRepositoryInterface
        */
        protected $categoryRepositoryInterface;

        /**
        * @var \Magento\Catalog\Model\Category
        */
        protected $_category;

        /**
        * AssignProducts constructor.
        *
        * @param \Magento\Backend\Block\Template\Context $context
        * @param CategoryimagesRepositoryInterface       $categoryimagesRepository
        * @param CategoryRepositoryInterface             $categoryRepositoryInterface
        * @param \Magento\Catalog\Model\Category         $category
        * @param array                                   $data
        */
        public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            CategoryimagesRepositoryInterface $categoryimagesRepository,
            CategoryRepositoryInterface $categoryRepositoryInterface,
            \Magento\Catalog\Model\Category $category,
            array $data=[]) {
            $this->_categoryimagesRepository = $categoryimagesRepository;
            $this->categoryRepositoryInterface = $categoryRepositoryInterface;
            $this->_category = $category;
            parent::__construct($context, $data);
        }

        /**
        * Categoryimages initialization
        *
        * @return string categoryimages id
        */
        protected function initCurrentCategoryimages()  {
            $categoryimagesId = (int)$this->getRequest()->getParam("id");
            return $categoryimagesId;
        }

        /**
        * Return array with category IDs which the product is assigned to.
        *
        * @return array
        */
        public function getCategoryIds()    {
            $categoryimagesId = $this->initCurrentCategoryimages();
            if ($categoryimagesId) {
                return [$this->_categoryimagesRepository->getById($categoryimagesId)->getCategoryId()];
            } else {
                return [];
            }
        }

        public function getCategory()   {
            return $this->_category;
        }

        public function getCategoryData($categoryId)    {
            return $this->categoryRepositoryInterface->get($categoryId);
        }

    }