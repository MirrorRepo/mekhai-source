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

/**
 * Webkul Mpsplitcart ShoppingCart Observer
 */
class ShoppingCart implements ObserverInterface
{
    /**
     * @var \Webkul\Mpsplitcart\Helper\Data
     */
    protected $_helper;

    /**
     * [__construct ]
     *
     * @param \Webkul\Mpsplitcart\Helper\Data $helper
     */
    public function __construct(
        \Webkul\Mpsplitcart\Helper\Data $helper
    ) {
        $this->_helper     = $helper;
    }

    /**
     * [executes on controller_action_predispatch_checkout_cart_index event
     *  and used to add virtual cart items into quote]
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helper->checkMpsplitcartStatus()) {
            $this->_helper->addVirtualCartToQuote();
            $this->_helper->addQuoteToVirtualCart();
        }
    }
}
