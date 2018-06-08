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
namespace Webkul\Mpcashondelivery\Block;

use \Magento\Catalog\Model\Product;
use \Webkul\Mpcashondelivery\Helper\Data;

class Checkavailability extends \Magento\Framework\View\Element\Template
{
    /**
     * @var catalog/product
     */
    protected $_product = null;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Webkul\Mpcashondelivery\Helper\Data
     */
    protected $_mpcodHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;
    
    /**
     * @param Context                           $context
     * @param \Magento\Customer\Model\Session   $customerSession
     * @param \Magento\Framework\Registry       $registry
     * @param array                             $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        Data $mpcodHelper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $registry;
        $this->_mpcodHelper =$mpcodHelper;
        $this->_mpHelper = $mpHelper;
        parent::__construct($context, $data);
    }

    // get session Customer id
    public function getCustomerId()
    {
        return $this->_mpcodHelper->getCustomerId();
    }

    // load product from registry
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }

        return $this->_product;
    }
    
    public function getHelper()
    {
        return $this->_mpcodHelper;
    }
}
