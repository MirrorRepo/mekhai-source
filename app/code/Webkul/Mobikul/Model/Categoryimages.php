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
    use Webkul\Mobikul\Api\Data\CategoryimagesInterface;
    use Magento\Framework\DataObject\IdentityInterface;
    use Magento\Framework\Model\AbstractModel;

    class Categoryimages extends AbstractModel implements CategoryimagesInterface, IdentityInterface    {

        const NOROUTE_ID        = "no-route";
        const CACHE_TAG         = "mobikul_categoryimages";
        protected $_cacheTag    = "mobikul_categoryimages";
        protected $_eventPrefix = "mobikul_categoryimages";

        protected function _construct()     {
            $this->_init("Webkul\Mobikul\Model\ResourceModel\Categoryimages");
        }

        public function load($id, $field = null)    {
            if ($id === null)
                return $this->noRouteCategoryimages();
            return parent::load($id, $field);
        }

        public function noRouteCategoryimages()     {
            return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
        }

        public function getIdentities()     {
            return [self::CACHE_TAG . "_" . $this->getId()];
        }

        public function getId()     {
            return parent::getData(self::ID);
        }

        public function setId($id)  {
            return $this->setData(self::ID, $id);
        }

        public function getIcon()   {
            return parent::getData(self::ICON);
        }


        public function setIcon($icon)  {
            return $this->setData(self::ICON, $icon);
        }

        public function getBanner()     {
            return parent::getData(self::BANNER);
        }

        public function setBanner($banner)  {
            return $this->setData(self::BANNER, $banner);
        }

        public function getCategoryId()     {
            return parent::getData(self::CATEGORY_ID);
        }

        public function setCategoryId($categoryId)  {
            return $this->setData(self::CATEGORY_ID, $categoryId);
        }

        public function getCategoryName()   {
            return parent::getData(self::CATEGORY_NAME);
        }

        public function setCategoryName($categoryName)  {
            return $this->setData(self::CATEGORY_NAME, $categoryName);
        }

        public function getCreatedAt()  {
            return parent::getData(self::CREATED_AT);
        }

        public function setCreatedAt($createdAt)    {
            return $this->setData(self::CREATED_AT, $createdAt);
        }

        public function getUpdatedAt()  {
            return parent::getData(self::UPDATED_AT);
        }

        public function setUpdatedAt($updatedAt)    {
            return $this->setData(self::UPDATED_AT, $updatedAt);
        }

    }