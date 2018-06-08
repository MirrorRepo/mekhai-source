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

namespace Webkul\Mpcashondelivery\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;

class PaymentMethod extends AbstractMethod
{
    const CODE = 'mpcashondelivery';

    /**
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * Bank Transfer payment block paths.
     *
     * @var string
     */
    protected $_formBlockType = 'Webkul\Mpcashondelivery\Block\Payment\Mpcashondelivery';
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_isInitializeNeeded = false;

    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory       $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory            $customAttributeFactory
     * @param \Magento\Payment\Helper\Data                            $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface      $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger                    $logger
     * @param \Magento\Store\Model\StoreManagerInterface              $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
     * @param array                                                   $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
    }

    /**
     * Authorize payment.
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @param float                                                                      $amount
     *
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $helper = $this->_objectManager->create('Webkul\Mpcashondelivery\Helper\Data');
        $codCharges = $helper->getAppliedPriceRules();
        if ($codCharges['error'] != 0) {
            throw new LocalizedException(__($codCharges['cod_message']));
        }
        return $this;
    }

    /**
     * Payment action getter compatible with payment model.
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return $this->_scopeConfig->getValue(
            'payment/mpcashondelivery/payment_action',
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getStoreId()
        );
    }

    /**
     * Check whether payment method is available or not
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     *
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (parent::isAvailable($quote)) {
            if ($quote != null) {
                $helper = $this->_objectManager->create('Webkul\Mpcashondelivery\Helper\Data');
                $minTotal = $helper->getMinTotal();
                $maxTotal = $helper->getMaxTotal();
                if ($quote->getBaseGrandTotal() < $minTotal || $quote->getBaseGrandTotal() > $maxTotal) {
                    return false;
                }
                $specificcountry = explode(',', $helper->getSpecificCountry());
                if ($helper->getAllowCountry() != 0) {
                    if (!in_array($quote->getBillingAddress()->getCountry(), $specificcountry)) {
                        return false;
                    }
                }
            }
        } else {
            return false;
        }

        return true;
    }
}
