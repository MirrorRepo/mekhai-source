<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpsplitcart
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpsplitcart\Helper;

use Magento\Checkout\Model\Cart;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Mpsplitcart data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $_customerDataFactory;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $_customerMapper;

    /**
     * @var Magento\Framework\Api\DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * @var \Webkul\Marketplace\Model\Product
     */
    protected $_mpModel;

    /**
     * [__construct]
     *
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
        \Webkul\Marketplace\Model\Product $mpModel
    ) {
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
        $this->_cart = $cart;
        $this->_customerRepository = $customerRepository;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_customerMapper = $customerMapper;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_productRepository = $productRepository;
        $this->_mpModel = $mpModel;
    }

    /**
     * [getEnableSplitcartSettings used to get spitcart is enable or not].
     *
     * @return [integer] [returns 0 if disable else return 1]
     */
    public function getEnableSplitcartSettings()
    {
        return $this->scopeConfig->getValue(
            'marketplace/marketplacesplitcart_settings/mpsplitcart_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
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

                    if ($productSellerId !== $sellerId) {
                        $this->setCheckoutRemoveSession();
                        $this->_cart->removeItem($item->getId());
                        $flag = true;
                        // $this->saveCart();
                    }
                }
            }
            if ($flag) {
                $this->saveCart();
                $this->updateCart();
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * [saveCart save cart]
     *
     * @return void
     */
    public function saveCart()
    {
        $this->_cart->save();
    }

    /**
     * [setCheckoutRemoveSession used to set a value in checkout session].
     *
     * @return void
     */
    public function setCheckoutRemoveSession()
    {
        $this->_checkoutSession->setWkRemoveItem(1);
    }

    /**
     * [getCheckoutRemoveSession used to get a value from checkout session].
     *
     * @return [integer]
     */
    public function getCheckoutRemoveSession()
    {
        return $this->_checkoutSession->getWkRemoveItem();
    }

    /**
     * [unsetCheckoutRemoveSession used to unset value from checkout session]
     *
     * @return void
     */
    public function unsetCheckoutRemoveSession()
    {
        $this->_checkoutSession->unsWkRemoveItem();
    }

    /**
     * [setWkCartWasUpdated used to set cart was updated true].
     *
     * @return void
     */
    public function setWkCartWasUpdated()
    {
        $this->_checkoutSession->setCartWasUpdated(true);
    }

    /**
     * [getVirtualCart used to get virtual cart of user].
     *
     * @return [array] [returns virtual cart data]
     */
    public function getVirtualCart()
    {
        if ($this->_customerSession->isLoggedIn()) {
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
            $virtualCart = $this->_objectManager->get(
                'Webkul\Mpsplitcart\Cookie\Guestcart'
            )->get();
            $virtualCart = json_decode($virtualCart, true);
        }
        return $virtualCart;
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
        $virtualCart = json_encode($virtualCart, true);
        // $this->_logger->info(json_encode($virtualCart));=

        if ($this->_customerSession->isLoggedIn()) {
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
            $this->_objectManager->get(
                'Webkul\Mpsplitcart\Cookie\Guestcart'
            )->delete();
            $this->_objectManager->get(
                'Webkul\Mpsplitcart\Cookie\Guestcart'
            )->set($virtualCart, 3600);
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
                    $this->addQuoteToVirtualCart();
                }
                // $this->setVirtualCart($virtualCart);
            }
        } catch (\Exception $e) {
            // $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * [checkEmptyVirtualCart checks array empty or not]
     *
     * @param  [array] $data [virtual cart]
     * @return [boolean]
     */
    public function checkEmptyVirtualCart($data)
    {
        if (is_array($data) && count($data) <= 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * [addVirtualCartToQuote used to add products in cart from virtual cart].
     *
     * @return void
     */
    public function addVirtualCartToQuote()
    {
        $quote       = $this->_cart->getQuote();
        $virtualCart = $this->getVirtualCart();
        $oldVirtualCart = $virtualCart;

        $itemIds = [];
        $proIds  = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item->hasParentItemId()) {
                $itemIds[$item->getId()] = $item->getProductId();

                $options = $item->getBuyRequest()->getData();
                //checks for seller assign product
                if (array_key_exists('mpassignproduct_id', $options)) {
                    $proIds[$item->getProductId()] = $options['mpassignproduct_id'];
                }
            }
        }

        if ($virtualCart
            && is_array($virtualCart)
            && $virtualCart !== ''
            && $this->checkMpsplitcartStatus()
        ) {
            $addCart = $this->addProductToCart($virtualCart, $itemIds, $proIds);
            if ($addCart) {
                $this->saveCart();
                // $quote->setTotalsCollectedFlag(false)->collectTotals();
                // $quote->save();

                $cartData = [];
                foreach ($quote->getAllVisibleItems() as $item) {
                    $cartData[$item->getId()]['qty'] = $item->getQty();
                }
                if (!empty($cartData)) {
                    $cartData = $this->_cart->suggestItemsQty($cartData);
                    try {
                        $this->_cart->updateItems($cartData)->save();
                    } catch (\Exception $e) {
                    }
                }
            }
        }
        $this->checkMpQuoteSystem($oldVirtualCart);
        // $this->setWkCartWasUpdated();
        $this->unsetCheckoutRemoveSession();
        $this->updateCart();
    }

    public function checkQuoteExistsInVirtualCart($item, $quoteItemId, $virtualCart)
    {
        if ($virtualCart && count($virtualCart)>0) {
            foreach ($virtualCart as $sellerId => $data) {
                if (array_key_exists($item->getProductId(), $data) && $data[$item->getProductId()]['item_id']==$quoteItemId) {
                    return true;
                    break;
                }
            }
        }
    }

    /**
     * [checkMpQuoteSystem used if any quote product was added into cart]
     *
     * @return void
     */
    public function checkMpQuoteSystem($oldVirtualCart)
    {
        $quote      = $this->_cart->getQuote();
        $customerId = $this->_customerSession->getId();
        $check      = $this->_moduleManager->isEnabled("Webkul_Mpquotesystem");

        if ($check && $customerId) {
            $mpQuoteSystemHelper = $this->_objectManager->get(
                'Webkul\Mpquotesystem\Helper\Data'
            );
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($item->getParentItemId() === null
                    && $item->getItemId() > 0
                ) {
                    $price    = 0;
                    $quoteId  = 0;
                    $quoteQty = 0;

                    $baseCurrencyCode    = $mpQuoteSystemHelper->getBaseCurrencyCode();
                    $currentCurrencyCode = $mpQuoteSystemHelper->getCurrentCurrencyCode();

                    $options = $item->getBuyRequest()->getData();
                    //checks for seller assign product
                    if (!array_key_exists('mpassignproduct_id', $options)) {
                        $model = $this->_objectManager->get(
                            'Webkul\Mpquotesystem\Model\QuotesFactory'
                        )->create();
                        $mpQuote = $model->getCollection()
                            ->addFieldToFilter(
                                'product_id',
                                $item->getProductId()
                            )->addFieldToFilter(
                                'item_id',
                                ['neq'=>0]
                            )->addFieldToFilter(
                                'status',
                                ['eq'=>2]
                            );

                        if ($mpQuote->getSize()>0) {
                            $res = false;
                            foreach ($mpQuote as $quote) {
                                $res = $this->checkQuoteExistsInVirtualCart(
                                    $item,
                                    $quote->getItemId(),
                                    $oldVirtualCart
                                );
                                if ($res) {
                                    $price    = $quote->getQuotePrice();
                                    $quoteId  = $quote->getEntityId();
                                    $quoteQty = $quote->getQuoteQty();

                                    $quote->setItemId($item->getId());
                                    $quote->save();
                                }
                            }
                            if ($res) {
                                $priceOne = $mpQuoteSystemHelper->getwkconvertCurrency(
                                    $currentCurrencyCode,
                                    $baseCurrencyCode,
                                    $price
                                );

                                if ($quoteId != 0) {
                                    $item->setCustomPrice($priceOne);
                                    $item->setOriginalCustomPrice($priceOne);
                                    $item->setQty($quoteQty);
                                    $item->setRowTotal($priceOne * $quoteQty);
                                    $item->getProduct()->setIsSuperMode(true);
                                    $item->save();
                                }
                            }
                        }
                    }
                }
            }
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
                                // $this->saveCart();
                            }
                        } catch (\Exception $e) {
                        }
                    }
                }
            }
            return $addCart;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * [checkSplitCart used to get all seller ids of products added in cart].
     *
     * @return [array] [seller ids]
     */
    public function checkSplitCart()
    {
        $quote     = $this->_cart->getQuote();
        $sellerIds = [];

        foreach ($quote->getAllVisibleItems() as $item) {
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
                $sellerIds[] = $sellerId;
            }
        }
        $sellerIds = array_unique($sellerIds);

        return $sellerIds;
    }
    /**
     * [getSellerId used to get seller id by giving a product id].
     *
     * @param [integer] $productid [contains product id]
     *
     * @return [integer] [returns seller id]
     */
    public function getSellerId($productid)
    {
        $sellerId = 0;
        $model = $this->_mpModel->getCollection()
            ->addFieldToFilter(
                'mageproduct_id',
                $productid
            );
        if ($model->getSize()) {
            foreach ($model as $value) {
                $sellerId = $value->getSellerId();
            }
        }

        return $sellerId;
    }

    /**
     * [getSellerIdFromMpassign used to get seller id
     * who has assigned the product of other seller].
     *
     * @param [integer] $assignId [contains assign id]
     *
     * @return [integer] [returns seller id]
     */
    public function getSellerIdFromMpassign($assignId)
    {
        $sellerId = 0;
        $model = $this->_objectManager->get(
            'Webkul\MpAssignProduct\Model\Items'
        )->load($assignId);
        if ($model->getSellerId()) {
            $sellerId = $model->getSellerId();
        }

        return $sellerId;
    }

    public function addQuoteToVirtualCart()
    {
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

            /*if ($productType=="configurable" && $item->getHasChildren()) {
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $attributesData['super_attribute'] = $options['info_buyRequest']['super_attribute'];
                if(isset($options['info_buyRequest']['options'])){
                    $attributesData['options'] = $options['info_buyRequest']['options'];
                }
            } elseif ($productType=="bundle" && $item->getHasChildren()) {
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $bundleOption['selected_configurable_option'] = $options['info_buyRequest']['selected_configurable_option'];
                if(isset($options['info_buyRequest']['bundle_option']))
                    $bundleOption['bundle_option'] = $options['info_buyRequest']['bundle_option'];
                if(isset($options['info_buyRequest']['bundle_option_qty']))
                    $bundleOption['bundle_option_qty'] = $options['info_buyRequest']['bundle_option_qty'];
            } elseif (($productType=="virtual" || $productType=="simple")) {
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                if (isset($options['info_buyRequest']['options'])) {
                    $attributesData['options'] = $options['info_buyRequest']['options'];
                }
            } elseif (($productType=="downloadable")) {
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                if (isset($options['info_buyRequest']['options'])) {
                    $attributesData['options'] = $options['info_buyRequest']['options'];
                }
                if (isset($options['info_buyRequest']['links'])) {
                    $attributesData['links'] = $options['info_buyRequest']['links'];
                }
            }*/

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
        $this->setVirtualCart($virtualCart);
    }

    public function updateCart()
    {
        $quote = $this->_cart->getQuote();
        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $quote->save();
        $this->setWkCartWasUpdated();
    }

    /**
     * isModuleEnabled checks a given module is enabled or not
     *
     * @param  string $moduleName
     * @return boolean
     */
    public function isModuleEnabled($moduleName)
    {
        return $this->_moduleManager->isEnabled($moduleName);
    }

    /**
     * isOutputEnabled checks a given module is enabled or not
     *
     * @param  string $moduleName
     * @return boolean
     */
    public function isOutputEnabled($moduleName)
    {
        return $this->_moduleManager->isOutputEnabled($moduleName);
    }

    public function checkMpsplitcartStatus()
    {
        $moduleEnabled = $this->isModuleEnabled('Webkul_Mpsplitcart');
        $moduleOutputEnabled = $this->isOutputEnabled('Webkul_Mpsplitcart');
        if ($this->getEnableSplitcartSettings()
            && $moduleEnabled
            && $moduleOutputEnabled
        ) {
            return true;
        } else {
            return false;
        }
    }
}
