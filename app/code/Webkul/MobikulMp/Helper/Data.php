<?php
    /**
    * Webkul Software.
    *
    * @category  Webkul
    * @package   Webkul_MobikulMp
    * @author    Webkul
    * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
    * @license   https://store.webkul.com/license.html
    */

    namespace Webkul\MobikulMp\Helper;

    class Data extends \Magento\Framework\App\Helper\AbstractHelper     {

        protected $_seller;
        protected $_storeManager;
        protected $_objectManager;

        public function __construct(
            \Webkul\Marketplace\Model\Seller $seller,
            \Magento\Framework\App\Helper\Context $context,
            \Magento\Framework\ObjectManagerInterface $objectManager,
            \Magento\Store\Model\StoreManagerInterface $storeManager
        ) {
            $this->_seller        = $seller;
            $this->_storeManager  = $storeManager;
            $this->_objectManager = $objectManager;
            parent::__construct($context);
        }

        public function isSeller($customerId)  {
            $sellerStatus = 0;
            $model = $this->getSellerCollectionObj($customerId);
            foreach ($model as $value) {
                $sellerStatus = $value->getIsSeller();
            }
            return $sellerStatus;
        }

        public function getSellerCollectionObj($customerId)   {
            $model = $this->_seller->getCollection()
                ->addFieldToFilter("seller_id", $customerId)
                ->addFieldToFilter("store_id", $this->_storeManager->getStore()->getStoreId());
// If seller data doesn't exist for current store ///////////////////////////////////////////////////////////////////////////////
            if (!count($model)) {
                $model = $this->_seller->getCollection()
                    ->addFieldToFilter("seller_id", $customerId)
                    ->addFieldToFilter("store_id", 0);
            }
            return $model;
        }

    }