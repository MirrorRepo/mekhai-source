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

namespace Webkul\Mpcashondelivery\Controller\Adminhtml\Adminorder;

use Webkul\Mpcashondelivery\Model\CodordersFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;

class Builder
{
    /**
     * @var \Webkul\Mpcashondelivery\Model\CodordersFactory
     */
    protected $_codordersFactory;
    
    /**
     * @param CodordersFactory $_codordersFactory
     */
    
    public function __construct(
        CodordersFactory $_codordersFactory
    ) {
        $this->_codordersFactory = $_codordersFactory;
    }
    /**
     * Build Mpcashondelivery based on user request
    *
    * @param RequestInterface $request
    * @return \Webkul\Mpcashondelivery\Model\Codorders
    */
    public function build(RequestInterface $request)
    {
        $rowId = (int)$request->getParam('id');
        $shipping = $this->_codordersFactory->create();
        if ($rowId) {
            try {
                $shipping->load($rowId);
            } catch (\Exception $e) {
            }
        }
        return $shipping;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpcashondelivery::mpcodorders'
        );
    }
}
