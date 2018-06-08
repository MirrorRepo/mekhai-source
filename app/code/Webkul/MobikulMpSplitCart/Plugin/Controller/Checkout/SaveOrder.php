<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MobikulMpSplitCart
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MobikulMpSplitCart\Plugin\Controller\Checkout;

use \Magento\Framework\Controller\ResultFactory;

class SaveOrder {

    public function __construct(
        \Webkul\MobikulMpSplitCart\Helper\Data $helper,
        \Webkul\Mpsplitcart\Helper\Data $splitCartHelper,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\Order $order
    ) {
        $this->helper = $helper;
        $this->splitCartHelper = $splitCartHelper;
        $this->resultFactory = $resultFactory;
        $this->quoteFactory = $quoteFactory;
        $this->order = $order;
    }

    public function afterExecute(\Webkul\Mobikul\Controller\Checkout\SaveOrder $subject, $response) {
        try {
            $returnArray = json_decode($response->getRawData());
            
            if ($returnArray->success
                && $returnArray->orderId !== 0
                && $this->splitCartHelper->checkMpsplitcartStatus()
            ) {
                $order = $this->order->load($returnArray->orderId);
                $itemIds = [];
                foreach ($order->getAllVisibleItems() as $item) {
                    $itemIds[$item->getProductId()] = $item->getQuoteItemId();
                }
                $result = $this->splitCartHelper->updateMobileVirtualCart($itemIds);
                if (!empty($result) && isset($result['quoteId']) && $result['quoteId']!==0) {

                    $returnArray->quoteId = $result['quoteId'];
                    $returnArray->customerId = $result['customerId'];
                    $returnArray->storeId = $result['storeId'];
                    
                    $quote = $this->quoteFactory->create()->setStoreId($result['storeId'])->load($result['quoteId']);
                    if (!empty($result['qty']) && $result['qty'] > 0) {
                        $returnArray->cartCount = $result['qty'] * 1;
                    }
                }
            }
            $this->helper->logDataInLogger("SaveOrder return data : ".print_r($returnArray, true));
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($returnArray);
            return $resultJson;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("SaveOrder afterExecute Exception : ".$e->getMessage());
        }
    }
}