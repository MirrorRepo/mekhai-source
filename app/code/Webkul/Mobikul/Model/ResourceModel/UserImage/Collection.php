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

    namespace Webkul\Mobikul\Model\ResourceModel\UserImage;
    use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

    class Collection extends AbstractCollection     {

        protected $_idFieldName = "id";
        protected $_storeManager;

        public function __construct(
            \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
            \Psr\Log\LoggerInterface $logger,
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
            \Magento\Framework\Event\ManagerInterface $eventManager,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
        ) {
            parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
            $this->_storeManager = $storeManager;
        }

        protected function _construct()     {
            $this->_init("Webkul\Mobikul\Model\UserImage", "Webkul\Mobikul\Model\ResourceModel\UserImage");
            $this->_map["fields"]["id"] = "main_table.id";
        }

        public function addStoreFilter($store, $withAdmin = true)   {
            if (!$this->getFlag("store_filter_added")) {
                $this->performAddStoreFilter($store, $withAdmin);
            }
            return $this;
        }

    }