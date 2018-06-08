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
namespace Webkul\Mpcashondelivery\Controller\Adminhtml\Seller;

use Magento\Backend\App\Action;

class Formatedprice extends \Magento\Backend\App\Action
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpcashondelivery::mpcodorders'
        );
    }
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $price = 0;
        $paramData = $this->getRequest()->getParams();
        if (array_key_exists('price', $paramData)) {
            $price = $paramData['price'];
        }
        $formatedprice = $this->_objectManager
            ->get('Magento\Framework\Pricing\Helper\Data')
            ->currency($price, true, false);
        $this->getResponse()->setHeader('Content-type', 'text/html');
        $this->getResponse()->setBody($formatedprice);
    }
}
