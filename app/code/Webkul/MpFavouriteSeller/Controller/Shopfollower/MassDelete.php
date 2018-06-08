<?php
/**
 * @category   Webkul
 * @package    Webkul_MpFavouriteSeller
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpFavouriteSeller\Controller\Shopfollower;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpFavouriteSeller\Model\MpfavouritesellerRepository;

/**
 * MassDelete class
 */
class MassDelete extends \Magento\Customer\Controller\AbstractAccount
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
     * @var Webkul\MpFavouriteSeller\Model\MpfavouritesellerRepository
     */
    protected $_mpFavouritesellerRepository;

    /**
     * @param Context                         $context
     * @param MpfavouritesellerRepository     $mpFavouritesellerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param PageFactory                     $resultPageFactory
     */
    public function __construct(
        Context $context,
        MpfavouritesellerRepository $mpFavouritesellerRepository,
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory,
        \Webkul\MpFavouriteSeller\Helper\Data $helper
    ) {
    
        $this->_mpFavouritesellerRepository = $mpFavouritesellerRepository;
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * MassDelete
     */
    public function execute()
    {
        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $resultRedirect = $this->resultRedirectFactory->create();
            if ($this->getRequest()->isPost()) {
                $customerIds = $this->getRequest()->getParam('customer_mass_delete');
                $sellerId = $this->helper->getCurrentCustomer();
    
                foreach ($customerIds as $customerId) {
                    $followerCollection = $this->_mpFavouritesellerRepository
                                        ->getSellerCollectionByCustomerId($sellerId, $customerId);
    
                    foreach ($followerCollection as $follower) {
                        $this->_delFollower($follower);
                    }
                }
            }
            $this->messageManager->addSuccess(
                __(
                    'Successfully followers removed'
                )
            );
            return $resultRedirect->setPath('mpfavouriteseller/shopfollower/index');
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }


        
        
    }

    /**
     * delete follower
     * @param  object $thisObject
     * @return $this
     */
    private function _delFollower($thisObject)
    {
        $thisObject->delete()->save();
    }
}
