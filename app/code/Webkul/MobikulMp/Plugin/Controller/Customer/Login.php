<?php

    namespace Webkul\MobikulMp\Plugin\Controller\Customer;
    use \Magento\Framework\Controller\ResultFactory;

    class Login     {

        protected $_helper;
        protected $_seller;
        protected $_request;
        protected $_jsonHelper;
        protected $_resultFactory;

        public function __construct(
            \Webkul\Mobikul\Helper\Data $helper,
            \Webkul\Marketplace\Model\Seller $seller,
            \Magento\Framework\App\Request\Http $request,
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Magento\Framework\Controller\ResultFactory $resultFactory
        ) {
            $this->_helper        = $helper;
            $this->_seller        = $seller;
            $this->_request       = $request;
            $this->_jsonHelper    = $jsonHelper;
            $this->_resultFactory = $resultFactory;
        }

        public function afterExecute(\Webkul\Mobikul\Controller\Customer\Login $subject, $response)     {
            $returnArray = $this->_jsonHelper->jsonDecode($response->getRawData());
            $returnArray["isAdmin"]   = false;
            $returnArray["isSeller"]  = false;
            $returnArray["isPending"] = false;
            $wholeData = $this->_request->getPostValue();
            $storeId   = $this->_helper->validate($wholeData, "storeId")  ? $wholeData["storeId"]  : 0;
            $username  = $this->_helper->validate($wholeData, "username") ? $wholeData["username"] : "";
            if($username == $this->_helper->getConfigData("mobikulmp/admin/email"))
                $returnArray["isAdmin"] = true;
            $collection = $this->_seller->getCollection()
                ->addFieldToFilter("seller_id", $returnArray["customerId"])
                ->addFieldToFilter("store_id", $storeId);
// If seller data doesn't exist for current store ///////////////////////////////////////////////////////////////////////////////
            if (!count($collection)) {
                $collection = $this->_seller->getCollection()
                    ->addFieldToFilter("seller_id", $returnArray["customerId"])
                    ->addFieldToFilter("store_id", 0);
            }
            foreach($collection as $record)     {
                $returnArray["isSeller"] = true;
                if($record->getIsSeller() == 0)
                    $returnArray["isPending"] = true;
            }
            $resultJson = $this->_resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($returnArray);
            return $resultJson;
        }

    }