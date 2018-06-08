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

    namespace Webkul\Mobikul\Model\ResourceModel\Categoryimages\Grid;
    use Magento\Framework\Api\Search\SearchResultInterface;
    use Magento\Framework\Search\AggregationInterface;
    use Webkul\Mobikul\Model\ResourceModel\Categoryimages\Collection as CategoryimagesCollection;

    class Collection extends CategoryimagesCollection implements SearchResultInterface  {

        protected $_aggregations;
        protected $_objectManager;

        public function __construct(
            \Magento\Framework\ObjectManagerInterface $objectManager,
            \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
            \Psr\Log\LoggerInterface $logger,
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
            \Magento\Framework\Event\ManagerInterface $eventManager,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            $mainTable,
            $eventPrefix,
            $eventObject,
            $resourceModel,
            $model = "Magento\Framework\View\Element\UiComponent\DataProvider\Document",
            $connection = null,
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
        ) {
            $this->_objectManager = $objectManager;
            parent::__construct(
                $entityFactory,
                $logger,
                $fetchStrategy,
                $eventManager,
                $storeManager,
                $connection,
                $resource
            );
            $this->_eventPrefix = $eventPrefix;
            $this->_eventObject = $eventObject;
            $this->_init($model, $resourceModel);
            $this->setMainTable($mainTable);
        }

        public function getAggregations()   {
            return $this->_aggregations;
        }

        public function setAggregations($aggregations)  {
            $this->_aggregations = $aggregations;
        }

        public function getAllIds($limit = null, $offset = null)    {
            return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
        }

        public function getSearchCriteria()     {
            return null;
        }

        public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null) {
            return $this;
        }

        public function getTotalCount()     {
            return $this->getSize();
        }

        public function setTotalCount($totalCount)  {
            return $this;
        }

        public function setItems(array $items = null)   {
            return $this;
        }

    }