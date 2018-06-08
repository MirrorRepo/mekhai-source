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

    namespace Webkul\Mobikul\Model\ResourceModel\Fulltext;
    use Magento\CatalogSearch\Model\Search\RequestGenerator;
    use Magento\Framework\DB\Select;
    use Magento\Framework\Exception\StateException;
    use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
    use Magento\Framework\Search\Response\QueryResponse;
    use Magento\Framework\Search\Request\EmptyRequestDataException;
    use Magento\Framework\Search\Request\NonExistingRequestNameException;
    use Magento\Framework\Api\Search\SearchResultFactory;
    use Magento\Framework\Exception\LocalizedException;
    use Magento\Framework\App\ObjectManager;

    class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection  {

        protected $queryResponse;
        protected $queryFactory = null;
        private $requestBuilder;
        private $searchEngine;
        private $queryText;
        private $order = null;
        private $searchRequestName;
        private $temporaryStorageFactory;
        private $search;
        private $searchCriteriaBuilder;
        private $searchResult;
        private $searchResultFactory;
        private $filterBuilder;

        public function __construct(
            \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
            \Psr\Log\LoggerInterface $logger,
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
            \Magento\Framework\Event\ManagerInterface $eventManager,
            \Magento\Eav\Model\Config $eavConfig,
            \Magento\Framework\App\ResourceConnection $resource,
            \Magento\Eav\Model\EntityFactory $eavEntityFactory,
            \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
            \Magento\Framework\Validator\UniversalFactory $universalFactory,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\Module\Manager $moduleManager,
            \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
            \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Framework\Stdlib\DateTime $dateTime,
            \Magento\Customer\Api\GroupManagementInterface $groupManagement,
            \Magento\Search\Model\QueryFactory $catalogSearchData,
            \Magento\Framework\Search\Request\Builder $requestBuilder,
            \Magento\Search\Model\SearchEngine $searchEngine,
            \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
            \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
            $searchRequestName = "catalog_view_container",
            SearchResultFactory $searchResultFactory = null
        ) {
            $this->queryFactory = $catalogSearchData;
            if ($searchResultFactory === null) {
                $this->searchResultFactory = \Magento\Framework\App\ObjectManager::getInstance()->get("Magento\Framework\Api\Search\SearchResultFactory");
            }
            parent::__construct(
                $entityFactory,
                $logger,
                $fetchStrategy,
                $eventManager,
                $eavConfig,
                $resource,
                $eavEntityFactory,
                $resourceHelper,
                $universalFactory,
                $storeManager,
                $moduleManager,
                $catalogProductFlatState,
                $scopeConfig,
                $productOptionFactory,
                $catalogUrl,
                $localeDate,
                $customerSession,
                $dateTime,
                $groupManagement,
                $catalogSearchData,
                $requestBuilder,
                $searchEngine,
                $temporaryStorageFactory,
                null,
                "catalog_view_container",
                null
            );
            $this->requestBuilder = $requestBuilder;
            $this->searchEngine = $searchEngine;
            $this->temporaryStorageFactory = $temporaryStorageFactory;
            $this->searchRequestName = $searchRequestName;
        }

        private function getSearch()    {
            if ($this->search === null) {
                $this->search = ObjectManager::getInstance()->get("\Magento\Search\Api\SearchInterface");
            }
            return $this->search;
        }

        private function getSearchCriteriaBuilder() {
            if ($this->searchCriteriaBuilder === null) {
                $this->searchCriteriaBuilder = ObjectManager::getInstance()
                    ->get("\Magento\Framework\Api\Search\SearchCriteriaBuilder");
            }
            return $this->searchCriteriaBuilder;
        }

        private function getFilterBuilder()     {
            if ($this->filterBuilder === null) {
                $this->filterBuilder = ObjectManager::getInstance()->get("\Magento\Framework\Api\FilterBuilder");
            }
            return $this->filterBuilder;
        }

        public function addFieldToFilter($field, $condition = null)     {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $request       = $objectManager->create("\Magento\Framework\App\Request\Http");
            if ($this->searchResult !== null && $request->getHeader("authKey") == "") {
                throw new \RuntimeException("Illegal state");
            }
            $this->getSearchCriteriaBuilder();
            $this->getFilterBuilder();
            if (!is_array($condition) || !in_array(key($condition), ["from", "to"])) {
                $this->filterBuilder->setField($field);
                $this->filterBuilder->setValue($condition);
                $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
            } else {
                if (!empty($condition["from"])) {
                    $this->filterBuilder->setField("{$field}.from");
                    $this->filterBuilder->setValue($condition["from"]);
                    $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
                }
                if (!empty($condition["to"])) {
                    $this->filterBuilder->setField("{$field}.to");
                    $this->filterBuilder->setValue($condition["to"]);
                    $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
                }
            }
            return $this;
        }

    }