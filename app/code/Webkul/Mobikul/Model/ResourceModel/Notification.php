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

    class Notification extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb     {

        protected $_store = null;

        protected function _construct()     {
            $this->_init("mobikul_notification", "id");
        }

        public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)     {
            if (!is_numeric($value) && $field === null)
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