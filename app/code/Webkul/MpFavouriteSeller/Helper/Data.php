<?php
/**
 * @category   Webkul
 * @package    Webkul_MpFavouriteSeller
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */ 
namespace Webkul\MpFavouriteSeller\Helper;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\Customer\Model\Customer;

/**
 * MpFavouriteSeller data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_EMAIL_PRODUCT_NOTIFY_CUSTOMERS = 'mpfavouriteseller/email/new_product_notify_follower_template';
    const XML_PATH_EMAIL_NOTIFY_CUSTOMERS = 'mpfavouriteseller/email/email_follower_template';
    const XML_PATH_EMAIL_FOLLOWED_MAIL_TO_SELLER = 'mpfavouriteseller/email/followed_mail_to_seller_template';
    const XML_PATH_EMAIL_FOLLOWED_MAIL_TO_CUSTOMER = 'mpfavouriteseller/email/followed_mail_to_customer_template';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    private $mpHelper;

    /**
     * @var Magento\Customer\Model\Customer
     */
    private $customerModel;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;
    
    private $template;

    private $messageManager;

    /**
     * @param \Magento\Framework\App\Helper\Context              $context
     * @param \Magento\Framework\ObjectManagerInterface          $objectManager
     * @param \Magento\Customer\Model\Session                    $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param MpHelper                                           $mpHelper
     * @param Customer                                           $customerModel
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        MpHelper $mpHelper,
        Customer $customerModel,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
    
        $this->objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->mpHelper = $mpHelper;
        $this->customerModel = $customerModel;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->messageManager = $messageManager;
    }

    /**
     * check is seller or not
     * @return int
     */
    public function checkIsSeller()
    {
        return $this->mpHelper->isSeller();
    }

    /**
     * get Customer Data
     * @return string
     */
    public function getCustomerData($customerId)
    {
        return $this->customerModel->load($customerId);
    }

    /**
     * get current customer
     * @return string
     */
    public function getCurrentCustomer()
    {
        return $this->mpHelper->getCustomerId();
    }   

    /**
     * notify all followers
     * @return string
     */
    public function notifyFollowers($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $error = false;$msg = null;
        $this->template = $this->getTemplateId(self::XML_PATH_EMAIL_NOTIFY_CUSTOMERS);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $error = true;
            $msg = $e->getMessage();
        }
        $this->inlineTranslation->resume();
        $result = ['error'=>$error,'msg'=>$msg];
        return $result;
    }

    /**
     * notify to seller
     * @return string
     */
    public function notifySeller($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->template = $this->getTemplateId(self::XML_PATH_EMAIL_FOLLOWED_MAIL_TO_SELLER);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }

    /**
     * notify to customer
     * @return string
     */
    public function notifyCustomer($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->template = $this->getTemplateId(self::XML_PATH_EMAIL_FOLLOWED_MAIL_TO_CUSTOMER);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }

    /**
     * [followersNotifyProductApprove description]
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     */
    public function followersNotifyProductApprove($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->template = $this->getTemplateId(self::XML_PATH_EMAIL_PRODUCT_NOTIFY_CUSTOMERS);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }

    /**
     * [generateTemplate description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->transportBuilder
                ->setTemplateIdentifier($this->template)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo['email'], $receiverInfo['name']);

        return $this;
    }

    /**
     * Return store.
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Return template id.
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

   /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
