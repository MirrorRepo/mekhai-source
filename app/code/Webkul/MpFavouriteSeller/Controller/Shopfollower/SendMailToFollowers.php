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
use Webkul\MpFavouriteSeller\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * SendMailToFollowers class
 */
class SendMailToFollowers extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var Webkul\MpFavouriteSeller\Helper\Data
     */
    private $helperData;

    /**
     * @var Webkul\MpFavouriteSeller\Model\MpfavouritesellerRepository
     */
    private $mpFavouritesellerRepository;

    /**
     * @var Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context                         $context
     * @param MpfavouritesellerRepository     $mpFavouritesellerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Data                            $helperData
     * @param PageFactory                     $resultPageFactory
     */
    public function __construct(
        Context $context,
        MpfavouritesellerRepository $mpFavouritesellerRepository,
        \Magento\Customer\Model\Session $customerSession,
        Data $helperData,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    ) {
    
        $this->mpFavouritesellerRepository = $mpFavouritesellerRepository;
        $this->customerSession = $customerSession;
        $this->helperData = $helperData;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * SendMailToFollowers class
     */
    public function execute()
    {
        $fields = $this->getRequest()->getParams();
        $sellerCollection = $this->helperData->getCustomerData($fields['seller-id']);
        $email = $sellerCollection->getEmail();
        $name = $sellerCollection->getName();

        $followersCollection = $this->mpFavouritesellerRepository
            ->getCustomersCollectionBySellerId($fields['seller-id']);

        foreach ($followersCollection as $follower) {
            $customer = $this->helperData->getCustomerData($follower->getCustomerId());
            $senderInfo = [];
            $receiverInfo = [];

            $receiverInfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ];
            $senderInfo = [
                'name' => $name,
                'email' => $email,
            ];
            $emailTempVariables = [];
            $emailTempVariables['myvar1'] = $customer->getName();
            $emailTempVariables['myvar2'] = $fields['message'];
            $emailTempVariables['myvar3'] = $fields['subject'];

            $result = $this->helperData->notifyFollowers(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
        }
        return $this->resultJsonFactory->create()->setData($result);
    }
}
