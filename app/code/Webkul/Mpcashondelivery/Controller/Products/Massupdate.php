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

namespace Webkul\Mpcashondelivery\Controller\Products;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ProductFactory;

class Massupdate extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    protected $_magentoproduct;
    /**
     * @param Context          $context
     * @param Session          $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param Product          $magentoProduct
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        ProductFactory $magentoProduct
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_magentoproduct = $magentoProduct;
        parent::__construct(
            $context
        );
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Update Cod status of products.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $helper = $this->_objectManager->create('Webkul\Mpcashondelivery\Helper\Data');
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $resultRedirect->setPath('mpcashondelivery/products/index');
                }
                $productIds = $this->getRequest()->getParam('mpcashondelivery');
                $status = $this->getRequest()->getParam('massstatus');
                if (!is_array($productIds)) {
                    $this->messageManager->addError(__('Please select Product ids to update.'));
                } else {
                    $unauthorized = [];
                    $i = 0;
                    foreach ($productIds as $productId) {
                        $rightseller = $helper->isRightSellerforProduct($productId);
                        if ($rightseller == 1) {
                            ++$i;
                            $productModel = $this->getProduct($productId);
                            $productModel->setCodAvailable($status);
                            $productModel->save();
                        } else {
                            array_push($unauthorized, $productId);
                        }
                    }
                    if (count($unauthorized)) {
                        $this->messageManager->addError(
                            __(
                                'You are not authorized to delete id(s) %1',
                                implode(',', $unauthorized)
                            )
                        );
                    } else {
                        $this->messageManager->addSuccess(
                            __('Total of %1 record(s) were successfully updated', $i)
                        );
                    }
                }

                return $this->resultRedirectFactory->create()
                    ->setPath('mpcashondelivery/products/index');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()
                    ->setPath('mpcashondelivery/products/index');
            }
        } else {
            return $this->resultRedirectFactory->create()
                ->setPath('mpcashondelivery/products/index');
        }
    }

    public function getProduct($productId)
    {
        return $this->_magentoproduct->create()->load($productId);
    }
}
