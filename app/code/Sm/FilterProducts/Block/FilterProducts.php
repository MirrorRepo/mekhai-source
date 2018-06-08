<?php
/*------------------------------------------------------------------------
# SM Filter Products - Version 1.0.0
# Copyright (c) 2016 YouTech Company. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: YouTech Company
# Websites: http://www.magentech.com
-------------------------------------------------------------------------*/

namespace Sm\FilterProducts\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\UrlFactory;

class FilterProducts extends \Magento\Catalog\Block\Product\AbstractProduct
{
	protected $_config = null;
    protected $_collection;
    protected $_resource;
    protected $_helper;
	protected $_storeManager;
    protected $_scopeConfig;
	protected $_storeId;
	protected $_storeCode;
	protected $_catalogProductVisibility;
	protected $_review;
	

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        \Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
		\Magento\Review\Model\Review $review,
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
		$this->_review = $review;
		$this->_config = $this->_getCfg($attr, $data);
        parent::__construct($context, $data);
    }
	
	public function _getCfg($attr = null , $data = null)
	{
		$defaults = [];
		$_cfg_xml = $this->_scopeConfig->getValue('filterproducts',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->_storeCode);
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
			$_cfgs = $this->_scopeConfig->getValue('filterproducts/'.$group.'',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->_storeCode);
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

	public function _getConfig($name = null, $value_def = null)
	{
		if (is_null($this->_config)) $this->_getCfg();
		if (!is_null($name)) {
			$value_def = isset($this->_config[$name]) ? $this->_config[$name] : $value_def;
			return $value_def;
		}
		return $this->_config;
	}

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
	
	protected function _toHtml()
    {
		if (!(int)$this->_getConfig('enable', 1)) return ;
        $template_file = $this->getTemplate();
        $template_file = (!empty($template_file)) ? $template_file : "Sm_FilterProducts::default.phtml";
        $this->setTemplate($template_file);
        return parent::_toHtml();
    }
	
	private function _getProducts(){
		$product_source = $this->_getConfig('product_source');
		//$product_source = 'best_sellers';
		switch($product_source){
			default:
			case 'lastest_products':
			    return $this->_newProducts();
			break;
			case 'best_sellers':
				return $this->_bestSellers();
			break;	
			case 'special_products':
				return $this->_specialProducts();
			break;
			case 'featured_products':
				return $this->_featuredProducts();
			break;	
			case 'other_products':
				return $this->_otherProducts();
			break;	
			case 'viewed_products':
				return $this->_viewedProducts();
			case 'countdown_products':
				return $this->_countDownProducts();
			break;	
		}
	}
	
	private function _countDownProducts() {
		$count = $this->_getConfig('product_limitation');                       
        $category_id = $this->_getConfig('select_category');
		!is_array($category_id) && $category_id = preg_split('/[\s|,|;]/', $category_id, -1, PREG_SPLIT_NO_EMPTY);
		$collection = clone $this->_collection;	
		$collection->clear()->getSelect()
			->reset(\Magento\Framework\DB\Select::WHERE)
			->reset(\Magento\Framework\DB\Select::ORDER)
			->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)
			->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)
			->reset(\Magento\Framework\DB\Select::GROUP);
		
		$todayDate = date('m/d/y');	
		$dateTo =  $this->_getConfig('date_to', '');	
		$todayStartOfDayDate = $this->_localeDate->date($todayDate)->setTime(0, 0, 0)->format('Y-m-d H:i:s');
		$todayEndOfDayDate = $this->_localeDate->date($dateTo)->setTime(23, 59, 59)->format('Y-m-d H:i:s');
		$collection->addMinimalPrice()
			->addFinalPrice()
			->addTaxPercents()
			->addAttributeToSelect('name')
			->addAttributeToSelect('image')
			->addAttributeToSelect('small_image')
			->addAttributeToSelect('thumbnail')
			->addAttributeToSelect('news_from_date')
			->addAttributeToSelect('news_to_date')
			->addAttributeToSelect('short_description')
			->addAttributeToSelect('special_from_date')
			->addAttributeToSelect('special_to_date')
			->addAttributeToFilter('special_price', ['neq' => ''])
			->addAttributeToFilter('special_from_date',
				['and' => [
					0 => ['date' => true, 'to' => $todayEndOfDayDate]
				]], 'left')
			->addAttributeToFilter('special_to_date',
				['and' => [
					0 => ['date' => true, 'from' => $todayStartOfDayDate]
				]], 'left')
			->addAttributeToFilter('is_saleable', 1, 'left')
			->setStoreId($this->_storeId);
		(!empty($category_id) && $category_id) ? $collection->addCategoriesFilter(['in' => $category_id]) : '';
		 $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection->getSelect()->distinct(true)->limit($count);
		return $collection;
	}
	
	private function _featuredProducts(){
		$count = $this->_getConfig('product_limitation');                       
        $category_id = $this->_getConfig('select_category');
		!is_array($category_id) && $category_id = preg_split('/[\s|,|;]/', $category_id, -1, PREG_SPLIT_NO_EMPTY);
		$collection = clone $this->_collection;
        $collection->clear()->getSelect()
			->reset(\Magento\Framework\DB\Select::WHERE)
			->reset(\Magento\Framework\DB\Select::ORDER)
			->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)
			->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)
			->reset(\Magento\Framework\DB\Select::GROUP);
		
		$collection->addMinimalPrice()
			->addFinalPrice()
			->addTaxPercents()
			->addAttributeToSelect('name')
			->addAttributeToSelect('image')
			->addAttributeToSelect('small_image')
			->addAttributeToSelect('thumbnail')
			->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
			->addUrlRewrite()
			->setStoreId($this->_storeId)
			->addAttributeToFilter('is_saleable', 1, 'left')
			->addAttributeToFilter('sm_featured', 1, 'left');
		(!empty($category_id) && $category_id) ? $collection->addCategoriesFilter(['in' => $category_id]) : '';
		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection->getSelect()
            ->order('rand()')
            ->limit($count);

        return $collection;	
	}
	
	private function _specialProducts() {
		$count = $this->_getConfig('product_limitation');                       
        $category_id = $this->_getConfig('select_category');
		!is_array($category_id) && $category_id = preg_split('/[\s|,|;]/', $category_id, -1, PREG_SPLIT_NO_EMPTY);
		$collection = clone $this->_collection;	
		$collection->clear()->getSelect()
			->reset(\Magento\Framework\DB\Select::WHERE)
			->reset(\Magento\Framework\DB\Select::ORDER)
			->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)
			->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)
			->reset(\Magento\Framework\DB\Select::GROUP);
		$now = date('Y-m-d');
		
		$collection->addMinimalPrice()
			->addFinalPrice()
			->addTaxPercents()
			->addAttributeToSelect('name')
			->addAttributeToSelect('image')
			->addAttributeToSelect('small_image')
			->addAttributeToSelect('thumbnail')
			->addAttributeToSelect('news_from_date')
			->addAttributeToSelect('news_to_date')
			->addAttributeToSelect('short_description')
			->addAttributeToSelect('special_from_date')
			->addAttributeToSelect('special_to_date')
			->addAttributeToFilter('special_price', ['neq' => ''])
			->addAttributeToFilter([
				[
					'attribute' => 'special_from_date',
					'lteq' => date('Y-m-d G:i:s', strtotime($now)),
					'date' => true,
				],
				[
					'attribute' => 'special_to_date',
					'gteq' => date('Y-m-d G:i:s', strtotime($now)),
					'date' => true,
				]
			])
			->addAttributeToFilter('is_saleable', 1, 'left')
			->setStoreId($this->_storeId);
		(!empty($category_id) && $category_id) ? $collection->addCategoriesFilter(['in' => $category_id]) : '';
		 $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection->getSelect()->distinct(true)->limit($count);
		return $collection;
	}
	
	private function _bestSellers(){
		$count = $this->_getConfig('product_limitation');                       
        $category_id = $this->_getConfig('select_category');
		!is_array($category_id) && $category_id = preg_split('/[\s|,|;]/', $category_id, -1, PREG_SPLIT_NO_EMPTY);
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
        $collection->getSelect()->join(['e' => $connection->getTableName('catalog_product_entity')],'');
		$collection->addMinimalPrice()
			->addFinalPrice()
			->addTaxPercents()
			->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
			->addUrlRewrite()
			->setStoreId($this->_storeId)
			->addAttributeToFilter('is_saleable', 1, 'left');
			
		(!empty($category_id) && $category_id) ? $collection->addCategoriesFilter(['in' => $category_id]) : '';
		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection->getSelect()->distinct(true);
		$collection->getSelect()
            ->joinLeft(['soi' => $connection->getTableName('sales_order_item')], 'soi.product_id = e.entity_id', ['SUM(soi.qty_ordered) AS ordered_qty'])
            ->join(['order' => $connection->getTableName('sales_order')], "order.entity_id = soi.order_id",['order.state'])
            ->where("order.state <> 'canceled' and soi.parent_item_id IS NULL AND soi.product_id IS NOT NULL")
            ->group('soi.product_id')
            ->order('ordered_qty DESC')
            ->limit($count);
			//echo $collection->getSelect()->__toString(); die;
		return $collection;	
	}
	
	private function _viewedProducts(){
		$count = $this->_getConfig('product_limitation');                       
        $category_id = $this->_getConfig('select_category');
		!is_array($category_id) && $category_id = preg_split('/[\s|,|;]/', $category_id, -1, PREG_SPLIT_NO_EMPTY);
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
        $collection->getSelect()->join(['e' => $connection->getTableName('catalog_product_entity')],'');
		$collection->addMinimalPrice()
			->addFinalPrice()
			->addTaxPercents()
			->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
			->addUrlRewrite()
			->setStoreId($this->_storeId)
			->addAttributeToFilter('is_saleable', 1, 'left');
			
		(!empty($category_id) && $category_id) ? $collection->addCategoriesFilter(['in' => $category_id]) : '';
		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection->getSelect()->distinct(true);
		$collection->getSelect()
			->joinLeft(['mv' => $connection->getTableName('report_event')],'mv.object_id = e.entity_id', ['*', 'num_view_counts' => 'COUNT(`event_id`)'])
			->where('mv.event_type_id = 1 AND mv.store_id='.$this->_storeId.'' )
			->group('entity_id');
		$collection->getSelect()->distinct(true);
		$collection->getSelect()->order('num_view_counts DESC');
		$collection->clear();	
		$collection->getSelect()->limit($count);		
		$this->_review->appendSummary($collection);
		return $collection;
	}
	
	private function _newProducts(){
		$count = $this->_getConfig('product_limitation');                       
        $category_id = $this->_getConfig('select_category');
		!is_array($category_id) && $category_id = preg_split('/[\s|,|;]/', $category_id, -1, PREG_SPLIT_NO_EMPTY);
        $collection = clone $this->_collection;
        $collection->clear()->getSelect()
			->reset(\Magento\Framework\DB\Select::WHERE)
			->reset(\Magento\Framework\DB\Select::ORDER)
			->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)
			->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)
			->reset(\Magento\Framework\DB\Select::GROUP);
        
		$collection->addMinimalPrice()
			->addFinalPrice()
			->addTaxPercents()
			->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
			->addUrlRewrite()
			->setStoreId($this->_storeId)
			->addAttributeToFilter('is_saleable', 1, 'left')
			->addAttributeToSort('created_at','DESC');
			
		(!empty($category_id) && $category_id) ? $collection->addCategoriesFilter(['in' => $category_id]) : '';
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection->getSelect()->distinct(true)->limit($count);
		$this->_review->appendSummary($collection);
        return $collection;
	}
	
	private function _otherProducts(){
		$count = $this->_getConfig('product_limitation');                       
        $category_id = $this->_getConfig('select_category');
		$product_order_by = $this->_getConfig('product_order_by');
		$product_order_dir = $this->_getConfig('product_order_dir');
		!is_array($category_id) && $category_id = preg_split('/[\s|,|;]/', $category_id, -1, PREG_SPLIT_NO_EMPTY);
        $collection = clone $this->_collection;
        $collection->clear()->getSelect()
			->reset(\Magento\Framework\DB\Select::WHERE)
			->reset(\Magento\Framework\DB\Select::ORDER)
			->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)
			->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)
			->reset(\Magento\Framework\DB\Select::GROUP);
		
		$collection->addMinimalPrice()
			->addFinalPrice()
			->addTaxPercents()
			->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
			->addUrlRewrite()
			->setStoreId($this->_storeId)
			->addAttributeToFilter('is_saleable', 1, 'left');
			
		(!empty($category_id) && $category_id) ? $collection->addCategoriesFilter(['in' => $category_id]) : '';
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		
		switch ($product_order_by) {
			case 'entity_id':
			case 'name':
			case 'created_at':
				$collection->setOrder($product_order_by, $product_order_dir);
				break;
			case 'price':
				$collection->getSelect()->order('final_price ' . $product_order_dir . '');
				break;
			case 'random':
				$collection->getSelect()->order(new \Zend_Db_Expr('RAND()'));
				break;
		}
		
		$collection->getSelect()->distinct(true)->limit($count);
		$this->_review->appendSummary($collection);
        return $collection;
	}
	
	public function getLoadedProductCollection() {
        return $this->_getProducts();
    }
	
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
