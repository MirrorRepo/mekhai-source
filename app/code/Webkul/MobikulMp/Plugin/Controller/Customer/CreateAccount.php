<?php

    namespace Webkul\MobikulMp\Plugin\Controller\Customer;
    use \Magento\Framework\Controller\ResultFactory;

    class CreateAccount     {

        protected $_date;
        protected $_helper;
        protected $_seller;
        protected $_request;
        protected $_jsonHelper;
        protected $_resultFactory;
        protected $_marketplaceHelper;

        public function __construct(
            \Webkul\Mobikul\Helper\Data $helper,
            \Webkul\Marketplace\Model\Seller $seller,
            \Magento\Framework\App\Request\Http $request,
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Magento\Framework\Stdlib\DateTime\DateTime $date,
            \Webkul\Marketplace\Helper\Data $marketplaceHelper,
            \Magento\Framework\Controller\ResultFactory $resultFactory
        ) {
            $this->_date              = $date;
            $this->_helper            = $helper;
            $this->_seller            = $seller;
            $this->_request           = $request;
            $this->_jsonHelper        = $jsonHelper;
            $this->_resultFactory     = $resultFactory;
            $this->_marketplaceHelper = $marketplaceHelper;
        }

        public function aroundExecute(\Webkul\Mobikul\Controller\Customer\CreateAccount $subject, \Closure $proceed)     {
            $returnArray                  = [];
            $returnArray["authKey"]       = "";
            $returnArray["success"]       = false;
            $returnArray["message"]       = "";
            $returnArray["isAdmin"]       = false;
            $returnArray["isSeller"]      = false;
            $returnArray["cartCount"]     = 0;
            $returnArray["isPending"]     = false;
            $returnArray["customerId"]    = 0;
            $returnArray["responseCode"]  = 0;
            $returnArray["customerName"]  = "";
            $returnArray["customerEmail"] = "";
            $wholeData    = $this->_request->getPostValue();
            $email        = $this->_helper->validate($wholeData, "email")        ? $wholeData["email"]        : "";
            $shopUrl      = $this->_helper->validate($wholeData, "shopUrl")      ? $wholeData["shopUrl"]      : "";
            $becomeSeller = $this->_helper->validate($wholeData, "becomeSeller") ? $wholeData["becomeSeller"] : 0;
            if($becomeSeller == 1)  {
                $model = $this->_seller->getCollection()->addFieldToFilter("shop_url", $shopUrl);
                if (count($model)) {
                    $returnArray["message"] = __("Shop URL already exist please set another.");
                    $resultJson = $this->_resultFactory->create(ResultFactory::TYPE_JSON);
                    $resultJson->setData($returnArray);
                    return $resultJson;
                }
            }
            $response    = $proceed();
            $returnArray = $this->_jsonHelper->jsonDecode($response->getRawData());
            $returnArray["isAdmin"]   = false;
            $returnArray["isSeller"]  = false;
            $returnArray["isPending"] = false;
            if($returnArray["customerId"] != 0)  {
                if($becomeSeller == 1)  {
                    $status = $this->_marketplaceHelper->getIsPartnerApproval() ? 0 : 1;
                    $seller = $this->_seller;
                    $seller->setData("is_seller", $status);
                    $seller->setData("shop_url", $shopUrl);
                    $seller->setData("seller_id", $returnArray["customerId"]);
                    $seller->setCreatedAt($this->_date->gmtDate());
                    $seller->setUpdatedAt($this->_date->gmtDate());
                    $seller->setAdminNotification(1);
                    $seller->save();
                    $returnArray["isSeller"] = true;
                    if($status == 0)
                        $returnArray["isPending"] = true;
                } elseif ($this->getSellerByCustomerId($returnArray["customerId"])) {
                    $returnArray["isSeller"] = true;
                }
            }
            if($email == $this->_helper->getConfigData("mobikulmp/admin/email"))
                $returnArray["isAdmin"] = true;
            $resultJson = $this->_resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($returnArray);
            return $resultJson;
        }
        public function getSellerByCustomerId($id) {
            return $this->_seller->getCollection()
                ->addFieldToFilter("seller_id", $id)
                ->addFieldToFilter("is_seller", ['eq' => 1])
                ->getSize();
        }

    }