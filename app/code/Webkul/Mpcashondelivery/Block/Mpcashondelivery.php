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

use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Product;

class Mpcashondelivery extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;
    /**
     * @var \Webkul\Mpcashondelivery\Helper\Data
     */
    protected $_mpcodHelper;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;
    /**
    * @param Context    $context
    * @param Product    $product
    * @param array      $data
    */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        Product $product,
        \Webkul\Mpcashondelivery\Helper\Data $mpcodHelper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        array $data = []
    ) {
        $this->_product = $product;
        $this->_mpcodHelper = $mpcodHelper;
        $this->_mpHelper = $mpHelper;
        parent::__construct($context, $data);
    }
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }
    public function getCurrentUrl()
    {
        // Give the current url of recently viewed page
        return $this->_urlBuilder->getCurrentUrl();
    }
    public function getProduct()
    {
        return $this->_product;
    }
    public function getMpCodHelper()
    {
        return $this->_mpcodHelper;
    }
    public function getMpHelper()
    {
        return $this->_mpHelper;
    }
}
