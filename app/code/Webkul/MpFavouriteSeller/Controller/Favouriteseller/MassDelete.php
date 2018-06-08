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
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost()) {
            $sellerIds = $this->getRequest()->getParam('seller_mass_delete');
            $customerId = $this->helper->getCurrentCustomer();
            foreach ($sellerIds as $sellerId) {
                $favouritesCollection = $this->_mpFavouritesellerRepository
                                    ->getSellerCollectionByCustomerId($sellerId, $customerId);

                foreach ($favouritesCollection as $favSeller) {
                    $this->_delSeller($favSeller);
                }
            }
        }
        $this->messageManager->addSuccess(
            __(
                'Sellers have been successfully removed.'
            )
        );
        return $resultRedirect->setPath('mpfavouriteseller/favouriteseller/index');
    }

    /**
     * get favourite seller
     * @param  object $thisObject
     * @return this
     */
    private function _delSeller($thisObject)
    {
        $thisObject->delete()->save();
    }
}
