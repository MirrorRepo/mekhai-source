<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpfreeshipping
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpfreeshipping\Helper;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Mpfreeshipping data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var string
     */
    protected $_code = 'mpfreeshipping';
    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Configurable
     */
    protected $_configurable;

    /**
     * @param Magento\Framework\App\Helper\Context        $context
     * @param Magento\Catalog\Model\ResourceModel\Product $product
     * @param Magento\Store\Model\StoreManagerInterface   $_storeManager
     * @param Magento\Directory\Model\Currency            $currency
     * @param Magento\Framework\Locale\CurrencyInterface  $localeCurrency
     * @param \Magento\Customer\Model\Session             $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        Configurable $configurable
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_configurable = $configurable;
    }
    
    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        if (empty($this->_code)) {
            return false;
        }
        $path = 'carriers/'.$this->_code.'/'.$field;

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()
        );
    }

    /**
     * return current customer session.
     *
     * @return \Magento\Customer\Model\Session
     */
    public function _getCustomerData()
    {
        return $this->_customerSession->getCustomer();
    }
}
