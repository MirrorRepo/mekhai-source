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

class Grid extends \Webkul\Mpcashondelivery\Controller\Adminhtml\Adminorder
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;
    
    /**
     * @param \Magento\Backend\App\Action\Context          $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->_resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('mpcod.report.grid');
        return $resultLayout;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpcashondelivery::mpcodorders'
        );
    }
}
