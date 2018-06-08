<?php
    /**
     * Webkul Software.
     *
     * @category  Webkul
     * @package   Webkul_MobikulMp
     * @author    Webkul
     * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
     * @license   https://store.webkul.com/license.html
     */

namespace Webkul\MobikulMp\Controller\Chat;

use Magento\Framework\App\Action\Context;
use Webkul\Mobikul\Helper\Data as HelperData;
use Webkul\Mobikul\Helper\Catalog as HelperCatalog;
use Magento\Store\Model\App\Emulation;

/**
 * MpMobikul API chat controller.
 */
class SellerList extends \Webkul\Mobikul\Controller\ApiController
{
    /**
     * $_customerFactory.
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * $_dir.
     *
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_dir;
    protected $_seller;
    protected $_emulate;
    protected $_baseDir;
    protected $_deviceToken;
    protected $_helperCatalog;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        HelperData $helper,
        HelperCatalog $helperCatalog,
        Emulation $emulate,
        \Magento\Framework\Filesystem\DirectoryList $baseDir,
        \Webkul\Mobikul\Model\DeviceTokenFactory $deviceToken,
        \Webkul\Marketplace\Model\SellerFactory $seller,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_helperCatalog = $helperCatalog;
        $this->_emulate = $emulate;
        $this->_baseDir = $baseDir;
        $this->_deviceToken = $deviceToken;
        $this->_seller = $seller;
        $this->_customerFactory = $customerFactory;
        parent::__construct($helper, $context);
    }

    /**
     * execute notify admin.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->getPost()) {
            $wholeData = $this->getRequest()->getPost();
            $returnArray = [];
            $returnArray['success'] = false;
            $authKey     = $this->getRequest()->getHeader("authKey");
            $apiKey      = $this->getRequest()->getHeader("apiKey");
            $apiPassword = $this->getRequest()->getHeader("apiPassword");
            $authData    = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
            if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                $returnArray["authKey"]      = $authData["authKey"];
                $returnArray["responseCode"] = $authData["responseCode"];
                $width         = $wholeData["width"]         ?? 1000;
                $websiteId     = $wholeData['websiteId']     ?? 0;
                $storeId       = $wholeData['storeId']       ?? 0;
                $mFactor       = $wholeData['mFactor']       ?? 1;
                $sellerId      = $wholeData["customerId"]    ?? '';
                try {
                    $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                    $adminEmail = $this->_helper->getConfigData('mobikulmp/admin/email');
                    $width    = $width*$mFactor;
                    $height   = ($width/2)*$mFactor;
                    if ($adminEmail) {
                        $customer = $this->_customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($adminEmail);

                        $customerIdNotToBeIncluded = [];
                        $customerIdNotToBeIncluded[] = 0;
                        $customerIdNotToBeIncluded[] = $customer->getId();

                        $androidTokenCollection = $this->_deviceToken
                        ->create()
                        ->getCollection()
                        ->addFieldToFilter('customer_id', array('nin' => $customerIdNotToBeIncluded));
                        $logoArray = [];
                        $sellerCollection = $this->_seller
                        ->create()
                        ->getCollection()
                        ->addFieldToSelect('is_seller')
                        ->addFieldToSelect('seller_id')
                        ->addFieldToFilter('is_seller', 1);
                        $sellerIdArray = [];
                        foreach ($sellerCollection as $value) {
                            $sellerIdArray[] = $value->getSellerId();
                            $logoArray[$value->getSellerId()] = $value->getLogoPic()!=''?$value->getLogoPic():"noimage.png";
                        }
                        $this->_helper->printLog('MpMobikul seller list: ', (array) $sellerIdArray);
                        $sellerList = [];
                        foreach ($androidTokenCollection as $token) {
                            if (!in_array($token->getCustomerId(), $sellerIdArray)) {
                                continue;
                            }
                            $eachSeller = [];
                            $isExist = 0;
                            foreach ($sellerList as $key => $value) {
                                if ($value['customerId'] == $token->getCustomerId()) {
                                    $sellerList[$key]['token'] = $value['token'].','.$token->getToken();
                                    $isExist = 1;
                                    break;
                                }
                            }
                            if ($isExist == 0) {
                                $eachSeller['customerId']    = $token->getCustomerId();
                                $eachSeller['token'] = $token->getToken();
                                $collection = $this->_customerFactory->create()
                                ->getCollection()
                                ->addAttributeToSelect('firstname')
                                ->addAttributeToSelect('lastname')
                                ->addAttributeToSelect('entity_id')
                                ->addFieldToFilter('entity_id', $token->getCustomerId());
                                foreach ($collection as $item) {
                                    $eachSeller['name'] = $item->getFirstname().' '.$item->getLastname();
                                    $eachSeller['email'] = $item->getEmail();
                                }
                                $basePath = $this->_baseDir->getPath("media").'/avatar/'.$logoArray[$token->getCustomerId()];
                                $newPath  = $this->_baseDir->getPath("media")."/mobikulresized/avatar/".$width."x".$height."/".$logoArray[$token->getCustomerId()];
                                $this->_helperCatalog->resizeNCache($basePath, $newPath, $width, $height);
                                $eachSeller['profileImage'] = $this->_helper->getUrl("media")."mobikulresized/avatar/".$width."x".$height."/".$logoArray[$token->getCustomerId()];
                                $sellerList[] = $eachSeller;
                            }
                        }
                        $returnArray['apiKey'] = $this->_helper->getConfigData("mobikul/notification/apikey");
                        $returnArray['success'] = true;
                        $returnArray['sellerList'] = $sellerList;
                        $this->_emulate->stopEnvironmentEmulation($environment);
                        return $this->getJsonResponse($returnArray);
                    } else {
                        $returnArray['message'] = ____('Unauthorised Access');
                        $this->_emulate->stopEnvironmentEmulation($environment);
                        return $this->getJsonResponse($returnArray);
                    }
                    $this->_emulate->stopEnvironmentEmulation($environment);
                    return $this->getJsonResponse($returnArray);
                } catch (\Exception $e) {
                    $this->_helper->printLog('MpMobikul Exception log for class: '.get_class($this).' : '.$e->getMessage(), (array) $e->getTrace());
                    $returnArray['message'] = __('Invalid Request.');
                    $this->_emulate->stopEnvironmentEmulation($environment);
                    return $this->getJsonResponse($returnArray);
                }
            } else {
                $returnArray["responseCode"] = $authData["responseCode"];
                $returnArray["message"]      = $authData["message"];
                $this->_helper->log($returnArray, "logResponse", $wholeData);
                return $this->getJsonResponse($returnArray);
            }
        } else {
            $returnArray['message'] = __('Invalid Request.');

            return $this->getJsonResponse($returnArray);
        }
    }
}
