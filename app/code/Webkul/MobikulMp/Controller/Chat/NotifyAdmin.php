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
class NotifyAdmin extends \Webkul\Mobikul\Controller\ApiController
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
    protected $_emulate;
    protected $_deviceToken;
    

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        HelperData $helper,
        HelperCatalog $helperCatalog,
        Emulation $emulate,
        \Webkul\Mobikul\Model\DeviceTokenFactory $deviceToken,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_emulate = $emulate;
        $this->_customerFactory = $customerFactory;
        $this->_deviceToken = $deviceToken;
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
            $returnArray = [];
            $returnArray['success'] = false;
            $wholeData   = $this->getRequest()->getPost();
            $authKey     = $this->getRequest()->getHeader("authKey");
            $apiKey      = $this->getRequest()->getHeader("apiKey");
            $apiPassword = $this->getRequest()->getHeader("apiPassword");
            $authData    = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
            if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                $returnArray["authKey"]      = $authData["authKey"];
                $returnArray["responseCode"] = $authData["responseCode"];
                $sellerMessage = $wholeData['message']       ?? '';
                $websiteId     = $wholeData['websiteId']     ?? '';
                $sellerName    = $wholeData['sellerName']    ?? '';
                $storeId       = $wholeData['storeId']       ?? '';
                $sellerId      = $wholeData["customerId"]    ?? 0;
                try {
                    $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                    $adminEmail = $this->_helper->getConfigData('mobikulmp/admin/email');

                    $customer = $this->_customerFactory->create()->setWebsiteId($websiteId)->loadByEmail($adminEmail);
                    $androidTokenCollection = $this->_deviceToken
                    ->create()
                    ->getCollection()
                    ->addFieldToFilter('customer_id', $customer->getId());

                    foreach ($androidTokenCollection as $token) {
                        $sellerTokenCollection = $this->_deviceToken
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter("customer_id", $sellerId);
                            $sellerTokens = [];
                            foreach ($sellerTokenCollection as $each)
                                $sellerTokens[] = $each->getToken();
                        $message = [
                            "id"               => $sellerId,
                            "name"             => $sellerName,
                            "body"             => $sellerMessage,
                            "sound"            => "default",
                            "title"            => "New Message from ".$sellerName,
                            "apiKey"           => $this->_helper->getConfigData("mobikul/notification/apikey"),
                            "tokens"           => implode(",", $sellerTokens),
                            "message"          => $sellerMessage,
                            "notificationType" => "chatNotification"
                        ];
                        $url = 'https://fcm.googleapis.com/fcm/send';
                        $authKey = $this->_helper->getConfigData("mobikul/notification/apikey");
                        $headers = [
                            'Authorization: key='.$authKey,
                            'Content-Type: application/json',
                        ];
                        $error = 0;
                        $errorMsg = [];
                        $fields = [
                            "to"                => $token->getToken(),
                            "data"              => $message,
                            "priority"          => "high",
                            "content_available" => true,
                            "time_to_live"      => 30,
                            "delay_while_idle"  => true
                        ];
                        if($token->getOs() == "ios")
                            $fields["notification"] = $message;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                        $result = curl_exec($ch);
                        curl_close($ch);
                        if($this->isJson($result)){
                            $result = json_decode($result, true);
                            if ($result["success"] == 0 && $result["failure"] == 1) {
                                $token->delete();
                            }
                        }
                    }
                    $returnArray['success'] = true;
                    $this->_emulate->stopEnvironmentEmulation($environment);
                    return $this->getJsonResponse($returnArray);
                } catch (\Exception $e) {
                    $this->createLog('MpMobikul Exception log for class: '.get_class($this).' : '.$e->getMessage(), (array) $e->getTrace());
                    $returnArray['success'] = 0;
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
            $returnArray['success'] = 0;
            $returnArray['message'] = __('Invalid Request.');

            return $this->getJsonResponse($returnArray);
        }
    }

    public function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
