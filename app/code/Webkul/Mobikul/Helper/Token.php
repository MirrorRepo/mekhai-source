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
    use Magento\Framework\Stdlib\DateTime\DateTime;

    class Token extends \Magento\Framework\App\Helper\AbstractHelper    {

        protected $_date;
        protected $_storeManager;
        protected $_objectManager;
        protected $_deviceToken;

        public function __construct(
            \Magento\Framework\App\Helper\Context $context,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\ObjectManagerInterface $objectManager,
            \Webkul\Mobikul\Model\DeviceTokenFactory $deviceTokenFactory
        ) {
            $this->_storeManager  = $storeManager;
            $this->_objectManager = $objectManager;
            $this->_deviceToken   = $deviceTokenFactory;
            parent::__construct($context);
        }

        public function saveToken($customerId, $token)    {
            try {
                $deviceTokenModel = $this->_deviceToken->create();
                if ($customerId != "" && $token != "") {
                    $collection = $deviceTokenModel->getCollection()->addFieldToFilter("token", $token);
                    if ($collection->getSize() > 0) {
                        foreach ($collection as $eachRow) {
                            $this->_deviceToken->create()->load($eachRow->getId())->setCustomerId($customerId)->save();
                            return $eachRow->getId();
                        }
                    } else {
                        return $this->_deviceToken->create()->setToken($token)->setCustomerId($customerId)->save()->getId();
                    }
                }
                if ($customerId == "" && $token != "") {
                    $collection = $deviceTokenModel->getCollection()->addFieldToFilter("token", $token);
                    if ($collection->getSize() > 0) {
                        foreach ($collection as $eachRow) {
                            $this->_deviceToken->create()->load($eachRow->getId())->setCustomerId($customerId)->save();
                            return $eachRow->getId();
                        }
                    } else {
                        return $this->_deviceToken->create()->setToken($token)->setCustomerId($customerId)->save()->getId();
                    }
                }
            } catch (\Exception $e) {
                $this->_objectManager->get("\Webkul\Mobikul\Helper\Data")->printLog($e->getMessage(), 1);
            }
        }

    }