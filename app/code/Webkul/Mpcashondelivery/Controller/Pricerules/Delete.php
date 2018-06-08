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

namespace Webkul\Mpcashondelivery\Controller\Pricerules;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\RequestInterface;

class Delete extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @param Context          $context
     * @param Session          $customerSession
     * @param FormKeyValidator $formKeyValidator
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
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
     * Default customer account page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $helper = $this->_objectManager
                ->create('Webkul\Mpcashondelivery\Helper\Data');
        try {
            $priceruleId = $this->getRequest()->getParam('id');
            if ($priceruleId != '') {
                $rightseller = $helper->isRightSeller($priceruleId);
                if ($rightseller == 1) {
                    $priceruleModel = $this->_objectManager
                                    ->create('Webkul\Mpcashondelivery\Model\Pricerules')
                                    ->load($priceruleId);
                    $priceruleModel->delete();
                    $this->messageManager->addSuccess(
                        __('Cash On Delivery rates are successfully deleted!')
                    );
                    return $this->resultRedirectFactory
                            ->create()
                            ->setPath('mpcashondelivery/pricerules/index');
                } else {
                    $this->messageManager->addError(
                        __('You are not authorized to delete this cash on delivery rate.')
                    );
                }
            } else {
                $this->messageManager->addError(
                    __('Something went wrong, Please try again.')
                );
            }
            return $this->resultRedirectFactory->create()
                ->setPath('mpcashondelivery/pricerules/index');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()
                    ->setPath('mpcashondelivery/pricerules/index');
        }
    }
}
