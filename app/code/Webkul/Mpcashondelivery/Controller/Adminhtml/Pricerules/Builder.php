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

namespace Webkul\Mpcashondelivery\Controller\Adminhtml\Pricerules;

use \Webkul\Mpcashondelivery\Model\PricerulesFactory ;
use Magento\Cms\Model\Wysiwyg as WysiwygModel;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Registry;

class Builder
{
    /**
     * @var \Webkul\Mpcashondelivery\Model\MpcashondeliveryFactory
     */
    protected $_codpriceruleFactory;

    /**
     * @param PricerulesFactory $_codpriceruleFactory
     */
    public function __construct(
        PricerulesFactory $codpriceruleFactory
    ) {
        $this->_codpriceruleFactory = $codpriceruleFactory;
    }

    /**
     * Build Mpcashondelivery based on user request
     *
     * @param RequestInterface $request
     * @return \Webkul\Mpcashondelivery\Model\Mpcashondelivery
     */
    public function build(RequestInterface $request)
    {
        $rowId = (int)$request->getParam('id');
        $codPricerule = $this->_codpriceruleFactory->create();
        if ($rowId) {
            try {
                $codPricerule->load($rowId);
            } catch (\Exception $e) {
            }
        }
        return $codPricerule;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpcashondelivery::mpcodrates');
    }
}
