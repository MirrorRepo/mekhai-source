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

namespace Webkul\Mpcashondelivery\Controller\Adminhtml\Updateprice;

use Magento\Backend\App\Action;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry                $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpcashondelivery::mpcodrates'
        );
    }
    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_Mpcashondelivery::mpcodrates')
            ->addBreadcrumb(__('Manage Cash On Delivery Rates'), __('Manage Cash On Delivery Rates'));
        return $resultPage;
    }
    public function execute()
    {
        $flag = 0;
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->_objectManager
                ->create('Webkul\Mpcashondelivery\Model\Pricerules');
        if ($id) {
            $model->load($id);
            if (!$model->getEntityId()) {
                $this->messageManager->addError(
                    __('This post no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')
                ->getFormData(true);
        if (!isset($data) && $data) {
            $model->setData($data);
            $flag = 1;
        }
        if ($flag == 1 || $id) {
            $this->_coreRegistry->register('cod_pricerates', $model);
            $resultPage = $this->_initAction();
            $resultPage->addBreadcrumb(__('Edit Cash on Delivery rate'), __('Edit Cash on Delivery rate'));
            $resultPage->getConfig()->getTitle()->prepend(__('Update Cash on Delivery Rates'));
            return $resultPage;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('mpcashondelivery/pricerules/index');
        }
    }
}
