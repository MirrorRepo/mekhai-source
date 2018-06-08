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

    interface FeaturedcategoriesInterface   {
        const ID           = "id";
        const FILENAME     = "filename";
        const CATEGORY_ID  = "category_id";
        const STORE_ID     = "store_id";
        const SORT_ORDER   = "sort_order";
        const STATUS       = "status";
        const CREATED_TIME = "created_time";
        const UPDATE_TIME  = "update_time";

        public function getId();

        public function setId($id);

        public function getFilename();

        public function setFilename($filename);

        public function getCategoryId();

        public function setCategoryId($categoryId);

        public function getStoreId();

        public function setStoreId($storeId);

        public function getSortOrder();

        public function setSortOrder($sortOrder);

        public function getStatus();

        public function setStatus($status);

        public function getCreatedTime();

        public function setCreatedTime($createdAt);

        public function getUpdateTime();

        public function setUpdateTime($updatedAt);

    }