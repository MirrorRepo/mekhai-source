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

    namespace Webkul\Mobikul\Model;
    use Webkul\Mobikul\Api\Data\DeviceTokenInterface;
    use Magento\Framework\DataObject\IdentityInterface;

    class DeviceToken extends \Magento\Framework\Model\AbstractModel implements DeviceTokenInterface, IdentityInterface     {

        const NOROUTE_ENTITY_ID = "no-route";
        const CACHE_TAG         = "mobikul_devicetoken";
        protected $_cacheTag    = "mobikul_devicetoken";
        protected $_eventPrefix = "mobikul_devicetoken";

        protected function _construct()     {
            $this->_init("Webkul\Mobikul\Model\ResourceModel\DeviceToken");
        }

        public function load($id, $field = null)    {
            if ($id === null)
                return $this->noRouteProduct();
            return parent::load($id, $field);
        }

        public function noRouteProduct()    {
            return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
        }

        public function getIdentities()     {
            return [self::CACHE_TAG . "_" . $this->getId()];
        }

        public function getId()     {
            return parent::getData(self::ENTITY_ID);
        }

        public function setId($id)  {
            return $this->setData(self::ENTITY_ID, $id);
        }

        public function getToken()  {
            return parent::getData(self::TOKEN);
        }

        public function setToken($token)    {
            return $this->setData(self::TOKEN, $token);
        }

        public function getCustomerId()  {
            return parent::getData(self::CUSTOMER_ID);
        }

        public function setCustomerId($customerId)    {
            return $this->setData(self::CUSTOMER_ID, $customerId);
        }

    }