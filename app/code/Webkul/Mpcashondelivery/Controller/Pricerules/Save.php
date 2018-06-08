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
use Magento\Store\Model\StoreManagerInterface;

class Save extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var storeManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @param Context               $context
     * @param Session               $customerSession
     * @param FormKeyValidator      $formKeyValidator
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        StoreManagerInterface $storeManager,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_storeManager = $storeManager;
        $this->_mpHelper = $mpHelper;
        parent::__construct(
            $context
        );
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }
    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
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
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $helper = $this->_objectManager->create('Webkul\Mpcashondelivery\Helper\Data');
        $websiteId = $this->_storeManager->getStore(true)
                    ->getWebsite()->getId();
        if ($this->getRequest()->isPost()) {
            try {
                $wholedata=[];
                $wholedata = $this->getRequest()->getParams();
                list($data, $errors) = $this->validatePost();
                if (empty($errors)) {
                    $wholedata['seller_id'] = $this->_mpHelper->getCustomerId();
                    $id='';
                    if (array_key_exists('id', $wholedata)) {
                        $id = $wholedata['id'];
                    }
                    if ($wholedata['is_range']==0) {
                        $wholedata['zipcode'] = '';
                    } else {
                        $wholedata['dest_zip_from'] = 0;
                        $wholedata['dest_zip_to'] = 0;
                    }
                    if ($id!='') {
                        $rightseller=$helper->isRightSeller($id);
                        if ($rightseller==1) {
                            $priceruleModel = $this->_objectManager
                                        ->create('Webkul\Mpcashondelivery\Model\Pricerules')
                                        ->load($id);
                            $priceruleModel->setDestCountryId($wholedata['dest_country_id'])
                                    ->setDestRegionId($wholedata['dest_region_id'])
                                    ->setDestZipFrom($wholedata['dest_zip_from'])
                                    ->setDestZipTo($wholedata['dest_zip_to'])
                                    ->setPriceType($wholedata['price_type'])
                                    ->setFixedPrice($wholedata['fixed_price'])
                                    ->setPercentagePrice($wholedata['percentage_price'])
                                    ->setWeightFrom($wholedata['weight_from'])
                                    ->setWeightTo($wholedata['weight_to'])
                                    ->setSellerId($wholedata['seller_id'])
                                    ->setIsRange($wholedata['is_range'])
                                    ->setZipcode($wholedata['zipcode'])
                                    ->setWebsiteId($websiteId)
                                    ->setEntityId($id);
                            $saved = $priceruleModel->save();
                            $this->messageManager->addSuccess(__('Price rule updated successfully.'));
                        } else {
                            $this->messageManager->addError(__('You are not authorized to edit it.'));
                            return $this->resultRedirectFactory
                                    ->create()->setPath('mpcashondelivery/pricerules/index');
                        }
                    } else {
                        $priceruleModel = $this->_objectManager
                                        ->create('Webkul\Mpcashondelivery\Model\Pricerules');
                        $priceruleModel->setDestCountryId($wholedata['dest_country_id'])
                                    ->setDestRegionId($wholedata['dest_region_id'])
                                    ->setDestZipFrom($wholedata['dest_zip_from'])
                                    ->setDestZipTo($wholedata['dest_zip_to'])
                                    ->setPriceType($wholedata['price_type'])
                                    ->setFixedPrice($wholedata['fixed_price'])
                                    ->setPercentagePrice($wholedata['percentage_price'])
                                    ->setWeightFrom($wholedata['weight_from'])
                                    ->setWeightTo($wholedata['weight_to'])
                                    ->setSellerId($wholedata['seller_id'])
                                    ->setIsRange($wholedata['is_range'])
                                    ->setWebsiteId($websiteId)
                                    ->setZipcode($wholedata['zipcode']);
                        $saved = $priceruleModel->save();
                        $this->messageManager->addSuccess(
                            __('Price rule saved successfully.')
                        );
                    }
                    return $this->resultRedirectFactory
                        ->create()
                        ->setPath('mpcashondelivery/pricerules/index');
                } else {
                    foreach ($errors as $message) {
                        $this->messageManager->addError($message);
                    }
                    return $this->resultRedirectFactory
                        ->create()
                        ->setPath('mpcashondelivery/pricerules/index');
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory
                    ->create()
                    ->setPath('mpcashondelivery/pricerules/index');
            }
        } else {
            return $this->resultRedirectFactory
                ->create()
                ->setPath('mpcashondelivery/pricerules/index');
        }
    }

    protected function validatePost()
    {
        $errors = [];
        $data = [];
        $wholedata = $this->getRequest()->getParams();
        foreach ($this->getRequest()->getParams() as $code => $value) {
            switch ($code) :
                case 'dest_country_id':
                    if (trim($value) == '') {
                        $errors[] = __('Destination Country has to be completed');
                    } else {
                        $data[$code] = $value;
                    }
                    break;
                case 'dest_region_id':
                    if (trim($value) == '') {
                        $errors[] = __('Destination State/Region has to be completed');
                    } else {
                        $data[$code] = $value;
                    }
                    break;
                case 'dest_zip_from':
                    if ($wholedata['is_range']==0) {
                        if (trim($value) == '') {
                            $errors[] = __('Zip/Post Code From has to be completed');
                        } else {
                            $data[$code] = $value;
                        }
                    }
                    break;
                case 'dest_zip_to':
                    if ($wholedata['is_range']==0) {
                        if (trim($value) == '') {
                            $errors[] = __('Zip/Post Code To has to be completed');
                        } else {
                            $data[$code] = $value;
                        }
                    }
                    break;
                case 'zipcode':
                    if ($wholedata['is_range'] == 1) {
                        if ($value == '' || $value == '*') {
                            $errors[] = __('Zipcode field must be specific.');
                        } else {
                            $data[$code] = $value;
                        }
                    }
                    break;
                case 'price_type':
                    if (trim($value) == '') {
                        $errors[] = __('Price Type has to be seleted');
                    } else {
                        $data[$code] = $value;
                    }
                    break;
                case 'fixed_price':
                    if ($wholedata['price_type']==0) {
                        if (!preg_match("/^([0-9])+?[0-9.]*$/", $value)) {
                            $errors[] = __('Price should contain only decimal numbers');
                        } else {
                            $data[$code] = $value;
                        }
                    }
                    break;
                case 'percentage_price':
                    if ($wholedata['price_type']==1) {
                        if (!preg_match("/^([0-9])+?[0-9.]*$/", $value)) {
                            $errors[] = __('Percentage(%) Rate should contain only decimal numbers');
                        } else {
                            $data[$code] = $value;
                        }
                    }
                    break;
                case 'weight_from':
                    if (!preg_match("/^([0-9])+?[0-9.]*$/", $value)) {
                        $errors[] = __('Weight From should contain only decimal numbers');
                    } else {
                        $data[$code] = $value;
                    }
                    break;
                case 'weight_to':
                    if (!preg_match("/^([0-9])+?[0-9.]*$/", $value)) {
                        $errors[] = __('Weight To should contain only decimal numbers');
                    } else {
                        $data[$code] = $value;
                    }
                    break;
            endswitch;
        }
        return [$data, $errors];
    }
}
