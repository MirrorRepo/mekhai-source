<?php

/**
* Webkul Software
* @category Webkul
* @package Webkul_MpSellerProductSearch
* @author Webkul
* @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
* @license https://store.webkul.com/license.html
*/

namespace Webkul\MpSellerProductSearch\Block;

/**
 * Marketplace Seller Product Search  
 *
 */
class MpSellerProductSearch extends  \Magento\Framework\View\Element\Template
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Webkul\Marketplace\Helper\Data $helper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * [getProfileUrl ]
     * @return [string] [seller shop name]
     */

    public function getProfileUrl()
    {
        $shopUrl = $this->_helper->getCollectionUrl(); 
        if (!$shopUrl) { 
            return $shopUrl = $this->getRequest()->getParam('shop'); 
        } 
        return $shopUrl;
    }


    public function getSearchText()
    {
        $searchText = $this->getRequest()->getParam('name');
        return $searchText;
    }
}