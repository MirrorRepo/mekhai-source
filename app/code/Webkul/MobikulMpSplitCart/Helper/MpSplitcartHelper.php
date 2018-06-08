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

namespace Webkul\MobikulMpSplitCart\Helper;

use Magento\Checkout\Model\Cart;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Webkul MobikulMpSplitCart Helper
 */
class MpSplitcartHelper extends \Webkul\Mpsplitcart\Helper\Data
{
    protected $_requestInfoFilter;

    /**
     * @param \Magento\Framework\App\Helper\Context     $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param Cart                                      $cart
     * @param CustomerRepositoryInterface               $customerRepository
     * @param CustomerInterfaceFactory                  $customerDataFactory
     * @param \Magento\Customer\Model\Customer\Mapper   $customerMapper
     * @param DataObjectHelper                          $dataObjectHelper
     * @param \Magento\Checkout\Model\Session           $checkoutSession
     * @param \Magento\Catalog\Model\ProductRepository  $productRepository
     * @param \Webkul\Marketplace\Model\Product         $mpModel
     * @param \Magento\Framework\App\Request\Http       $request
     * @param \Webkul\MobikulMpSplitCart\Logger\Logger  $logger
     * @param \Webkul\Mobikul\Helper\Data               $mobikulHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        Cart $cart,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        DataObjectHelper $dataObjectHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Webkul\Marketplace\Model\Product $mpModel,
        \Magento\Framework\App\Request\Http $request,
        \Webkul\MobikulMpSplitCart\Logger\Logger $logger,
        \Webkul\Mobikul\Helper\Data $mobikulHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\Quote\Item $quoteItem,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        parent::__construct(
            $context,
            $objectManager,
            $customerSession,
            $cart,
            $customerRepository,
            $customerDataFactory,
            $customerMapper,
            $dataObjectHelper,
            $checkoutSession,
            $productRepository,
            $mpModel
        );
        $this->request = $request;
        $this->logger = $logger;
        $this->mobikulHelper = $mobikulHelper;
        $this->quoteFactory = $quoteFactory;
        $this->quoteItem = $quoteItem;
        $this->customerModel = $customerModel;
        $this->coreSession = $coreSession;
    }

    /**
     * [getVirtualCart used to get virtual cart of user].
     *
     * @return [array] [returns virtual cart data]
     */
    public function getVirtualCart()
    {
        try {
            if ($this->_customerSession->isLoggedIn()) {
                $this->logDataInLogger("getVirtualCart customer logged in");
                $customerId = $this->_customerSession->getId();
                $customerData = [];
                $savedCustomerData = $this->_customerRepository
                    ->getById($customerId);
                $customer = $this->_customerDataFactory->create();
                //merge saved customer data with new values
                $customerData = array_merge(
                    $this->_customerMapper->toFlatArray($savedCustomerData),
                    $customerData
                );
                if (isset($customerData['virtual_cart'])) {
                    $virtualCart = $customerData['virtual_cart'];
                    $virtualCart = json_decode($virtualCart, true);
                } else {
                    $virtualCart = "";
                }
            } else {
                $this->logDataInLogger("getVirtualCart customer not logged in");
                $wholeData       = $this->request->getPostValue();
                $flag = true;
                if ($wholeData) {
                    $authKey     = $this->request->getHeader("authKey");
                    $apiKey      = $this->request->getHeader("apiKey");
                    $apiPassword = $this->request->getHeader("apiPassword");
                    $authData    = $this->mobikulHelper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $flag = false;
                    }
                }
                if ($flag) {
                    $virtualCart = $this->_objectManager->get(
                        'Webkul\Mpsplitcart\Cookie\Guestcart'
                    )->get();
                    $virtualCart = json_decode($virtualCart, true);
                } else {
                    $remoteAddress = $this->_objectManager->get(
                        'Webkul\Mpsplitcart\Cookie\Guestcart'
                    )->getRemoteAddress();
                    $virtualCart = $this->coreSession->getData($remoteAddress);
                    $virtualCart = json_decode($virtualCart, true);
                    $this->logDataInLogger("getVirtualCart : ".print_r($virtualCart, true));
                }
            }
            // if (isset($virtualCart)) {
                return $virtualCart;
            // }
        } catch (\Exception $e) {
            $this->logDataInLogger("getVirtualCart Exception : ".$e->getMessage());
        }
    }

    /**
     * [setVirtualCart used to set virtual cart of user in customer session].
     *
     * @param [array] $virtualCart [contains virtual cart data]
     *
     * @return void
     */
    public function setVirtualCart($virtualCart)
    {
        try {
            $virtualCart = json_encode($virtualCart, true);

            if ($this->_customerSession->isLoggedIn()) {
                $this->logDataInLogger("customer logged in : ");
                $customerId  = $this->_customerSession->getId();
                $customerData      = [];
                $savedCustomerData = $this->_customerRepository
                    ->getById($customerId);

                $customer = $this->_customerDataFactory->create();
                //merge saved customer data with new values
                $customerData = array_merge(
                    $this->_customerMapper->toFlatArray($savedCustomerData),
                    $customerData
                );

                $customerData['virtual_cart'] = $virtualCart;
                $this->_dataObjectHelper->populateWithArray(
                    $customer,
                    $customerData,
                    '\Magento\Customer\Api\Data\CustomerInterface'
                );
                //save customer
                $this->_customerRepository->save($customer);
            } else {
                $wholeData       = $this->request->getPostValue();
                $flag = true;
                if ($wholeData) {
                    $authKey     = $this->request->getHeader("authKey");
                    $apiKey      = $this->request->getHeader("apiKey");
                    $apiPassword = $this->request->getHeader("apiPassword");
                    $authData    = $this->mobikulHelper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $flag = false;
                    }
                }
                if ($flag) {
                    $this->_objectManager->get(
                        'Webkul\Mpsplitcart\Cookie\Guestcart'
                    )->delete();
                    $this->_objectManager->get(
                        'Webkul\Mpsplitcart\Cookie\Guestcart'
                    )->set($virtualCart, 3600);
                } else {
                    $remoteAddress = $this->_objectManager->get(
                        'Webkul\Mpsplitcart\Cookie\Guestcart'
                    )->getRemoteAddress();
                    $this->coreSession->setData($remoteAddress, $virtualCart);
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("setVirtualCart Exception : ".$e->getMessage());
        }
    }

    /**
     * [updateVirtualCart used to update virtual cart data ].
     *
     * @param [array] $itemArray [item information]
     *
     * @return void
     */
    public function updateVirtualCart($itemArray)
    {
        try {
            $virtualCart = $this->getVirtualCart();
            if ($virtualCart
                && is_array($virtualCart)
                && $itemArray !== null
                && $this->checkMpsplitcartStatus()
            ) {
                foreach ($virtualCart as $sellerId => $productArray) {
                    foreach ($productArray as $productId => $itemInfo) {
                        if (array_key_exists($productId, $itemArray)
                            && $itemArray[$productId] == $itemInfo['item_id']
                        ) {
                            unset($virtualCart[$sellerId][$productId]);
                        }
                    }
                    $check = $this->checkEmptyVirtualCart($virtualCart[$sellerId]);
                    if ($check) {
                        unset($virtualCart[$sellerId]);
                    }
                }
                $this->setVirtualCart($virtualCart);

                $quote   = $this->_cart->getQuote();
                
                $itemIds = [];
                $proIds  = [];

                foreach ($quote->getAllVisibleItems() as $item) {
                    $itemIds[$item->getId()] = $item->getProductId();

                    $options = $item->getBuyRequest()->getData();
                    //checks for seller assign product
                    if (array_key_exists('mpassignproduct_id', $options)) {
                        $proIds[$item->getProductId()] = $options['mpassignproduct_id'];
                    }
                }

                if ($virtualCart
                    && is_array($virtualCart)
                    && $virtualCart !== ''
                    && count($virtualCart) > 0
                    && $this->checkMpsplitcartStatus()
                ) {
                    $addCart = $this->addProductToCart($virtualCart, $itemIds, $proIds);
                    if ($addCart) {
                        $this->saveCart();
                        $quote->setTotalsCollectedFlag(false)->collectTotals();
                        $quote->save();
                    }
                }
                $this->unsetCheckoutRemoveSession();
                if (!$this->_customerSession->isLoggedIn()) {
                    $wholeData       = $this->request->getPostValue();
                    $flag = true;
                    if ($wholeData) {
                        $authKey     = $this->request->getHeader("authKey");
                        $apiKey      = $this->request->getHeader("apiKey");
                        $apiPassword = $this->request->getHeader("apiPassword");
                        $authData    = $this->mobikulHelper->isAuthorized($authKey, $apiKey, $apiPassword);
                        if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                            $flag = false;
                        }
                    }
                    if ($flag) {
                        $this->addQuoteToVirtualCart();
                    }
                }
                // $this->setVirtualCart($virtualCart);
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("updateVirtualCart Exception : ".$e->getMessage());
            // $this->messageManager->addError($e->getMessage());
        }
    }

    public function updateMobileVirtualCart($itemArray)
    {
        try {
            $wholeData = $this->request->getPostValue();
            if ($wholeData) {
                $authKey     = $this->request->getHeader("authKey");
                $apiKey      = $this->request->getHeader("apiKey");
                $apiPassword = $this->request->getHeader("apiPassword");
                $authData    = $this->mobikulHelper->isAuthorized($authKey, $apiKey, $apiPassword);
                if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                    $quoteId = $this->mobikulHelper->validate($wholeData, "quoteId") ? $wholeData["quoteId"] : 0;
                    $customerId = $this->mobikulHelper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                    $storeId = $this->mobikulHelper->validate($wholeData, "storeId") ? $wholeData["storeId"] : 1;

                    if ($customerId == 0) {
                        $quoteId = 0;
                    }

                    if ($customerId == 0 && $quoteId == 0) {
                        $quote = $this->quoteFactory->create()
                            ->setStoreId($storeId)
                            ->setIsActive(true)
                            ->setIsMultiShipping(false)
                            ->save();
                        $quote->getBillingAddress();
                        $quote->getShippingAddress()->setCollectShippingRates(true);
                        $quote->collectTotals()->save();
                        $quoteId = (int) $quote->getId();
                    }

                    if ($customerId != 0) {
                        $quoteCollection = $this->quoteFactory->create()
                            ->getCollection()
                            ->addFieldToFilter("customer_id", $customerId)
                            ->addFieldToFilter("store_id", $storeId)
                            ->addFieldToFilter("is_active", 1)
                            ->addOrder("updated_at", "DESC");
                        $quote   = $quoteCollection->getFirstItem();
                        $quoteId = $quote->getId();
                        if ($quote->getId() < 0 || !$quoteId) {
                            $quote = $this->quoteFactory->create()
                                ->setStoreId($storeId)
                                ->setIsActive(true)
                                ->setIsMultiShipping(false)
                                ->save();
                            $quoteId = (int) $quote->getId();
                            $customer = $this->_customerRepository->getById($customerId);
                            $quote->assignCustomer($customer);
                            $quote->setCustomer($customer);
                            $quote->getBillingAddress();
                            $quote->getShippingAddress()->setCollectShippingRates(true);
                            $quote->collectTotals()->save();
                        }
                        if (!$this->_customerSession->isLoggedIn()) {
                            $customer = $this->customerModel->load($customerId);
                            $this->_customerSession->setCustomerAsLoggedIn($customer);
                        }
                    } else {
                        $quote = $this->quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                        $isGuest = true;
                    }

                    $this->_cart->setQuote($quote)->save();

                    $virtualCart = $this->getVirtualCart();
                    $addedQty = 0;
                    if ($virtualCart
                        && is_array($virtualCart)
                        && $itemArray !== null
                        && $this->checkMpsplitcartStatus()
                    ) {
                        foreach ($virtualCart as $sellerId => $productArray) {
                            foreach ($productArray as $productId => $itemInfo) {
                                if (array_key_exists($productId, $itemArray)
                                    && $itemArray[$productId] == $itemInfo['item_id']
                                ) {
                                    unset($virtualCart[$sellerId][$productId]);
                                }
                            }
                            $check = $this->checkEmptyVirtualCart($virtualCart[$sellerId]);
                            if ($check) {
                                unset($virtualCart[$sellerId]);
                            }
                        }
                        $this->setVirtualCart($virtualCart);


                        $itemIds = [];
                        $proIds  = [];

                        foreach ($quote->getAllVisibleItems() as $item) {
                            $itemIds[$item->getId()] = $item->getProductId();

                            $options = $item->getBuyRequest()->getData();
                            //checks for seller assign product
                            if (array_key_exists('mpassignproduct_id', $options)) {
                                $proIds[$item->getProductId()] = $options['mpassignproduct_id'];
                            }
                        }

                        if ($virtualCart
                            && is_array($virtualCart)
                            && $virtualCart !== ''
                            && count($virtualCart) > 0
                            && $this->checkMpsplitcartStatus()
                        ) {
                            $addCart = $this->addProductToCartMobile($virtualCart, $itemIds, $proIds, $quote);
                            if ($addCart!==false && !empty($addCart['addCart'])) {
                                $quote->collectTotals()->save();
                                // $this->saveCart();
                                $this->_cart->saveQuote($quote);
                                $addedQty = $addCart['addedQty'];
                            }
                        }
                        $this->unsetCheckoutRemoveSession();
                        if (!$this->_customerSession->isLoggedIn()) {
                            $wholeData       = $this->request->getPostValue();
                            $flag = true;
                            if ($wholeData) {
                                $authKey     = $this->request->getHeader("authKey");
                                $apiKey      = $this->request->getHeader("apiKey");
                                $apiPassword = $this->request->getHeader("apiPassword");
                                $authData    = $this->mobikulHelper->isAuthorized($authKey, $apiKey, $apiPassword);
                                if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                                    $flag = false;
                                }
                            }
                            if ($flag) {
                                $this->addQuoteToVirtualCart();
                            }
                        }
                    }
                    
                    return [
                        'quoteId' => $quoteId,
                        'customerId' => $customerId,
                        'storeId' => $storeId,
                        'qty' => $addedQty
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("updateMobileVirtualCart Exception : ".$e->getMessage());
        }
    }

    public function logDataInLogger($data)
    {
        $this->logger->info($data);
    }

    public function addProductToCartMobile($virtualCart, $itemIds, $productIds, $quote)
    {
        try {
            $addCart = false;
            $addedQty = 0;
            foreach ($virtualCart as $sellerId => $productArray) {
                foreach ($productArray as $productId => $itemData) {
                    if (!array_key_exists($itemData['item_id'], $itemIds)
                        && (!in_array($productId, $itemIds)
                        || isset($itemData['mpassignproduct_id'])
                        || array_key_exists($productId, $productIds))
                    ) {
                        $params = [];
                        $params['qty'] = $itemData['qty'];
                        $params['product'] = $productId;
                        // $params['_current'] = true;
                        if (isset($itemData['mpassignproduct_id'])) {
                            $params['mpassignproduct_id'] = $itemData[
                                'mpassignproduct_id'
                            ];
                        }

                        if (isset($itemData['child']) && $itemData['child']!=='') {
                            $attributes = json_decode($itemData['child'], true);
                            // $params['super_attribute'] = $attributes;
                            $params = array_merge($params, $attributes);
                        }
                        if (isset($itemData['bundle_options']) && $itemData['bundle_options']!=='') {
                            $bundleItemData = json_decode($itemData['bundle_options'], true);
                            $params = array_merge($params, $bundleItemData);
                        }

                        try {
                            $_product = $this->_productRepository
                                ->getById($productId);
                            if ($_product) {
                                $addCart = true;
                                // $this->_cart->addProduct($_product, $params);
                                $request = $this->_getProductRequest($params);
                                $quoteItem = $quote->addProduct($_product, $request);
                                $addedQty += $quoteItem->getQty();
                            }
                        } catch (\Exception $e) {
                            $this->logDataInLogger("addProductToCartMobile Exception : ".$e->getMessage());
                        }
                    }
                }
            }
            return [
                'addCart' => $addCart,
                'addedQty' => $addedQty
            ];
        } catch (\Exception $e) {
            $this->logDataInLogger("addProductToCartMobile Exception2 : ".$e->getMessage());
            return false;
        }
    }

    protected function _getProductRequest($requestInfo)     {
        if ($requestInfo instanceof \Magento\Framework\DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new \Magento\Framework\DataObject(["qty"=>$requestInfo]);
        } elseif (is_array($requestInfo)) {
            $request = new \Magento\Framework\DataObject($requestInfo);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__("We found an invalid request for adding productsddffd to quote."));
        }
        $this->getRequestInfoFilter()->filter($request);
        return $request;
    }

    protected function getRequestInfoFilter(){
        if ($this->_requestInfoFilter === null) {
            $this->_requestInfoFilter = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Checkout\Model\Cart\RequestInfoFilterInterface::class
            );
        }
        return $this->_requestInfoFilter;
    }

    public function addQuoteToVirtualCart()
    {
        try {
            $quote = $this->_cart->getQuote();
            $virtualCart = $this->getVirtualCart();

            if ($virtualCart == null
                || !is_array($virtualCart)
                || $virtualCart==""
            ) {
                $virtualCart = [];
            }
            foreach ($quote->getAllVisibleItems() as $item) {
                $attributesData = [];
                $bundleOption = [];
                $productType = $item->getProductType();
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

                if ($productType=="bundle" && $item->getHasChildren()) {
                    $bundleOption['selected_configurable_option'] = $options['info_buyRequest']['selected_configurable_option'];
                    if (isset($options['info_buyRequest']['bundle_option'])) {
                        $bundleOption['bundle_option'] = $options['info_buyRequest']['bundle_option'];
                    }
                    if (isset($options['info_buyRequest']['bundle_option_qty'])) {
                        $bundleOption['bundle_option_qty'] = $options['info_buyRequest']['bundle_option_qty'];
                    }
                } else {
                    $attributesData = $options['info_buyRequest'];
                    if (isset($attributesData['qty'])) {
                        unset($attributesData['qty']);
                    }
                    if (isset($attributesData['product'])) {
                        unset($attributesData['product']);
                    }
                }

                $productId = $item->getProductId();

                $options   = $item->getBuyRequest()->getData();

                //checks for seller assign product
                if (array_key_exists("mpassignproduct_id", $options)) {
                    $mpAssignId = $options["mpassignproduct_id"];
                    $sellerId = $this->getSellerIdFromMpassign(
                        $mpAssignId
                    );
                    $virtualCart[$sellerId][$productId][
                        'mpassignproduct_id'
                    ] = $mpAssignId;
                } else {
                    $sellerId=$this->getSellerId($productId);
                }

                $virtualCart[$sellerId][$productId]['qty'] = $item->getQty();
                $virtualCart[$sellerId][$productId]['item_id'] = $item->getId();
                if ($attributesData && count($attributesData)>0) {
                    $virtualCart[$sellerId][$productId]['child'] = json_encode($attributesData, true);
                } else {
                    if (isset($virtualCart[$sellerId][$productId]['child'])) {
                        unset($virtualCart[$sellerId][$productId]['child']);
                    }
                }
                if ($bundleOption && count($bundleOption)>0) {
                    $virtualCart[$sellerId][$productId]['bundle_options'] = json_encode($bundleOption, true);
                }
            }
            $flag = true;
            if (!$this->_customerSession->isLoggedIn()) {
                $wholeData       = $this->request->getPostValue();
                if ($wholeData) {
                    $authKey     = $this->request->getHeader("authKey");
                    $apiKey      = $this->request->getHeader("apiKey");
                    $apiPassword = $this->request->getHeader("apiPassword");
                    $authData    = $this->mobikulHelper->isAuthorized($authKey, $apiKey, $apiPassword);
                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                        $flag = false;
                    }
                }
                
            }
            // if ($flag) {
                $this->setVirtualCart($virtualCart);
            // } else {
            //     return $virtualCart;
            // }
        } catch (\Exception $e) {
            $this->logDataInLogger("addQuoteToVirtualCart Exception : ".$e->getMessage());
            // $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * [getUpdatedQuote used to remove items of other sellers from the quote].
     *
     * @param [integer] $productSellerId [current seller checkout cart id]
     *
     * @return void
     */
    public function getUpdatedQuote($productSellerId)
    {
        try {
            if ($productSellerId==0) {
                $productSellerId = 0;
            }
            $cart      = $this->_cart->getQuote();
            $cartArray = [];
            $flag = false;
            $isMobikul = false;
            $removedItemIds = [];

            foreach ($cart->getAllVisibleItems() as $item) {
                if (!$item->hasParentItemId()) {
                    $options = $item->getBuyRequest()->getData();
                    //checks for seller assign product
                    if (array_key_exists('mpassignproduct_id', $options)) {
                        $sellerId = $this->getSellerIdFromMpassign(
                            $options['mpassignproduct_id']
                        );
                    } else {
                        $sellerId = $this->getSellerId($item->getProductId());
                    }
                    $sellerId = (int)$sellerId;
                    $productSellerId = (int)$productSellerId;

                    if ($productSellerId !== $sellerId) {
                        $this->setCheckoutRemoveSession();
                        $this->_cart->removeItem($item->getId());
                        $flag = true;

                        $wholeData       = $this->request->getPostValue();
                        if ($wholeData) {
                            $authKey     = $this->request->getHeader("authKey");
                            $apiKey      = $this->request->getHeader("apiKey");
                            $apiPassword = $this->request->getHeader("apiPassword");
                            $authData    = $this->mobikulHelper->isAuthorized($authKey, $apiKey, $apiPassword);
                            if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                                $isMobikul = true;
                                $quoteItem = $this->quoteItem->load((int)$item->getId());
                                $quoteItem->delete();
                            }
                        }
                    }
                }
            }
            if ($flag) {
                $this->saveCart();
                if (!$isMobikul) {
                    $this->updateCart();
                }
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("getUpdatedQuote Exception : ".$e->getMessage());
        }
    }

    /**
     * [addProductToCart used to add product in cart]
     *
     * @param [array] $virtualCart [contains virtual cart data of user]
     * @param [array] $itemIds     [contains product ids]
     * @param [array] $productIds  [contains product ids]
     */
    public function addProductToCart($virtualCart, $itemIds, $productIds)
    {
        try {
            $addCart = false;
            foreach ($virtualCart as $sellerId => $productArray) {
                foreach ($productArray as $productId => $itemData) {
                    if (!array_key_exists($itemData['item_id'], $itemIds)
                        && (!in_array($productId, $itemIds)
                        || isset($itemData['mpassignproduct_id'])
                        || array_key_exists($productId, $productIds))
                    ) {
                        $params = [];
                        $params['qty'] = $itemData['qty'];
                        $params['product'] = $productId;
                        // $params['_current'] = true;
                        if (isset($itemData['mpassignproduct_id'])) {
                            $params['mpassignproduct_id'] = $itemData[
                                'mpassignproduct_id'
                            ];
                        }

                        if (isset($itemData['child']) && $itemData['child']!=='') {
                            $attributes = json_decode($itemData['child'], true);
                            // $params['super_attribute'] = $attributes;
                            $params = array_merge($params, $attributes);
                        }
                        if (isset($itemData['bundle_options']) && $itemData['bundle_options']!=='') {
                            $bundleItemData = json_decode($itemData['bundle_options'], true);
                            $params = array_merge($params, $bundleItemData);
                        }

                        try {
                            $_product = $this->_productRepository
                                ->getById($productId);
                            if ($_product) {
                                $addCart = true;
                                $this->_cart->addProduct($_product, $params);

                                $wholeData       = $this->request->getPostValue();
                                if ($wholeData) {
                                    $authKey     = $this->request->getHeader("authKey");
                                    $apiKey      = $this->request->getHeader("apiKey");
                                    $apiPassword = $this->request->getHeader("apiPassword");
                                    $authData    = $this->mobikulHelper->isAuthorized($authKey, $apiKey, $apiPassword);
                                    if ($authData["responseCode"] == 1 || $authData["responseCode"] == 2) {
                                        $quoteId = $this->mobikulHelper->validate($wholeData, "quoteId") ? $wholeData["quoteId"] : 0;
                                        $customerId = $this->mobikulHelper->validate($wholeData, "customerId") ? $wholeData["customerId"] : 0;
                                        $storeId = $this->mobikulHelper->validate($wholeData, "storeId") ? $wholeData["storeId"] : 1;

                                        if ($customerId != 0) {
                                            $quoteCollection = $this->quoteFactory->create()->getCollection()
                                                ->addFieldToFilter("customer_id", $customerId)
                                                // ->addFieldToFilter("store_id", $storeId)
                                                ->addFieldToFilter("is_active", 1)
                                                ->addOrder("updated_at", "DESC");
                                            $quote = $quoteCollection->getFirstItem();
                                        }
                                        if ($quoteId != 0) {
                                            $quote = $this->quoteFactory->create()->setStoreId($storeId)->load($quoteId);
                                            $quote->addProduct($_product, $params)->save();
                                        }
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            $this->logDataInLogger("addProductToCart Exception : ".$e->getMessage());
                        }
                    }
                }
            }
            return $addCart;
        } catch (\Exception $e) {
            $this->logDataInLogger("addProductToCart Exception2 : ".$e->getMessage());
            return false;
        }
    }
}
