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

    interface CategoryimagesInterface   {
        const ID            = "id";
        const ICON          = "icon";
        const BANNER        = "banner";
        const CATEGORY_ID   = "category_id";
        const CATEGORY_NAME = "category_name";
        const CREATED_AT    = "created_at";
        const UPDATED_AT    = "updated_at";

        public function getId();

        public function setId($id);

        public function getIcon();

        public function setIcon($icon);

        public function getBanner();

        public function setBanner($banner);

        public function getCategoryId();

        public function setCategoryId($categoryId);

        public function getCategoryName();

        public function setCategoryName($categoryName);

        public function getCreatedAt();

        public function setCreatedAt($createdAt);

        public function getUpdatedAt();

        public function setUpdatedAt($updatedAt);

    }