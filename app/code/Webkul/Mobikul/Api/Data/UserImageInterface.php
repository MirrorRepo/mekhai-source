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

    interface UserImageInterface    {

        const CREATED_TIME = "created_at";
        const ENTITY_ID    = "id";
        const PROFILE      = "profile";
        const BANNER       = "banner";
        const CUSTOMER_ID  = "customer_id";
        const IS_SOCIAL    = "is_social";

        public function getId();

        public function setId($id);

        public function getCreatedTime();

        public function setCreatedTime($createdAt);

        public function getProfile();

        public function setProfile($profile);

        public function getBanner();

        public function setBanner($banner);

        public function getCustomerId();

        public function setCustomerId($customerId);

        public function getIsSocial();

        public function setIsSocial($isSocial);

    }