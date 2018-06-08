<?php
/**
 * Webkul Mpcashondelivery Adminhtml Controller Pricerules NewAction
 *
 * @category    Webkul
 * @package     Webkul_Mpcashondelivery
 * @author      Webkul Software Private Limited
 *
 */
namespace Webkul\Mpcashondelivery\Controller\Adminhtml\Pricerules;

use \Magento\Backend\Model\View\Result\ForwardFactory;

class NewAction extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $_resultForwardFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param ForwardFactory                      $resultForwardFactory
     */
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        ForwardFactory $resultForwardFactory
    ) {
        $this->_resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpcashondelivery::mpcodrates'
        );
    }
    /**
     * Forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultForward = $this->_resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
