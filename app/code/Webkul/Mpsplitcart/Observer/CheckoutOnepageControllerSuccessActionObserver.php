<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpsplitcart
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpsplitcart\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Webkul Mpsplitcart CheckoutOnepageControllerSuccessActionObserver Observer
 */
class CheckoutOnepageControllerSuccessActionObserver implements ObserverInterface
{
    /**
     * @var \Webkul\Mpsplitcart\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;
    
    /**
     * [__construct ]
     *
     * @param \Magento\Sales\Model\Order      $orderFactory
     * @param \Webkul\Mpsplitcart\Helper\Data $helper
     */
    public function __construct(
        \Magento\Sales\Model\Order $orderFactory,
        \Webkul\Mpsplitcart\Helper\Data $helper
    ) {
        $this->_order = $orderFactory;
        $this->_helper     = $helper;
    }

    /**
     * [executes when checkout_onepage_controller_success_action event hit,
     * and used to update virtual cart after successfully placed an order]
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->_helper->checkMpsplitcartStatus()) {
                $orderIds = $observer->getOrderIds();
                $itemIds = [];
                foreach ($orderIds as $orderId) {
                    $orderInformation = $this->getOrderInfo($orderId);
                    foreach ($orderInformation->getAllVisibleItems() as $item) {
                        $itemIds[$item->getProductId()] = $item->getQuoteItemId();
                    }
                }
                $this->_helper->updateVirtualCart($itemIds);
            }
        } catch (\Exception $e) {
            // $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * [getOrderInfo loads order]
     *
     * @param  [integer] $orderId [order id]
     * @return [object]
     */
    public function getOrderInfo($orderId)
    {
        $orderInformation = $this->_order->load($orderId);
        return $orderInformation;
    }
}
