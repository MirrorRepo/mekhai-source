<?php
    /**
    * Webkul Software.
    *
    * @category Webkul
    *
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\Mobikul\Helper;

    class Data extends \Magento\Framework\App\Helper\AbstractHelper     {

        protected $_encrypted;
        protected $_storeManager;
        protected $_sessionManager;

        public function __construct(
            \Magento\Framework\App\Helper\Context $context,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Config\Model\Config\Backend\Encrypted $encrypted,
            \Magento\Framework\Session\SessionManagerInterface $sessionManager
        ) {
            $this->_encrypted      = $encrypted;
            $this->_storeManager   = $storeManager;
            $this->_sessionManager = $sessionManager;
            parent::__construct($context);
        }

        public function getPassword()   {
            return $this->_encrypted->processValue(
                $this->scopeConfig->getValue("mobikul/configuration/apikey", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        public function getUrl($dir)    {
            return $this->_storeManager->getStore()->getBaseUrl($dir);
        }

        public function isAuthorized($authKey, $apiKey, $apiPassword)   {
            $returnArray                 = [];
            $returnArray["authKey"]      = $authKey;
            $returnArray["message"]      = "";
            $returnArray["responseCode"] = 0;
            $currentSessionId            = $this->_sessionManager->getSessionId();
            if ($authKey == $currentSessionId)
                $returnArray["responseCode"] = 1;
            else {
                $configUserName = $this->getConfigData("mobikul/configuration/apiusername");
                $configApiKey   = $this->getPassword();
                if (($apiKey == $configUserName) && ($apiPassword == $configApiKey)) {
                    $newSessionId                = $this->_sessionManager->getSessionId();
                    $returnArray["authKey"]      = $newSessionId;
                    $returnArray["responseCode"] = 2;
                } else {
                    $returnArray["message"]      = __("Unable to Authorize User.");
                    $returnArray["responseCode"] = 3;
                }
            }
            return $returnArray;
        }

        public function log($data, $key, $wholeData) {
            $flag = $this->validate($wholeData, $key) ? $wholeData[$key] : 0;
            $this->printLog($data, $flag);
        }

        public function printLog($data, $flag=1, $filename="mobikul.log"){
            if($flag == 1)  {
                $logger        = new \Zend\Log\Logger();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $path          = $objectManager->get("\Magento\Framework\Filesystem\DirectoryList")->getPath("var");
                if (!file_exists($path."/log/"))
                    mkdir($path."/log/", 0777, true);
                $logger->addWriter(new \Zend\Log\Writer\Stream($path."/log/".$filename));
                if (is_array($data) || is_object($data))
                    $data = print_r($data, true);
                $logger->info($data);
            }
        }

        public function validate($wholeData, $key)    {
            if(isset($wholeData[$key]) && $wholeData[$key] != "")
                return true;
            else
                return false;
        }

        public function getConfigData($path, $scope=\Magento\Store\Model\ScopeInterface::SCOPE_STORE) {
            return $this->scopeConfig->getValue($path, $scope);
        }

        public function canReorder(\Magento\Sales\Model\Order $order)   {
            if (!$this->getConfigData("sales/reorder/allow"))
                return 0;
            if (1)
                return $order->canReorder();
            else
                return 1;
        }

    }