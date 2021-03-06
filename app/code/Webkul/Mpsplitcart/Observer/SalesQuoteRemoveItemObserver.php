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
 * Webkul Mpsplitcart SalesQuoteRemoveItemObserver Observer
 */
class SalesQuoteRemoveItemObserver implements ObserverInterface
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
     * [executes when sales_quote_remove_item event hit and used to
     *  update virtual cart when any item is removed from sales quote]
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $itemId = $quoteItem->getItemId();
        
        $virtualCart = $this->_helper->getVirtualCart();
        $removeItemCheck = $this->_helper->getCheckoutRemoveSession();
        $moduleEnabledCheck = $this->_helper->checkMpsplitcartStatus();

        if ($virtualCart
            && is_array($virtualCart)
            && $virtualCart !== ""
            && $moduleEnabledCheck
            && (!$removeItemCheck
            || $removeItemCheck !== 1
            || $removeItemCheck == null)
        ) {
            foreach ($virtualCart as $sellerId => $sellerArray) {
                foreach ($sellerArray as $productId => $productData) {
                    if ($productData['item_id'] == $itemId) {
                        unset($virtualCart[$sellerId][$productId]);
                    }
                }
                $check = $this->_helper->checkEmptyVirtualCart(
                    $virtualCart[$sellerId]
                );
                if ($check) {
                    unset($virtualCart[$sellerId]);
                }
            }
            $this->_helper->setVirtualCart($virtualCart);
        }
    }
}
