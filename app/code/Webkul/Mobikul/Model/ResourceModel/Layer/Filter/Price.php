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

    namespace Webkul\Mobikul\Model\ResourceModel\Layer\Filter;

    class Price extends \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price {

        private $layer;
        private $session;
        private $storeManager;
        public $_customCollection;
        const MIN_POSSIBLE_PRICE = .01;
        protected $_eventManager = null;

        public function __construct(
            \Magento\Framework\Model\ResourceModel\Db\Context $context,
            \Magento\Framework\Event\ManagerInterface $eventManager,
            \Magento\Catalog\Model\Layer\Resolver $layerResolver,
            \Magento\Customer\Model\Session $session,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            $connectionName = null
        ) {
            $this->layer = $layerResolver->get();
            $this->session = $session;
            $this->storeManager = $storeManager;
            $this->_eventManager = $eventManager;
            parent::__construct($context, $eventManager, $layerResolver, $session, $storeManager, null);
        }

        protected function _getSelect()     {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $wholeData     = $objectManager->create("\Magento\Framework\App\Request\Http")->getPostValue();
            if(isset($wholeData["custom"]) && $wholeData["customCollection"] == 1){
                $collection = $this->_customCollection;
            }
            else{
                $collection = $this->layer->getProductCollection();
            }
            $collection->addPriceData(
                $this->session->getCustomerGroupId(),
                $this->storeManager->getStore()->getWebsiteId()
            );
            if ($collection->getCatalogPreparedSelect() !== null) {
                $select = clone $collection->getCatalogPreparedSelect();
            } else {
                $select = clone $collection->getSelect();
            }
            // reset columns, order and limitation conditions
            $select->reset(\Magento\Framework\DB\Select::COLUMNS);
            $select->reset(\Magento\Framework\DB\Select::ORDER);
            $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
            $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
            // remove join with main table
            $fromPart = $select->getPart(\Magento\Framework\DB\Select::FROM);
            if (!isset(
                    $fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS]
                ) || !isset(
                    $fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS]
                )
            ) {
                return $select;
            }
            // processing FROM part
            $priceIndexJoinPart = $fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS];
            $priceIndexJoinConditions = explode('AND', $priceIndexJoinPart['joinCondition']);
            $priceIndexJoinPart['joinType'] = \Magento\Framework\DB\Select::FROM;
            $priceIndexJoinPart['joinCondition'] = null;
            $fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS] = $priceIndexJoinPart;
            unset($fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS]);
            $select->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
            foreach ($fromPart as $key => $fromJoinItem) {
                $fromPart[$key]['joinCondition'] = $this->_replaceTableAlias($fromJoinItem['joinCondition']);
            }
            $select->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
            // processing WHERE part
            $wherePart = $select->getPart(\Magento\Framework\DB\Select::WHERE);
            foreach ($wherePart as $key => $wherePartItem) {
                $wherePart[$key] = $this->_replaceTableAlias($wherePartItem);
            }
            $select->setPart(\Magento\Framework\DB\Select::WHERE, $wherePart);
            $excludeJoinPart = \Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS . '.entity_id';
            foreach ($priceIndexJoinConditions as $condition) {
                if (strpos($condition, $excludeJoinPart) !== false) {
                    continue;
                }
                $select->where($this->_replaceTableAlias($condition));
            }
            $select->where($this->_getPriceExpression($select) . ' IS NOT NULL');
            return $select;
        }

    }