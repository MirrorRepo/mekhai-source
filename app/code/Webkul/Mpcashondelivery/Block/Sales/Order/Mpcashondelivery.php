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
/**
 * mpcashondelivery amount modification block.
 */
namespace Webkul\Mpcashondelivery\Block\Sales\Order;

use Magento\Sales\Model\Order;

class Mpcashondelivery extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var Order
     */
    protected $_order;
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;
    /**
     * @var \Webkul\Mpcashondelivery\Helper\Data
     */
    protected $_mpcodHelper;
    /**
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Framework\ObjectManagerInterface         $objectManager
     * @param \Webkul\Mpcashondelivery\Helper\Data              $mpcodHelper
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Mpcashondelivery\Helper\Data $mpcodHelper,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_mpcodHelper = $mpcodHelper;
        parent::__construct($context, $data);
    }
    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    public function displayFullSummary()
    {
        return true;
    }
    /**
     * Initialize all order totals
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $title = $this->_mpcodHelper
                ->getPymentAnountTitle();
        $store = $this->getStore();
        if ($this->_order->getPayment()->getMethod()=='mpcashondelivery') {
            $mpcashondelivery = new \Magento\Framework\DataObject(
                [
                    'code' => 'mpcashondelivery',
                    'strong' => false,
                    'value' => $this->_order->getMpcashondelivery(),
                    'base_value' => $this->_order->getBaseMpcashondelivery(),
                    'label' => __($title),
                ]
            );
            $parent->addTotal($mpcashondelivery, 'mpcashondelivery');
        }
        return $this;
    }
    /**
     * Get order store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }
    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }
    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }
    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
