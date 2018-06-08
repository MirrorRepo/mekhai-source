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

class Delete extends \Magento\Backend\App\Action
{
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager
                    ->create('Webkul\Mpcashondelivery\Model\Pricerules');
            $id = $this->getRequest()->getParam('entity_id');
            if ($id) {
                $model->load($id);
            }
            try {
                $model->delete();
                $this->messageManager->addSuccess('Cash on Delivery Rate successfully deleted.');
                return $resultRedirect->setPath('mpcashondelivery/pricerules/index');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while Deleting the data.')
                );
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('mpcashondelivery/updateprice/index');
        }
        return $resultRedirect->setPath('mpcashondelivery/pricerules/index');
    }
}
