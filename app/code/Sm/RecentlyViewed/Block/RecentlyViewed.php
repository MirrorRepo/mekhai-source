<?php
/*------------------------------------------------------------------------
# SM Recently Viewed - Version 1.0.0
# Copyright (c) 2017 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\RecentlyViewed\Block;

/**
 * Class RecentlyViewed
 * @package Sm\RecentlyViewed\Block
 */
class RecentlyViewed extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var array|null|void
     */
    protected $_config = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_collection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
	protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var int
     */
	protected $_storeId;

    /**
     * @var string
     */
	protected $_storeCode;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
	protected $_catalogProductVisibility;

    /**
     * @var \Magento\Review\Model\Review
     */
	protected $_productImageHelper;

    /**
     * RecentlyViewed constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Review\Model\Review $review
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param array $data
     * @param null $attr
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        \Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
		\Magento\Catalog\Helper\Image $productImageHelper,
		\Magento\Catalog\Block\Product\Context $context,
        array $data = [],
		$attr = null
    ) {
        $this->_collection = $collection;
        $this->_resource = $resource;
		$this->_storeManager = $context->getStoreManager();
        $this->_scopeConfig = $context->getScopeConfig();
		$this->_catalogProductVisibility = $catalogProductVisibility;
		$this->_storeId=(int)$this->_storeManager->getStore()->getId();
		$this->_storeCode=$this->_storeManager->getStore()->getCode();
		$this->_productImageHelper = $productImageHelper;
		$this->_config = $this->_getCfg($attr, $data);
        parent::__construct($context, $data);
    }

    /**
     * @param null $attr
     * @param null $data
     * @return array|null|void
     */
	public function _getCfg($attr = null , $data = null)
	{
		$defaults = [];
		$_cfg_xml = $this->_scopeConfig->getValue('recentlyviewed',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->_storeCode);
		if (empty($_cfg_xml)) return;
		$groups = [];
		foreach ($_cfg_xml as $def_key => $def_cfg) {
			$groups[] = $def_key;
			foreach ($def_cfg as $_def_key => $cfg) {
				$defaults[$_def_key] = $cfg;
			}
		}
		
		if (empty($groups)) return;
		$cfgs = [];
		foreach ($groups as $group) {
			$_cfgs = $this->_scopeConfig->getValue('recentlyviewed/'.$group.'',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->_storeCode);
			foreach ($_cfgs as $_key => $_cfg) {
				$cfgs[$_key] = $_cfg;
			}
		}

		if (empty($defaults)) return;
		$configs = [];
		foreach ($defaults as $key => $def) {
			if (isset($defaults[$key])) {
				$configs[$key] = $cfgs[$key];
			} else {
				unset($cfgs[$key]);
			}
		}
		$cf = ($attr != null) ? array_merge($configs, $attr) : $configs;
		$this->_config = ($data != null) ? array_merge($cf, $data) : $cf;
		return $this->_config;
	}

    /**
     * @param null $name
     * @param null $value_def
     * @return array|mixed|null|void
     */
	public function _getConfig($name = null, $value_def = null)
	{
		if (is_null($this->_config)) $this->_getCfg();
		if (!is_null($name)) {
			$value_def = isset($this->_config[$name]) ? $this->_config[$name] : $value_def;
			return $value_def;
		}
		return $this->_config;
	}

    /**
     * @param $name
     * @param null $value
     * @return bool|void
     */
	public function _setConfig($name, $value = null)
	{

		if (is_null($this->_config)) $this->_getCfg();
		if (is_array($name)) {
			$this->_config = array_merge($this->_config, $name);

			return;
		}
		if (!empty($name) && isset($this->_config[$name])) {
			$this->_config[$name] = $value;
		}
		return true;
	}

    /**
     * @return string|void
     */
	protected function _toHtml()
    {
		if (!(int)$this->_getConfig('is_enabled', 1)) return;
        $template_file = $this->getTemplate();
        $template_file = (!empty($template_file)) ? $template_file : "Sm_RecentlyViewed::default.phtml";
        $this->setTemplate($template_file);
        return parent::_toHtml();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
	private function _viewedProducts(){
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
		$customerSession = $objectManager->get('\Magento\Customer\Model\Session');
		$customerVisitor = $objectManager->get('\Magento\Customer\Model\Visitor');
		$subject_id = 1;
		if ($customerSession->isLoggedIn()) {
            $subject_id = $customerSession->getCustomerId();
        }  else {
            $subject_id = $customerVisitor->getId();
        }
		$count = $this->_getConfig('limitation');
		$collection = clone $this->_collection;
        $collection->clear()->getSelect()
			->reset(\Magento\Framework\DB\Select::WHERE)
			->reset(\Magento\Framework\DB\Select::ORDER)
			->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)
			->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)
			->reset(\Magento\Framework\DB\Select::GROUP)
			->reset(\Magento\Framework\DB\Select::COLUMNS)
			->reset('from');
		$connection  = $this->_resource->getConnection();
        $collection->getSelect()->join(['e' => $connection->getTableName($this->_resource->getTableName('catalog_product_entity'))],'');
		$collection->addMinimalPrice()
			->addFinalPrice()
			->addTaxPercents()
			->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
			->addUrlRewrite()
			->setStoreId($this->_storeId)
			->addAttributeToFilter('is_saleable', 1, 'left');
		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection->getSelect()
			->joinLeft(['mv' => $connection->getTableName($this->_resource->getTableName('report_event'))],'mv.object_id = e.entity_id', ['*','max_loged' => 'MAX(logged_at)'  , 'num_view_counts' => 'COUNT(`event_id`)'])
			->where('mv.event_type_id = 1 AND mv.subject_id='. $subject_id.' AND mv.store_id='.$this->_storeId.'' )
			->group('entity_id');
		$collection->getSelect()->distinct(true);
		$collection->getSelect()->order(' max_loged DESC ');
		$collection->clear();	
		$collection->getSelect()->limit($count);	
		return $collection;
	}

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
	public function getLoadedProductCollection() {
		 return $this->_viewedProducts() ;
    }

    /**
     * @return mixed|string
     */
	public function _tagId()
	{
		$tag_id = $this->getNameInLayout();
		$tag_id = strpos($tag_id, '.') !== false ? str_replace('.', '_', $tag_id) : $tag_id;
		return $tag_id;
	}

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
	public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {	
		$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $objectManager->get('\Magento\Framework\Url\Helper\Data')->getEncodedUrl($url),
            ]
        ];
    }
}