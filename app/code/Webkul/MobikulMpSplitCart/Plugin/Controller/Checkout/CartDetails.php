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

class CartDetails {

    public function __construct(
        \Webkul\MobikulMpSplitCart\Helper\Data $helper,
        \Webkul\Mpsplitcart\Helper\Data $splitCartHelper,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\Request\Http $request,
        \Webkul\Mobikul\Helper\Data $mobikulHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customerModel
    ) {
        $this->helper = $helper;
        $this->splitCartHelper = $splitCartHelper;
        $this->resultFactory = $resultFactory;
        $this->quoteItem = $quoteItem;
        $this->priceHelper = $priceHelper;
        $this->jsonHelper = $jsonHelper;
        $this->quoteFactory = $quoteFactory;
        $this->cart = $cart;
        $this->request = $request;
        $this->mobikulHelper = $mobikulHelper;
        $this->customerSession = $customerSession;
        $this->customerModel = $customerModel;
    }

    public function aroundExecute(\Webkul\Mobikul\Controller\Checkout\CartDetails $subject, callable $proceed) {
        try {
            $wholeData       = $this->request->getPostValue();
            $isGuest = false;
            if ($wholeData) {
                $authKey     = $this->request->getHeader("authKey");
                $apiKey      = $this->request->getHeader("apiKey");
                $apiPassword = $this->request->getHeader("apiPassword");
                $authData    = $this->mobikulHelper->isAuthorized($authKey, $apiKey, $apiPassword);
                if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                    $quoteId         = $this->mobikulHelper->validate($wholeData, "quoteId")         ? $wholeData["quoteId"]         : 0;
                    $storeId         = $this->mobikulHelper->validate($wholeData, "storeId")         ? $wholeData["storeId"]         : 1;
                    $customerId      = $this->mobikulHelper->validate($wholeData, "customerId")      ? $wholeData["customerId"]      : 0;

                    if ($customerId != 0) {
                        $quoteCollection = $this->quoteFactory->create()->getCollection()
                            ->addFieldToFilter("customer_id", $customerId)
                            // ->addFieldToFilter("store_id", $storeId)
                            ->addFieldToFilter("is_active", 1)
                            ->addOrder("updated_at", "DESC");
                        $quote = $quoteCollection->getFirstItem();

                        if (!$this->customerSession->isLoggedIn()) {
                            $customer = $this->customerModel->load($customerId);
                            $this->customerSession->setCustomerAsLoggedIn($customer);
                        }
                    }
                    if ($quoteId != 0) {
                        $quote = $this->quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                    }
                    if (isset($quote)) {
                        $this->cart->setQuote($quote)->save();
                    }

                    if ($this->splitCartHelper->checkMpsplitcartStatus()) {
                        $this->splitCartHelper->addVirtualCartToQuote();
                        $this->splitCartHelper->addQuoteToVirtualCart();
                    }
                }
            }

            $response = $proceed();
            $returnArray = json_decode($response->getRawData());

            if ($this->splitCartHelper->checkMpsplitcartStatus()
                && $returnArray->cartCount > 0
                && !empty($returnArray->items)
            ) {
                $splitCartArray = [];
                foreach ($returnArray->items as $item) {
                    $quoteItem = $this->quoteItem->load($item->id);

                    if (!$quoteItem->hasParentItemId()) {
                        $options = $quoteItem->getBuyRequest()->getData();

                        if (array_key_exists("mpassignproduct_id", $options)) {
                            $mpAssignId = $options["mpassignproduct_id"];
                            $sellerId = $this->splitCartHelper->getSellerIdFromMpassign(
                                $mpAssignId
                            );
                        } else {
                            $sellerId = $this->splitCartHelper->getSellerId($item->productId);
                        }
                        $price =  $quoteItem->getRowTotal();

                        $key = array_search($sellerId, array_column($splitCartArray, 'seller_id'));

                        if (($key || $key==0) && $key!==false) {
                            $splitCartArray[$key]['totals']['total'] += $price;

                            $formattedPrice = $this->priceHelper->currency(
                                $splitCartArray[$key]['totals']['total'],
                                true,
                                false
                            );
                            $splitCartArray[$key]['totals']['formatted_total'] = $formattedPrice;
                            $splitCartArray[$key]['items'][] = $item;
                        } else {
                            $formattedPrice = $this->priceHelper->currency(
                                $price,
                                true,
                                false
                            );

                            $splitCartArray[] = [
                                'seller_id' => $sellerId,
                                'items' => [
                                    $item
                                ],
                                'totals' => [
                                    'total' => $price,
                                    'formatted_total' => $formattedPrice
                                ]
                            ];
                        }
                    }
                }
                $returnArray->splitCartData = $splitCartArray;
                $this->helper->logDataInLogger("CartDetails aroundExecute returnArray : ".print_r($returnArray, true));
                // unset($returnArray->items);
            }
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($returnArray);
            return $resultJson;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("CartDetails aroundExecute Exception : ".$e->getMessage());
        }
    }
}