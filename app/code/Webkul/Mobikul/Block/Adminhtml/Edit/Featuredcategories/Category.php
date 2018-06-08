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

    namespace Webkul\Mobikul\Block\Adminhtml\Edit\Featuredcategories;

    use Webkul\Mobikul\Api\FeaturedcategoriesRepositoryInterface;
    use Magento\Catalog\Api\CategoryRepositoryInterface;

    class Category extends \Magento\Backend\Block\Template  {
        /**
        * Block template.
        *
        * @var string
        */
        protected $_template = "featuredcategories/categories.phtml";

        /**
        * @var FeaturedcategoriesRepositoryInterface
        */
        protected $_featuredcategoriesRepository;

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
        * @param FeaturedcategoriesRepositoryInterface   $featuredcategoriesRepository
        * @param CategoryRepositoryInterface             $categoryRepositoryInterface
        * @param \Magento\Catalog\Model\Category         $category
        * @param array                                   $data
        */
        public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            FeaturedcategoriesRepositoryInterface $featuredcategoriesRepository,
            CategoryRepositoryInterface $categoryRepositoryInterface,
            \Magento\Catalog\Model\Category $category,
            array $data=[]) {
            $this->_featuredcategoriesRepository = $featuredcategoriesRepository;
            $this->categoryRepositoryInterface = $categoryRepositoryInterface;
            $this->_category = $category;
            parent::__construct($context, $data);
        }

        /**
        * Featuredcategories initialization
        *
        * @return string featuredcategories id
        */
        protected function initCurrentFeaturedcategories()  {
            return (int)$this->getRequest()->getParam("id");
        }

        /**
        * Return array with category IDs which the product is assigned to.
        *
        * @return array
        */
        public function getCategoryIds()    {
            $featuredcategoriesId = $this->initCurrentFeaturedcategories();
            if ($featuredcategoriesId) {
                return [$this->_featuredcategoriesRepository->getById($featuredcategoriesId)->getCategoryId()];
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