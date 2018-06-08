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

namespace Webkul\Mpcashondelivery\Model\Total;

class Quotetotal extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Magento\Quote\Model\QuoteValidator
     */
    protected $_quoteValidator = null;
     /**
     * \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * \Webkul\Mpcashondelivery\Helper\Data
     */
    protected $_mpcodHelper;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Quote\Model\QuoteValidator       $quoteValidator
     * @param \Webkul\Mpcashondelivery\Helper\Data      $mpcodHeper
     * @param \Magento\Framework\Model\Context          $context
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Webkul\Mpcashondelivery\Helper\Data $mpcodHeper,
        \Magento\Framework\Model\Context $context
    ) {
        $this->_objectManager = $objectManager;
        $this->_quoteValidator = $quoteValidator;
        $this->_mpcodHelper = $mpcodHeper;
    }
    /**
     * Collect grand total address amount.
     *
     * @param \Magento\Quote\Model\Quote                          $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total            $total
     *
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }
        $method = $quote->getPayment()->getMethod();
        $store = $quote->getStore();
        if ($method == 'mpcashondelivery') {
            $balance = $this->getMpcodAmount();
            if ($balance == 0) {
                $baseAmunt = 0;
            } else {
                $baseAmunt = $this->getBaseCodAmount($balance, $store);
            }
        } else {
            $balance = 0;
            $baseAmunt = 0;
        }
        $total->setTotalAmount('mpcashondelivery', $balance);
        $total->setBaseTotalAmount('mpcashondelivery', $baseAmunt);

        $quote->setMpcashondelivery($balance);
        $quote->setBaseMpcashondelivery($baseAmunt);

        $total->setMpcashondelivery($balance);
        $total->setBaseMpcashondelivery($baseAmunt);

        return $this;
    }

    protected function getMpcodAmount()
    {
        $helper = $this->_mpcodHelper;
        $codCharges = $helper->getAppliedQuotePriceRules();

        return $codCharges;
    }
    /**
     * Assign subtotal amount and label to address object.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total              $total
     *
     * @return array
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        return [
            'code' => 'mpcashondelivery',
            'title' => 'Mpcashondelivery',
            'value' => $total->getMpcashondelivery(),
        ];
    }

    /**
     * Get Subtotal label.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Mpcashondelivery');
    }
    public function getBaseCodAmount($amount, $store)
    {
        $amount = $this->_mpcodHelper->baseCurrencyAmount($amount, $store);
        return $amount;
    }
}
