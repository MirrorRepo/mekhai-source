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
namespace Webkul\Mpsplitcart\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;

/**
 * Mpsplitcart Block
 */
class Index extends \Magento\Checkout\Block\Cart
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Webkul\Mpsplitcart\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cartModel;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * [__construct ]
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager
     * @param \Magento\Catalog\Model\ResourceModel\Url         $catalogUrl
     * @param \Magento\Checkout\Helper\Cart                    $cartHelper
     * @param \Webkul\Mpsplitcart\Helper\Data                  $helper
     * @param \Magento\Checkout\Model\Cart                     $cart
     * @param \Magento\Framework\Pricing\Helper\Data           $priceHelper
     * @param \Magento\Framework\App\Http\Context              $httpContext
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Webkul\Mpsplitcart\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $catalogUrl,
            $cartHelper,
            $httpContext,
            $data
        );
        $this->_cartHelper = $cartHelper;
        $this->_helper = $helper;
        $this->_objectManager = $objectManager;
        $this->checkoutSession = $checkoutSession;
        $this->_cartModel = $cart;
        $this->_priceHelper = $priceHelper;
    }

    /**
     * [getSellerData get seller array in order to
     * show items at shopping cart accr. to sellers]
     *
     * @return [array]
     */
    public function getSellerData()
    {
        $cart = $this->_cartModel->getQuote();
        $cartArray = [];
        foreach ($cart->getAllItems() as $item) {
            if (!$item->hasParentItemId()) {
                $options = $item->getBuyRequest()->getData();

                if (array_key_exists("mpassignproduct_id", $options)) {
                    $mpAssignId = $options["mpassignproduct_id"];
                    $sellerId = $this->_helper->getSellerIdFromMpassign(
                        $mpAssignId
                    );
                } else {
                    $sellerId = $this->_helper->getSellerId($item->getProductId());
                }

                $price =  $item->getRowTotal();

                $formattedPrice = $this->_priceHelper->currency(
                    $price,
                    true,
                    false
                );
                $cartArray[$sellerId][$item->getId()] = $formattedPrice;

                if (!isset($cartArray[$sellerId]['total'])
                    || $cartArray[$sellerId]['total']==null
                ) {
                    $cartArray[$sellerId]['total'] = $price;
                } else {
                    $cartArray[$sellerId]['total'] += $price;
                }

                $formattedPrice = $this->_priceHelper->currency(
                    $cartArray[$sellerId]['total'],
                    true,
                    false
                );
                $cartArray[$sellerId]['formatted_total'] = $formattedPrice;
            }
        }
        return $cartArray;
    }

    /**
     * [getMpsplitcartEnable get splitcart is enable or not]
     *
     * @return void
     */
    public function getMpsplitcartEnable()
    {
        return $this->_helper->checkMpsplitcartStatus();
    }

    /**
     * [getCartTotal used to get cart total]
     *
     * @return [string] [returns formatted total price]
     */
    public function getCartTotal()
    {
        $cart = $this->_cartModel->getQuote();
        $cartTotal = 0;
        foreach ($cart->getAllItems() as $item) {
            if (!$item->hasParentItemId()) {
                $sellerId=$this->_helper->getSellerId($item->getProductId());
                $price =  $item->getProduct()->getQuoteItemRowTotal();

                if (!$price) {
                    $price =  $item->getBaseRowTotal();
                }

                $cartTotal += $price;
            }
        }
        $formattedPrice = $this->_priceHelper->currency(
            $cartTotal,
            true,
            false
        );
        $cartTotal = $formattedPrice;
        
        return $cartTotal;
    }
}
