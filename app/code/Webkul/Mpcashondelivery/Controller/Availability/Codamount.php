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

namespace Webkul\Mpcashondelivery\Controller\Availability;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class Codamount extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var Webkul\Mpcashondelivery\Helper\Data
     */
    protected $_helper;
    /**
     * @var Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var Magento\Checkout\Model\Cart
     */
    protected $_cart;
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @param Context                                    $context
     * @param \Webkul\Mpcashondelivery\Helper\Data       $helper
     * @param \Magento\Checkout\Model\Session            $checkoutSession
     * @param PageFactory                                $resultPageFactory
     * @param \Magento\Checkout\Model\Cart               $cart
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        Context $context,
        \Webkul\Mpcashondelivery\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->_helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_cart = $cart;
        $this->_quoteRepository = $quoteRepository;

        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $codAmount = $this->_helper->getAppliedPriceRules();
        if ($codAmount['error']!=1) {
            $cartQuote = $this->_checkoutSession->getQuote();
            $store = $cartQuote->getStore();
            $cartQuote->setMpcashondelivery($codAmount['handlingfee']);
            $baseCodAmount = $this->getBaseCodAmount($codAmount['handlingfee'], $store);
            $cartQuote->setBaseMpcashondelivery($baseCodAmount);
            $cartQuote->collectTotals();
            $this->_quoteRepository->save($cartQuote);
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($codAmount);
        return $resultJson;
    }
    public function getBaseCodAmount($amount, $store = null)
    {
        $amount = $this->_helper->baseCurrencyAmount($amount, $store);
        return $amount;
    }
}
