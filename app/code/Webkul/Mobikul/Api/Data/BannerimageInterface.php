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

    interface BannerimageInterface      {

        const ID         = "id";
        const FILENAME   = "filename";
        const STATUS     = "status";
        const TYPE       = "type";
        const PRO_CAT_ID = "pro_cat_id";
        const STORE_ID   = "store_id";
        const SORT_ORDER = "sort_order";

        public function getId();

        public function setId($id);

        public function getFilename();

        public function setFilename($filename);

        public function getStatus();

        public function setStatus($status);

        public function getType();

        public function setType($type);

        public function getProCatId();

        public function setProCatId($proCatId);

        public function getStoreId();

        public function setStoreId($storeId);

        public function getSortOrder();

        public function setSortOrder($sortOrder);

        public function getCreatedTime();

        public function setCreatedTime($createdAt);

        public function getUpdateTime();

        public function setUpdateTime($updatedAt);

    }