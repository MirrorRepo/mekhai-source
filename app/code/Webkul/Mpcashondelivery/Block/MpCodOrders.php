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

namespace Webkul\Mpcashondelivery\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;
use Webkul\Marketplace\Helper\Orders;
use Magento\Framework\ObjectManagerInterface;

class MpCodOrders extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory
     */
    protected $_orderCollectionFactory;
    /**
     * @var Session
     */
    protected $_customerSession;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_marketplaceHelper;
     /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_order;
    /**
     * @var \Webkul\Mpcashondelivery\Helper\Data
     */
    protected $_mpcodHelper;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;
    /**
     * @var \Webkul\Mpcashondelivery\Helper\Codorders
     */
    protected $_mpcodOrderHelper;

    protected $_mpcodOrderCollection;

    /**
    * @param Context                    $context
    * @param ObjectManagerInterface     $objectManager
    * @param CollectionFactory          $orderCollectionFactory
    * @param Session                    $customerSession
    * @param Orders                     $marketplaceHelper
    * @param OrderFactory               $orders
    * @param array                      $data
    */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        CollectionFactory $orderCollectionFactory,
        Session $customerSession,
        Orders $marketplaceHelper,
        OrderFactory $order,
        \Webkul\Mpcashondelivery\Helper\Data $mpcodHelper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\Mpcashondelivery\Helper\Codorders $mpcodOrderHelper,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_order = $order;
        $this->_mpcodHelper = $mpcodHelper;
        $this->_mpHelper = $mpHelper;
        $this->_mpcodOrderHelper = $mpcodOrderHelper;
        parent::__construct($context, $data);
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCodOrderCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'mpcashondelivery.order.list.pager'
            )->setCollection(
                $this->getCodOrderCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCodOrderCollection()->load();
        }
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    public function getCustomerId()
    {
        return $this->_marketplaceHelper->getCustomerId();
    }
    public function getCodOrderCollection()
    {
        if (!$this->_mpcodOrderCollection) {
            if (!($customerId = $this->getCustomerId())) {
                return false;
            }
            $ids = [];
            $orderids = [];
            $paramData = $this->getRequest()->getParams();
            $filterOrderId = '';
            $filterOrderStatus = '';
            $filterDateTo = '';
            $filterDateFrm = '';
            $from = null;
            $to = null;
            list(
                $filterOrderId,
                $filterOrderStatus,
                $filterDateTo,
                $filterDateFrm
            ) = $this->getFilteredData($paramData);
            $collectionOrders = $this->_orderCollectionFactory->create()
                ->addFieldToFilter(
                    'seller_id',
                    ['eq' => $customerId]
                )
                ->addFieldToSelect('order_id')
                ->distinct(true);
            foreach ($collectionOrders as $collectionOrder) {
                $orderId = $collectionOrder->getOrderId();
                $order = $this->getOrder($orderId);
                if ($order->getPayment()) {
                    $paymentCode = $order->getPayment()->getMethod();
                    if ($paymentCode=='mpcashondelivery') {
                        if ($filterOrderStatus) {
                            $tracking=$this->_marketplaceHelper
                                    ->getOrderinfo($orderId);
                            if ($tracking) {
                                if ($tracking->getIsCanceled()) {
                                    if ($filterOrderStatus=='canceled') {
                                        array_push($orderids, $orderId);
                                    }
                                } else {
                                    $tracking = $this->getOrderById($orderId);
                                    $tracStatus = $tracking->getStatus();
                                    if ($tracStatus==$filterOrderStatus) {
                                        array_push($orderids, $orderId);
                                    }
                                }
                            }
                        } else {
                            array_push($orderids, $orderId);
                        }
                    }
                }
            }
            foreach ($orderids as $orderid) {
                $collectionIds = $this->_orderCollectionFactory->create()
                                ->addFieldToFilter(
                                    'order_id',
                                    ['eq' => $orderid]
                                )
                                ->addFieldToFilter(
                                    'seller_id',
                                    ['eq' => $customerId]
                                )
                                ->setOrder('entity_id', 'DESC')
                                ->setPageSize(1);
                foreach ($collectionIds as $collectionId) {
                    $autoid = $collectionId->getId();
                    array_push($ids, $autoid);
                }
            }
            $collection = $this->_orderCollectionFactory->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter(
                            'entity_id',
                            ['in' => $ids]
                        );
            if ($filterDateTo) {
                $todate = date_create($filterDateTo);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }
            if ($filterDateFrm) {
                $fromdate = date_create($filterDateFrm);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }
            if ($filterOrderId) {
                $collection->addFieldToFilter(
                    'magerealorder_id',
                    ['eq' => $filterOrderId]
                );
            }
            $collection->addFieldToFilter(
                'created_at',
                ['datetime' => true, 'from' => $from, 'to' =>  $to]
            );
            $collection->setOrder(
                'created_at',
                'desc'
            );
            $this->_mpcodOrderCollection = $collection;
        }
        return $this->_mpcodOrderCollection;
    }
    public function getOrder($orderId)
    {
        $order = $this->_order->create()->load($orderId);
        return $order;
    }
    public function getOrderStatusData()
    {
        $model = $this->_objectManager
                ->create('Magento\Sales\Model\Order\Status')
                ->getResourceCollection()->getData();
        return $model;
    }
    public function getCurrentUrl()
    {
        // Give the current url of recently viewed page
        return $this->_urlBuilder->getCurrentUrl();
    }
    public function getMpCodHelper()
    {
        return $this->_mpcodHelper;
    }
    public function getMpHelper()
    {
        return $this->_mpHelper;
    }
    public function getMpCodOrderHelper()
    {
        return $this->_mpcodOrderHelper;
    }

    public function getFilteredData($paramData)
    {
        $filterOrderId = '';
        $filterOrderStatus = '';
        $filterDateTo = '';
        $filterDateFrm = '';
        if (isset($paramData['s'])) {
                $filterOrderId = $paramData['s'] != ""?$paramData['s']:"";
        }
        if (isset($paramData['orderstatus'])) {
            $getStatus = $paramData['orderstatus'];
            $filterOrderStatus = $getStatus != ""?$getStatus:"";
        }
        if (isset($paramData['from_date'])) {
            $filterDateFrm = $paramData['from_date'] != ""?$paramData['from_date']:"";
        }
        if (isset($paramData['to_date'])) {
            $filterDateTo = $paramData['to_date'] != ""?$paramData['to_date']:"";
        }
        return [
            $filterOrderId,
            $filterOrderStatus,
            $filterDateTo,
            $filterDateFrm
        ];
    }

    public function getOrderById($orderId)
    {
        return $this->_order->create()
            ->load($orderId);
    }
}
