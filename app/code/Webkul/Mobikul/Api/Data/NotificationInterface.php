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

    interface NotificationInterface     {

        const ID           = "id";
        const FILENAME     = "filename";
        const TITLE        = "title";
        const CONTENT      = "content";
        const TYPE         = "type";
        const PRO_CAT_ID   = "pro_cat_id";
        const STORE_ID     = "store_id";
        const STATUS       = "status";
        const SORT_ORDER   = "sort_order";
        const CREATED_TIME = "created_time";
        const UPDATE_TIME  = "update_time";

        public function getId();

        public function setId($id);

        public function getFilename();

        public function setFilename($filename);

        public function getTitle();

        public function setTitle($title);

        public function getContent();

        public function setContent($content);

        public function getType();

        public function setType($type);

        public function getProCatId();

        public function setProCatId($proCatId);

        public function getStoreId();

        public function setStoreId($storeId);

        public function getStatus();

        public function setStatus($status);

        public function getSortOrder();

        public function setSortOrder($sortOrder);

        public function getCreatedTime();

        public function setCreatedTime($createdAt);

        public function getUpdateTime();

        public function setUpdateTime($updatedAt);

    }