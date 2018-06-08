<?php
/**
 * @category   Webkul
 * @package    Webkul_MpFavouriteSeller
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpFavouriteSeller\Controller\Favouriteseller;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpFavouriteSeller\Helper\Data;
use Webkul\MpFavouriteSeller\Model\Mpfavouriteseller;
use Webkul\MpFavouriteSeller\Model\MpfavouritesellerRepository;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * AddFavouriteSeller class
 */
class AddFavouriteSeller extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Webkul\SocialSignup\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var Webkul\MpFavouriteSeller\Model\MpfavouritesellerRepository
     */
    protected $_mpFavouritesellerRepository;

    /**
     * @var Webkul\MpFavouriteSeller\Model\Mpfavouriteseller
     */
    protected $_mpFavouritesellerModel;

    /**
     * @param Context                                     $context
     * @param Data                                        $dataHelper
     * @param Mpfavouriteseller                           $mpFavouritesellerModel
     * @param MpfavouritesellerRepository                 $mpFavouritesellerRepository
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param PageFactory                                 $resultPageFactory
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        Mpfavouriteseller $mpFavouritesellerModel,
        MpfavouritesellerRepository $mpFavouritesellerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PageFactory $resultPageFactory,
        TimezoneInterface $localeDate
    ) {
    
        $this->_mpFavouritesellerModel = $mpFavouritesellerModel;
        $this->_mpFavouritesellerRepository = $mpFavouritesellerRepository;
        $this->_dataHelper = $dataHelper;
        $this->_date = $date;
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->customerModel = $customerModel;
        $this->mpHelper = $mpHelper;
        $this->_storeManager=$storeManager;
        $this->localeDate = $localeDate;
        parent::__construct($context);
    }

    /**
     * AddFavouriteSeller
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $time = $this->localeDate->date()->format('Y-m-d H:i:s');
        $sellerId = $this->getRequest()->getParam('seller_id');
        $customerId = $this->_dataHelper->getCurrentCustomer();
        $favouritesCollection = $this->_mpFavouritesellerRepository
        ->getSellerCollectionByCustomerId($sellerId, $customerId);
        if (!$favouritesCollection->getSize()) {
            $favouriteSellerModel = $this->_mpFavouritesellerModel;
            $favouriteSellerModel->setSellerId($sellerId);
            $favouriteSellerModel->setCustomerId($customerId);
            $favouriteSellerModel->setLikedAt($time);
            $autoId = $favouriteSellerModel->save()->getEntityId();
            $this->messageManager->addSuccess(
                __('Successfully saved in your favourite seller list.')
            );
            $this->_sendMailToSeller($sellerId);
            $this->_sendMailToCustomer($sellerId);
        } else {
             $this->messageManager->addNotice(
                 __('Already added to your favourite seller list.')
             );
        }
        return $resultRedirect->setPath('mpfavouriteseller/favouriteseller/index');
    }

    /**
     * send mail to seller
     *
     * @return null
     */
    private function _sendMailToSeller($sellerId)
    {
        $customer = $this->_customerSession->getCustomer();
        $seller = $this->customerModel->load($sellerId);
        $adminStoremail = $this->mpHelper->getAdminEmailId();
        $defaultTransEmailId = $this->mpHelper->getDefaultTransEmailId();
        $adminEmail = $adminStoremail ? $adminStoremail : $defaultTransEmailId;
        $adminUsername = __('Admin');

        $senderInfo = [];
        $receiverInfo = [];

        $receiverInfo = [
            'name' => $seller->getName(),
            'email' => $seller->getEmail(),
        ];
        $senderInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        // print_r($receiverInfo);
        // print_r($senderInfo);die;
        $emailTempVariables = [];
        $emailTempVariables['myvar1'] = $seller->getName();
        $emailTempVariables['myvar2'] = $customer->getName();
        $emailTempVariables['myvar3'] = $this->_storeManager->getStore()->getBaseUrl().'customer/account/login';
        $emailTempVariables['myvar4'] = __('Followed by customer');

        $this->_dataHelper->notifySeller(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo
        );
    }

    /**
     * mail send to customer
     *
     * @return null
    */
    public function _sendMailToCustomer($sellerId)
    {
        $customer = $this->_customerSession->getCustomer();
        $seller = $this->customerModel->load($sellerId);
        $sellerMp = $this->mpHelper->getSellerDataBySellerId($sellerId);
        $shopUrl = $sellerMp->getFirstItem()->getShopUrl();
        $profileUrl = $this->mpHelper->getRewriteUrl('marketplace/seller/profile/shop/'.$shopUrl);

        $senderInfo = [];
        $receiverInfo = [];

        $receiverInfo = [
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
        ];
        $senderInfo = [
            'name' => $seller->getName(),
            'email' => $seller->getEmail(),
        ];
        $emailTempVariables = [];
        $emailTempVariables['myvar1'] = $customer->getName();
        $emailTempVariables['myvar2'] = $profileUrl;
        $emailTempVariables['myvar3'] = $shopUrl;
        $emailTempVariables['myvar4'] = __('Confirmation');

        $this->_dataHelper->notifyCustomer(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo
        );
    }
}
