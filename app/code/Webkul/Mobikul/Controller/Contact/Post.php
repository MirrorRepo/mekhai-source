<?php
    /**
    * Webkul Software.
    *
    * @category  Webkul
    * @package   Webkul_Mobikul
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Controller\Contact;
    use Magento\Store\Model\App\Emulation;
    use Magento\Framework\App\Action\Context;
    use Webkul\Mobikul\Helper\Data as HelperData;
    use Webkul\Mobikul\Helper\Catalog as HelperCatalog;

    class Post extends \Webkul\Mobikul\Controller\ApiController    {

        protected $_emulate;

        public function __construct(
            Context $context,
            HelperData $helper,
            Emulation $emulate,
            HelperCatalog $helperCatalog
        ) {
            $this->_emulate = $emulate;
            parent::__construct($helper, $context);
        }

        public function execute()   {
            $returnArray                 = [];
            $returnArray["authKey"]      = "";
            $returnArray["success"]      = false;
            $returnArray["message"]      = "";
            $returnArray["responseCode"] = 0;
            try {
                $wholeData       = $this->getRequest()->getPostValue();
                $this->_headers  = $this->getRequest()->getHeaders();
                $this->_helper->log(__CLASS__, "logClass", $wholeData);
                $this->_helper->log($wholeData, "logParams", $wholeData);
                $this->_helper->log($this->_headers, "logHeaders", $wholeData);
                if ($wholeData) {
                    $authKey     = $this->getRequest()->getHeader("authKey");
                    $apiKey      = $this->getRequest()->getHeader("apiKey");
                    $apiPassword = $this->getRequest()->getHeader("apiPassword");
                    $authData    = $this->_helper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $returnArray["authKey"]      = $authData["authKey"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $name        = $this->_helper->validate($wholeData, "name")      ? $wholeData["name"]      : "";
                        $email       = $this->_helper->validate($wholeData, "email")     ? $wholeData["email"]     : "";
                        $comment     = $this->_helper->validate($wholeData, "comment")   ? $wholeData["comment"]   : "";
                        $storeId     = $this->_helper->validate($wholeData, "storeId")   ? $wholeData["storeId"]   : 1;
                        $telephone   = $this->_helper->validate($wholeData, "telephone") ? $wholeData["telephone"] : "";
                        $environment = $this->_emulate->startEnvironmentEmulation($storeId);
                        $this->_objectManager->get("\Magento\Framework\Translate\Inline\StateInterface")->suspend();
                        $postObject = new \Magento\Framework\DataObject();
                        $postObject->setData($wholeData);
                        $error      = false;
                        if (!\Zend_Validate::is(trim($wholeData["name"]), "NotEmpty"))
                            $error = true;
                        if (!\Zend_Validate::is(trim($wholeData["comment"]), "NotEmpty"))
                            $error = true;
                        if (!\Zend_Validate::is(trim($wholeData["email"]), "EmailAddress"))
                            $error = true;
                        if ($error)
                            throw new \Exception();
                        $storeScope  = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                        $scopeConfig = $this->_objectManager->get("\Magento\Framework\App\Config\ScopeConfigInterface");
                        $transport   = $this->_objectManager->get("\Magento\Framework\Mail\Template\TransportBuilder")
                            ->setTemplateIdentifier($scopeConfig->getValue("contact/email/email_template", $storeScope))
                            ->setTemplateOptions([
                                "area"  => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                                "store" => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                            ])
                            ->setTemplateVars(["data"=>$postObject])
                            ->setFrom($scopeConfig->getValue("contact/email/sender_email_identity", $storeScope))
                            ->addTo($scopeConfig->getValue("contact/email/recipient_email", $storeScope))
                            ->setReplyTo($email)
                            ->getTransport();
                        $transport->sendMessage();
                        $this->_objectManager->get("\Magento\Framework\Translate\Inline\StateInterface")->resume();
                        $returnArray["message"] = __("Thanks for contacting us with your comments and questions. We'll respond to you very soon.");
                        $returnArray["success"] = true;
                        $this->_emulate->stopEnvironmentEmulation($environment);
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    } else {
                        $returnArray["message"]      = $authData["message"];
                        $returnArray["responseCode"] = $authData["responseCode"];
                        $this->_helper->log($returnArray, "logResponse", $wholeData);
                        return $this->getJsonResponse($returnArray);
                    }
                } else {
                    $returnArray["message"]      = __("Invalid Request");
                    $returnArray["responseCode"] = 0;
                    $this->_helper->log($returnArray, "logResponse", $wholeData);
                    return $this->getJsonResponse($returnArray);
                }
            } catch (\Exception $e) {
                $this->_objectManager->get("\Magento\Framework\Translate\Inline\StateInterface")->resume();
                $returnArray["message"] = __("We can't process your request right now. Sorry, that's all we know.");
                $this->_helper->printLog($returnArray, 1);
                return $this->getJsonResponse($returnArray);
            }
        }

    }