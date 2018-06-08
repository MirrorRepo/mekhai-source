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
    use Webkul\Mobikul\Api\Data\BannerimageInterface;

    class BannerimageRepository implements \Webkul\Mobikul\Api\BannerimageRepositoryInterface   {

        protected $_bannerimageFactory;
        protected $_instances     = [];
        protected $_instancesById = [];
        protected $_collectionFactory;
        protected $_resourceModel;
        protected $_extensibleDataObjectConverter;

        public function __construct(
            BannerimageFactory $bannerimageFactory,
            ResourceModel\Bannerimage\CollectionFactory $collectionFactory,
            ResourceModel\Bannerimage $resourceModel,
            \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter) {
            $this->_resourceModel                 = $resourceModel;
            $this->_bannerimageFactory            = $bannerimageFactory;
            $this->_collectionFactory             = $collectionFactory;
            $this->_extensibleDataObjectConverter = $extensibleDataObjectConverter;
        }

        public function save(BannerimageInterface $bannerimage)     {
            $bannerimageId = $bannerimage->getId();
            try {
                $this->_resourceModel->save($bannerimage);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\CouldNotSaveException($e->getMessage());
            }
            unset($this->_instancesById[$bannerimage->getId()]);
            return $this->getById($bannerimage->getId());
        }

        public function getById($bannerimageId)     {
            $bannerimageData = $this->_bannerimageFactory->create();
            $bannerimageData->load($bannerimageId);
            if (!$bannerimageData->getId()) {}
            $this->_instancesById[$bannerimageId] = $bannerimageData;
            return $this->_instancesById[$bannerimageId];
        }

        public function getList(SearchCriteriaInterface $searchCriteria)    {
            $collection = $this->_collectionFactory->create();
            $collection->load();
            return $collection;
        }

        public function delete(BannerimageInterface $bannerimage)   {
            $bannerimageId = $bannerimage->getId();
            try {
                $this->_resourceModel->delete($bannerimage);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\StateException(__("Unable to remove banner image with id %1", $bannerimageId));
            }
            unset($this->_instancesById[$bannerimageId]);
            return true;
        }

        public function deleteById($bannerimageId)  {
            $bannerimage = $this->getById($bannerimageId);
            return $this->delete($bannerimage);
        }

    }