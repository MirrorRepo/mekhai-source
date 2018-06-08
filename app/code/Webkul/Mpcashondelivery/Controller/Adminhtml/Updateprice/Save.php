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

class Save extends \Magento\Backend\App\Action
{
    /**
     * Pricerules model
     *
     * @var string
     */
    protected $_model = 'Webkul\Mpcashondelivery\Model\Pricerules';
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpcashondelivery::mpcodrates'
        );
    }
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create($this->_model);
            $id = $this->getRequest()->getParam('entity_id');
            if ($id) {
                $model->load($id);
            }
            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccess(
                    __('Cash On Delivery Rates are Successfully saved.')
                );
                $this->_objectManager->get('Magento\Backend\Model\Session')
                    ->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        'mpcashondelivery/updateprice/index',
                        [
                            'entity_id' => $model->getEntityId(),
                            '_current' => true
                        ]
                    );
                }
                return $resultRedirect->setPath('mpcashondelivery/pricerules/index');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving the data.')
                );
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath(
                'mpcashondelivery/updateprice/index',
                ['entity_id' => $this->getRequest()->getParam('entity_id')]
            );
        }
        return $resultRedirect->setPath('mpcashondelivery/pricerules/index');
    }
}
