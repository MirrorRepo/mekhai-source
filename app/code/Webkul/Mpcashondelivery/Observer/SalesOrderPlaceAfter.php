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
namespace Webkul\Mpcashondelivery\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Webkul\Mpcashondelivery\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \Magento\Checkout\Model\Session           $checkoutSession
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Webkul\Mpcashondelivery\Helper\Data      $dataHelper
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Mpcashondelivery\Helper\Data $dataHelper
    ) {
        $this->_objectManager = $objectManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * order place after handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $lastOrderId = $observer->getOrder()->getId();
        $order = $observer->getOrder();
        if ($order->getPayment()) {
            $order->setState($this->_dataHelper->getCodOrderStatus())
                ->setStatus($this->_dataHelper->getCodOrderStatus())->save();
            $paymentCode = $order->getPayment()->getMethod();
            if ($paymentCode == 'mpcashondelivery') {
                $sellerCodInfo = $this->_checkoutSession->getSellerCodInfo();
                if (is_array($sellerCodInfo)) {
                    foreach ($sellerCodInfo as $coddata) {
                        $sellerId = $coddata['seller_id'];
                        $sellerProductArray = [];
                        $codPrice = 0;
                        foreach ($coddata['codinfo'] as $key => $value) {
                            $salesListCollection = $this->_objectManager->create(
                                'Webkul\Marketplace\Model\Saleslist'
                            )->getCollection()
                                ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                                ->addFieldToFilter('order_id', ['eq' => $lastOrderId])
                                ->addFieldToFilter('mageproduct_id', ['eq' => $key]);
                            foreach ($salesListCollection as $salesList) {
                                $rowId = $salesList->getEntityId();
                                $data = ['cod_charges' => $value];
                                $salesList->setData($data)->setEntityId($rowId)->save();
                                array_push($sellerProductArray, $key);
                            }
                            $codPrice = $codPrice + $value;
                        }
                        $productIds = implode(',', $sellerProductArray);
                        $data = [
                            'order_id' => $lastOrderId,
                            'item_ids' => $productIds,
                            'seller_id' => $coddata['seller_id'],
                            'cod_charges' => $codPrice,
                        ];
                        $marketplaceOrderCollection = $this->_objectManager
                            ->create('Webkul\Marketplace\Model\Orders')
                            ->getCollection()
                            ->addFieldToFilter('order_id', $lastOrderId)
                            ->addFieldToFilter('seller_id', $coddata['seller_id']);
                        foreach ($marketplaceOrderCollection as $marketplaceOrder) {
                            $rowId = $marketplaceOrder->getEntityId();
                            $marketplaceOrderModel = $this->_objectManager->create(
                                'Webkul\Marketplace\Model\Orders'
                            )->load($rowId);
                            $marketplaceOrderModel->setCodCharges($codPrice);
                            $marketplaceOrderModel->save();
                        }
                        $codOrderCollection = $this->_objectManager
                            ->create('Webkul\Mpcashondelivery\Model\Codorders');
                        $codOrderCollection->setData($data);
                        $codOrderCollection->save();
                    }
                }
            }
            $this->_checkoutSession->unsSellerCodInfo();
        }
    }
}
