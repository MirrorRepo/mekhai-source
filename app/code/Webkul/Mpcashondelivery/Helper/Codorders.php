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

namespace Webkul\Mpcashondelivery\Helper;

class Codorders extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context     $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session           $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->_mpHelper = $mpHelper;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }
    // get quantity by order id
    public function getPricebyorder($orderId)
    {
        $sellerId = $this->getCustomerId();
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                    ->getCollection();
        $name = '';
        $collection->getSelect()
            ->where('seller_id ='.$sellerId)
            ->columns('SUM(actual_seller_amount) AS qty')
            ->group('order_id');
        foreach ($collection as $coll) {
            if ($coll->getOrderId() == $orderId) {
                return $coll->getQty();
            }
        }
    }
    // currenct customer Id
    public function getCustomerId()
    {
        return $this->_mpHelper->getCustomerId();
    }
}
