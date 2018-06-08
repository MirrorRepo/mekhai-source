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

    namespace Webkul\Mobikul\Model\ResourceModel;

    class DeviceToken extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb  {

        protected $_store = null;

        public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context, $connectionName = null) {
            parent::__construct($context, $connectionName);
        }

        protected function _construct()     {
            $this->_init("mobikul_devicetoken", "id");
        }

        public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null){
            if (!is_numeric($value) && is_null($field))
                $field = "identifier";
            return parent::load($object, $value, $field);
        }

        public function setStore($store)    {
            $this->_store = $store;
            return $this;
        }

        public function getStore()  {
            return $this->_storeManager->getStore($this->_store);
        }

    }