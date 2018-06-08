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

namespace Webkul\Mpcashondelivery\Controller\Adminhtml\Products;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;

/**
 * Class Massupdate.
 */
class Massupdate extends \Magento\Backend\App\Action
{
    /**
     * @var Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;
    /**
     * @var Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_collectionFactory;
    /**
     * @var Magento\Catalog\Model\Product
     */
    protected $_catalogProduct;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     * @param Product           $catalogProduct
     */
    
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ProductFactory $catalogProduct
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_catalogProduct = $catalogProduct;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    
    public function execute()
    {
        try {
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $status = $this->getRequest()->getParam('codstatus');
            foreach ($collection as $item) {
                $productId = $item->getMageproductId();
                $product = $this->_catalogProduct->create()->load($productId);
                $product->setCodAvailable($status);
                $product->save();
            }
            $this->messageManager->addSuccess(
                __(
                    'A total of %1 product(s) have been updated.',
                    $collection->getSize()
                )
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('marketplace/product/index');
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpcashondelivery::mpcodrates');
    }
}
