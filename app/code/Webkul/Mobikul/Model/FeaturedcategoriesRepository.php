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

    namespace Webkul\Mobikul\Model;
    use Magento\Framework\Exception\NoSuchEntityException;
    use Magento\Framework\Api\ExtensibleDataObjectConverter;
    use Magento\Framework\Api\SearchCriteriaInterface;
    use Webkul\Mobikul\Api\Data\FeaturedcategoriesInterface;

    class FeaturedcategoriesRepository implements \Webkul\Mobikul\Api\FeaturedcategoriesRepositoryInterface     {

        protected $_featuredcategoriesFactory;
        protected $_instances     = [];
        protected $_instancesById = [];
        protected $_collectionFactory;
        protected $_resourceModel;
        protected $_extensibleDataObjectConverter;

        public function __construct(
            FeaturedcategoriesFactory $featuredcategoriesFactory,
            ResourceModel\Featuredcategories\CollectionFactory $collectionFactory,
            ResourceModel\Featuredcategories $resourceModel,
            \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter) {
            $this->_resourceModel                 = $resourceModel;
            $this->_featuredcategoriesFactory     = $featuredcategoriesFactory;
            $this->_collectionFactory             = $collectionFactory;
            $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
        }

        public function save(FeaturedcategoriesInterface $featuredcategories)   {
            $featuredcategoriesId = $featuredcategories->getId();
            try {
                $this->_resourceModel->save($featuredcategories);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
            }
            unset($this->_instancesById[$featuredcategories->getId()]);
            return $this->getById($featuredcategories->getId());
        }

        public function getById($featuredcategoriesId)  {
            $featuredcategoriesData = $this->_featuredcategoriesFactory->create();
            $featuredcategoriesData->load($featuredcategoriesId);
            if (!$featuredcategoriesData->getId()) {}
            $this->_instancesById[$featuredcategoriesId] = $featuredcategoriesData;
            return $this->_instancesById[$featuredcategoriesId];
        }

        public function getList(SearchCriteriaInterface $searchCriteria)    {
            $collection = $this->_collectionFactory->create();
            $collection->load();
            return $collection;
        }

        public function delete(FeaturedcategoriesInterface $featuredcategories)     {
            $featuredcategoriesId = $featuredcategories->getId();
            try {
                $this->_resourceModel->delete($featuredcategories);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\StateException(__("Unable to remove featuredcategories record with id %1", $featuredcategoriesId));
            }
            unset($this->_instancesById[$featuredcategoriesId]);
            return true;
        }

        public function deleteById($featuredcategoriesId)   {
            $featuredcategories = $this->getById($featuredcategoriesId);
            return $this->delete($featuredcategories);
        }

    }