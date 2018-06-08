<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpcashondelivery
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;

class Codprice extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var \Webkul\Mpcashondelivery\Model\PricerulesFactory
     */
    protected $_priceruleFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context          $context
     * @param \Magento\Backend\Helper\Data                     $backendHelper
     * @param \Webkul\Mpcashondelivery\Model\PricerulesFactory $priceruleFactory
     * @param \Magento\Framework\Registry                      $coreRegistry
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Webkul\Mpcashondelivery\Model\PricerulesFactory $priceruleFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_priceruleFactory = $priceruleFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('codprice_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->_priceruleFactory->create()->getCollection()
                    ->addFieldToFilter('seller_id', ['eq' => 0]);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'type' => 'range',
            ]
        );
        $this->addColumn(
            'seller_id',
            [
                'header' => __('Seller Id'),
                'sortable' => true,
                'index' => 'seller_id',
                'type' => 'range',
            ]
        );
        $this->addColumn(
            'dest_country_id',
            [
                'header' => __('Destination Country'),
                'sortable' => true,
                'index' => 'dest_country_id',
                'type' => 'text',
            ]
        );
        $this->addColumn(
            'dest_region_id',
            [
                'header' => __('Desination region'),
                'sortable' => true,
                'index' => 'dest_region_id',
                'type' => 'text',
            ]
        );
        $this->addColumn(
            'is_range',
            [
                'header' => __('Is Range'),
                'sortable' => true,
                'index' => 'is_range',
                'type' => 'options',
                'options' => $this->getIsrangeType(),
            ]
        );
        $this->addColumn(
            'dest_zip_from',
            [
                'header' => __('Destination Zip from'),
                'sortable' => true,
                'index' => 'dest_zip_from',
                'type' => 'range',
            ]
        );
        $this->addColumn(
            'dest_zip_to',
            [
                'header' => __('Destination Zip To'),
                'sortable' => true,
                'index' => 'dest_zip_to',
                'type' => 'range',
            ]
        );
        $this->addColumn(
            'zipcode',
            [
                'header' => __('Zip Code'),
                'sortable' => true,
                'index' => 'zipcode',
            ]
        );
        $this->addColumn(
            'price_type',
            [
                'header' => __('Price Type'),
                'sortable' => true,
                'index' => 'price_type',
                'type' => 'options',
                'options' => $this->getPriceTypeOption(),
            ]
        );
        $this->addColumn(
            'fixed_price',
            [
                'header' => __('Fixed Price'),
                'sortable' => true,
                'index' => 'fixed_price',
                'currency' => 'currency_code',
                'type' => 'currency',
            ]
        );
        $this->addColumn(
            'percentage_price',
            [
                'header' => __('Percentage Price'),
                'sortable' => true,
                'index' => 'percentage_price',
                'currency' => 'currency_code',
                'type' => 'currency',

            ]
        );
        $this->addColumn(
            'weight_from',
            [
                'header' => __('Weight From'),
                'sortable' => true,
                'index' => 'weight_from',
            ]
        );
        $this->addColumn(
            'weight_to',
            [
                'header' => __('Weight To'),
                'sortable' => true,
                'index' => 'weight_to',
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'filter' => false,
                'sortable' => false,
                'width' => '100px',
                'renderer' => 'Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Renderer\Action',
            ]
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->getMassactionBlock()->setTemplate(
            'Webkul_Mpcashondelivery::widget/grid/massaction_extended.phtml'
        );
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('mpcodpricerule');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete', ['_current' => true]),
                'confirm' => __('Are you sure you want to delete ?'),
            ]
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getRowUrl($row)
    {
        return 'javascript:void(0)';
    }
    public function getcurrency()
    {
        return $currencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
    }
    public function getIsrangeType()
    {
        return [1 => __('Specific'), 0 => __('Range')];
    }
    public function getPriceTypeOption()
    {
        return [1 => __('Percentage'), 0 => __('Fixed')];
    }
}
