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

namespace Webkul\Mpcashondelivery\Block\Adminhtml\Seller\Edit\Tab\Report;

use \Webkul\Marketplace\Model\SaleslistFactory;
use \Magento\Sales\Model\OrderFactory;
use \Webkul\Marketplace\Helper\Data;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Webkul\Marketplace\Model\SaleslistFactory
     */
    protected $_salesList;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_order;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_marketplaceHelper;

     /**
     * @param \Magento\Backend\Block\Template\Context    $context
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     * @param \Magento\Backend\Helper\Data               $backendHelper
     * @param \Webkul\Marketplace\Model\SaleslistFactory $salesList
     * @param \Magento\Sales\Model\OrderFactory          $order
     * @param \Webkul\Marketplace\Helper\Data            $marketplaceHelper
     * @param array                                      $data
     */
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Helper\Data $backendHelper,
        SaleslistFactory $salesList,
        OrderFactory $order,
        Data $marketplaceHelper,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_salesList = $salesList;
        $this->_order = $order;
        $this->_marketplaceHelper = $marketplaceHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('reportGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setEmptyText(__('No Orders Found!'));
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'mpcashondelivery/adminorder/grid',
            ['_current' => true]
        );
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $salesOrderTable = $this->_salesList
        ->create()
        ->getCollection()
        ->getTable('sales_order');
        $customerid=$this->getRequest()->getParam('id');
        $mpcodOrders = [];
        $sellerColl = $this->_salesList->create()
                    ->getCollection()
                    ->addFieldToFilter('seller_id', ['eq'=>$customerid])
                    ->addFieldToFilter('magerealorder_id', ['neq'=>0]);
        foreach ($sellerColl as $value) {
            $order = $this->getOrderById($value->getOrderId());
            if ($order->getPayment()) {
                $paymentCode = $order->getPayment()->getMethod();
                if ($paymentCode == 'mpcashondelivery') {
                    array_push($mpcodOrders, $value->getOrderId());
                }
            }
        }
        $collection = $this->_salesList->create()
                    ->getCollection()
                    ->addFieldToFilter('order_id', ['in'=>$mpcodOrders])
                    ->addFieldToFilter('seller_id', ['eq'=>$customerid])
                    ->addFieldToFilter('magerealorder_id', ['neq'=>0]);
        $collection->getSelect()
                    ->join(
                        ["so" => $salesOrderTable],
                        'main_table.order_id = so.entity_id',
                        ["status" => "status"]
                    );
        $collection->addFilterToMap("order_id", "main_table.order_id");
        $this->setCollection($collection);
        parent::_prepareCollection();
        foreach ($collection as $item) {
            $totalCommission = $item->getTotalCommission();
            $totalTax = $item->getTotalTax();
            $taxManageConfig = $this->_marketplaceHelper
                            ->getConfigTaxManage();
            if ($taxManageConfig) {
                $item->setTotalAdminAmount($totalCommission+$totalTax);
            } else {
                $item->setTotaladminamount($totalCommission);
            }
            $totalAdminAmount = number_format($item->getTotaladminamount(), 4);
            $item->setTotaladminamount($totalAdminAmount);

            $item->setMagerealorderid(
                '<a class="wk_sellerorderstatus" wk_cpprostatus="'.
                $item->getCollectCodStatus().
                '" href="'.$this->getUrl(
                    'sales/order/view/',
                    ['order_id'=>$item->getOrderId()]
                ).
                '" title="'.__('View Order').'">'.
                $item->getMagerealorderId().'</a>'
            );
            $adminStatus = $item->getAdminPayStatus();
        }
        $collection->setOrder('order_id', 'DESC');
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'magerealorderid',
            [
                'header' => __('Order#'),
                'filter' => false,
                'sortable' => false,
                'width' => '100px',
                'renderer' => 'Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Renderer\Mageorderid'
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Purchased On'),
                'sortable' => true,
                'index' => 'created_at',
                'type'=>'datetime'
            ]
        );
        $this->addColumn(
            'magepro_name',
            [
                'header' => __('Product Name'),
                'sortable' => true,
                'index' => 'magepro_name'
            ]
        );
        $this->addColumn(
            'total_amount',
            [
                'header' => __('Total Amount'),
                'sortable' => true,
                'index' => 'total_amount',
                'column_css_class' => 'wktotalamount',
                'renderer' => 'Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Renderer\Amount'
            ]
        );
        $this->addColumn(
            'total_tax',
            [
                'header' => __('Total Tax'),
                'sortable' => true,
                'index' => 'total_tax',
                'renderer' => 'Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Renderer\Amount'
            ]
        );
        $this->addColumn(
            'actual_seller_amount',
            [
                'header' => __('Actual Partner Cost'),
                'sortable' => true,
                'index' => 'actual_seller_amount',
                'column_css_class' => 'wkactualsellercost',
                'renderer' => 'Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Renderer\Amount'
            ]
        );
        $this->addColumn(
            'total_commission',
            [
                'header' => __('Total Commission'),
                'sortable' => true,
                'index' => 'total_commission',
                'renderer' => 'Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Renderer\Amount'
            ]
        );
        $this->addColumn(
            'totaladminamount',
            [
                'header' => __('Total Admin Amount'),
                'sortable' => true,
                'index' => 'totaladminamount',
                'column_css_class' => 'wktotalcommision',
                'renderer' => 'Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Renderer\Amount'
            ]
        );
        $this->addColumn(
            'cod_charges',
            [
                'header' => __('COD Charges'),
                'sortable' => true,
                'index' => 'cod_charges',
                'renderer' => 'Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Renderer\Amount'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Order Status'),
                'sortable' => true,
                'index' => 'status'
            ]
        );
        $this->addColumn(
            'collect_cod_status',
            [
                'header' => __('Collect COD Status'),
                'sortable' => true,
                'index' => 'collect_cod_status',
                'type'      => 'options',
                'options'   => $this->getCollectedStatuses(),
                'column_css_class' => 'wk_orderstatus'
            ]
        );
        $this->addColumn(
            'admin_pay_status',
            [
                'header' => __('Commission Paid'),
                'sortable' => true,
                'index' => 'admin_pay_status',
                'type'      => 'options',
                'options'   => $this->getAdminPayStatuses(),
                'column_css_class' => 'wk_paidstatus'
            ]
        );
        $this->addColumn(
            'notifyseller',
            [
                'header' => __('Notify Seller'),
                'filter' => false,
                'sortable' => false,
                'width' => '100px',
                'renderer' => 'Webkul\Mpcashondelivery\Block\Adminhtml\Pricerules\Renderer\Notifyseller'
            ]
        );
        return parent::_prepareColumns();
    }
    public function getAdminPayStatuses()
    {
        return ['0'=>'Pending','1'=>'Paid','2'=>'Canceled'];
    }
    public function getCollectedStatuses()
    {
        return [
            '0'=>'Pending',
            '1'=>'Collected',
            '2'=>'Canceled',
            '3'=>'Refunded'
        ];
    }
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('order_id');
        $this->getMassactionBlock()->setFormFieldName('sellerorderids');
        $this->getMassactionBlock()->setUseSelectAll(false);
        $this->getMassactionBlock()->setUseUnSelectAll(false);
        $this->getMassactionBlock()->addItem(
            'massnotify',
            [
                'label'    => __('Notify'),
                'url'      => $this->getUrl('*/*/massnotify'),
                'confirm' => __('Are you want to make this payment?')
            ]
        );
        return $this;
    }
    protected function getAdditionalJavascript()
    {
        return 'window.reportGrid_massactionJsObject = 
        reportGrid_massactionJsObject;';
    }

    public function getOrderById($orderId)
    {
        return $this->_order->create()
                    ->load($orderId);
    }
}
