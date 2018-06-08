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

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Field id
     */
    const ID_FIELD = 'entity_id';
    /**
     * Pricerules model
     *
     * @var string
     */
    protected $_model = 'Webkul\Mpcashondelivery\Model\Pricerules';
    /**
     * Pricerules collection
     *
     * @var string
     */
    protected $_collection = 'Webkul\Mpcashondelivery\Model\ResourceModel\Pricerules\Collection';
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (array_key_exists('mpcodpricerule', $params) && !empty($params['mpcodpricerule'])) {
            try {
                $selected = $params['mpcodpricerule'];
                $this->selectedDelete($selected);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Please select item(s).'));
        }
        return $resultRedirect->setPath('*/*/index');
    }
    
    /**
     * Delete selected items
     *
     * @param array $selected
     * @return void
     * @throws \Exception
     */
    protected function selectedDelete(array $selected)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->_objectManager->get($this->_collection);
        $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);
        $this->setSuccessMessage($this->delete($collection));
    }
    
    /**
     * Delete collection items
     *
     * @param AbstractCollection $collection
     * @return int
     */
    protected function delete(AbstractCollection $collection)
    {
        $count = 0;
        foreach ($collection->getAllIds() as $id) {
            /** @var \Magento\Framework\Model\AbstractModel $model */
            $model = $this->_objectManager->get($this->_model);
            $model->load($id);
            $model->delete();
            ++$count;
        }

        return $count;
    }

    protected function setSuccessMessage($count)
    {
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $count));
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpcashondelivery::mpcodrates');
    }
}
