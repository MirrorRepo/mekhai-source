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
namespace Webkul\Mpfreeshipping\Block;

use Magento\Catalog\Model\Product;

class ManageFreeShipping extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\Mpfreeshipping\Helper\Data
     */
    protected $_currentHelper;
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;


    /**
     * @param \Magento\Catalog\Block\Product\Context             $context
     * @param \Webkul\MpFedexShipping\Helper\Data                $currentHelper
     * @param \Magento\Customer\Model\Session                    $customerSession
     * @param \Magento\Config\Model\Config\Source\Yesno          $yesNo
     * @param \Magento\Framework\Registry                        $coreRegistry
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Mpfreeshipping\Helper\Data $currentHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_currentHelper = $currentHelper;
        $this->_customerSession = $customerSession;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }
    /**
     * Prepare global layout.
     *
     * @return $this
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
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
     /**
      * Retrieve information from carrier configuration.
      *
      * @param string $field
      *
      * @return void|false|string
      */
    public function getConfigData($field)
    {
        return $this->getHelper()->getConfigData($field);
    }
    /**
     * get current module helper.
     *
     * @return \Webkul\MpDHLShipping\Helper\Data
     */
    public function getHelper()
    {
        return $this->_currentHelper;
    }
}
