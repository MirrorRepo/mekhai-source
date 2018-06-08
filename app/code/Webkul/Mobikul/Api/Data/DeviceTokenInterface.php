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
    namespace Webkul\Mobikul\Api\Data;

    interface DeviceTokenInterface  {
        const ENTITY_ID    = "id";
        const TOKEN        = "token";
        const CUSTOMER_ID  = "customer_id";

        public function getId();

        public function setId($id);

        // public function getCreatedTime();

        // public function setCreatedTime($createdAt);

        // public function getUpdatedTime();

        // public function setUpdatedTime($updatedAt);

        public function getToken();

        public function setToken($token);

        public function getCustomerId();

        public function setCustomerId($customerId);
    }