<?php

    namespace Webkul\MobikulMp\Plugin\Controller\Catalog;
    use \Magento\Framework\Controller\ResultFactory;

    class ProductPageData     {

        protected $_helper;
        protected $_request;
        protected $_jsonHelper;
        protected $_viewTemplate;
        protected $_resultFactory;
        protected $_marketplaceHelper;

        public function __construct(
            ResultFactory $resultFactory,
            \Webkul\Mobikul\Helper\Data $helper,
            \Magento\Framework\App\Request\Http $request,
            \Magento\Framework\Json\Helper\Data $jsonHelper,
            \Webkul\Marketplace\Helper\Data $marketplaceHelper,
            \Magento\Framework\View\Element\Template $viewTemplate
        ) {
            $this->_helper            = $helper;
            $this->_request           = $request;
            $this->_jsonHelper        = $jsonHelper;
            $this->_viewTemplate      = $viewTemplate;
            $this->_resultFactory     = $resultFactory;
            $this->_marketplaceHelper = $marketplaceHelper;
        }

        public function afterExecute(\Webkul\Mobikul\Controller\Catalog\ProductPageData $subject, $response)     {
            $returnArray = json_decode($response->getRawData());
            $wholeData   = $this->_request->getPostValue();
            $productId   = $this->_helper->validate($wholeData, "productId") ? $wholeData["productId"] : 0;
            $sellerId    = 0;
            $returnArray->displaySellerInfo = (bool)$this->_helper->getConfigData("marketplace/profile_settings/seller_profile_display");
            $returnArray->sellerId          = 0;
            $returnArray->shoptitle         = "";
            $returnArray->sellerRating      = [];
            $returnArray->reviewDescription = "";
            $marketplaceProduct = $this->_marketplaceHelper->getSellerProductDataByProductId($productId);
            foreach ($marketplaceProduct as $eachProduct) {
                $sellerId = $eachProduct["seller_id"];
            }
            if($sellerId != 0)  {
                $shoptitle        = "";
                $sellerCollection = $this->_marketplaceHelper->getSellerDataBySellerId($sellerId);
                foreach ($sellerCollection as $seller) {
                    $shoptitle = $seller["shop_title"];
                    if(!$shoptitle)
                        $shoptitle = $seller->getShopUrl();
                }
                $returnArray->sellerId  = $sellerId;
                $returnArray->shoptitle = $this->_viewTemplate->escapeHtml($shoptitle);
                $returnArray->sellerAverageRating = $this->_marketplaceHelper->getSelleRating($sellerId);
                $feeds = $this->_marketplaceHelper->getFeedTotal($sellerId);
                $returnArray->reviewDescription = (($this->_marketplaceHelper->getSelleRating($sellerId)*100)/5)."% ".__("positive feedback")." (".__("%1 ratings",number_format($feeds["feedcount"])).") ";
                $returnArray->sellerRating[] = [
                    "label" => __("Price"),
                    "value" => round(($feeds["price"]/20), 1, PHP_ROUND_HALF_UP)
                ];
                $returnArray->sellerRating[] = [
                    "label" => __("Value"),
                    "value" => round(($feeds["value"]/20), 1, PHP_ROUND_HALF_UP)
                ];
                $returnArray->sellerRating[] = [
                    "label" => __("Quality"),
                    "value" => round(($feeds["quality"]/20), 1, PHP_ROUND_HALF_UP)
                ];
            }
            $resultJson = $this->_resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($returnArray);
            return $resultJson;
        }

    }