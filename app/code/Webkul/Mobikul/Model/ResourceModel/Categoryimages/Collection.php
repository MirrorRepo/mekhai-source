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

    namespace Webkul\Mobikul\Model\ResourceModel\Categoryimages;
    use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

    class Collection extends AbstractCollection     {

        protected $_idFieldName = "id";

        public function __construct(
            \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
            \Psr\Log\LoggerInterface $loggerInterface,
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface,
            \Magento\Framework\Event\ManagerInterface $eventManager,
            \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
            \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
        ) {
            parent::__construct(
                $entityFactory,
                $loggerInterface,
                $fetchStrategyInterface,
                $eventManager,
                $connection,
                $resource
            );
        }

        protected function _construct()     {
            $this->_init("Webkul\Mobikul\Model\Categoryimages", "Webkul\Mobikul\Model\ResourceModel\Categoryimages");
            $this->_map["fields"]["id"] = "main_table.id";
        }

        public function addStoreFilter($store, $withAdmin = true)   {
            if (!$this->getFlag("store_filter_added")) {
                $this->performAddStoreFilter($store, $withAdmin);
            }
            return $this;
        }

        public function setCategoryimagesData($condition, $attributeData)   {
            return $this->getConnection()->update($this->getTable("mobikul_categoryimages"), $attributeData, $where = $condition);
        }

    }