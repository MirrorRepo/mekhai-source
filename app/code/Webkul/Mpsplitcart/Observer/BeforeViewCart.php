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
use Magento\Framework\Message\ManagerInterface;

/**
 * Webkul Mpsplitcart BeforeViewCart Observer
 */
class BeforeViewCart implements ObserverInterface
{
    /**
     * @var Webkul\Mpsplitcart\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlInterface;

    /**
     * [__construct ]
     *
     * @param \Webkul\Mpsplitcart\Helper\Data $helper
     * @param ManagerInterface                $messageManager
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        \Webkul\Mpsplitcart\Helper\Data $helper,
        ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->_helper     = $helper;
        $this->messageManager = $messageManager;
        $this->_urlInterface = $urlInterface;
    }

    /**
     * [executes on controller_action_predispatch_checkout_index_index event]
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $result = $this->_helper->checkSplitCart();
        $session = $this->_helper->getCheckoutRemoveSession();

        if (count($result)>1
            && $this->_helper->checkMpsplitcartStatus()
            && (!$session || $session!==1 || $session==null)
        ) {
            $this->messageManager->addError(
                __('At a time you can checkout only one seller\'s products. Remaining other products will be saved into your cart.')
            );

            $url = $this->_urlInterface->getUrl('checkout/cart');
            $observer->getControllerAction()
                ->getResponse()
                ->setRedirect($url);
        }
    }
}
