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
    use Webkul\Mobikul\Api\Data\CategoryimagesInterface;

    class CategoryimagesRepository implements \Webkul\Mobikul\Api\CategoryimagesRepositoryInterface     {

        protected $_categoryimagesFactory;
        protected $_instances     = [];
        protected $_instancesById = [];
        protected $_collectionFactory;
        protected $_resourceModel;
        protected $_extensibleDataObjectConverter;

        public function __construct(
            CategoryimagesFactory $categoryimagesFactory,
            ResourceModel\Categoryimages\CollectionFactory $collectionFactory,
            ResourceModel\Categoryimages $resourceModel,
            \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter) {
            $this->_resourceModel                 = $resourceModel;
            $this->_categoryimagesFactory         = $categoryimagesFactory;
            $this->_collectionFactory             = $collectionFactory;
            $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
        }

        public function save(CategoryimagesInterface $categoryimages)   {
            $categoryimagesId = $categoryimages->getId();
            try {
                $this->_resourceModel->save($categoryimages);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
            }
            unset($this->_instancesById[$categoryimages->getId()]);
            return $this->getById($categoryimages->getId());
        }

        public function getById($categoryimagesId)  {
            $categoryimagesData = $this->_categoryimagesFactory->create();
            $categoryimagesData->load($categoryimagesId);
            if (!$categoryimagesData->getId()) {}
            $this->_instancesById[$categoryimagesId] = $categoryimagesData;
            return $this->_instancesById[$categoryimagesId];
        }

        public function getList(SearchCriteriaInterface $searchCriteria)    {
            $collection = $this->_collectionFactory->create();
            $collection->load();
            return $collection;
        }

        public function delete(CategoryimagesInterface $categoryimages)     {
            $categoryimagesId = $categoryimages->getId();
            try {
                $this->_resourceModel->delete($categoryimages);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\StateException(__("Unable to remove categoryimages record with id %1", $categoryimagesId));
            }
            unset($this->_instancesById[$categoryimagesId]);
            return true;
        }

        public function deleteById($categoryimagesId)   {
            $categoryimages = $this->getById($categoryimagesId);
            return $this->delete($categoryimages);
        }

    }