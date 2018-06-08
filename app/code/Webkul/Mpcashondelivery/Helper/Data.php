<?php

/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpcashondelivery
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Mpcashondelivery\Helper;

use Webkul\Mpcashondelivery\Model\PricerulesFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var Magento\Customer\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
     /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalogProduct;

    /**
     * @var _pricerulesFactory
     */
    protected $_pricerulesFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context                                   $context
     * @param \Magento\Framework\ObjectManagerInterface                               $objectManager
     * @param \Magento\Customer\Model\Session                                         $customerSession
     * @param \Magento\Checkout\Model\Session                                         $checkoutSession
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product                            $product
     * @param \Magento\Store\Model\StoreManagerInterface                              $storeManager
     * @param \Magento\Directory\Model\Currency                                       $currency
     * @param \Magento\Framework\Locale\CurrencyInterface                             $localeCurrency
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface                       $priceCurrency
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $product,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ProductFactory $catalogProduct,
        PricerulesFactory $pricerulesFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->product = $product;
        $this->_checkoutSession = $checkoutSession;
        $this->_localeCurrency = $localeCurrency;
        parent::__construct($context);
        $this->_currency = $currency;
        $this->_storeManager = $storeManager;
        $this->_priceCurrency = $priceCurrency;
        $this->_catalogProduct = $catalogProduct;
        $this->_pricerulesFactory = $pricerulesFactory;
        $this->_mpHelper = $mpHelper;
    }

    // check whether seller has right to update pricerule or not
    public function isRightSeller($id = '')
    {
        $data = 0;
        $model = $this->_pricerulesFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('entity_id', $id)
                    ->addFieldToFilter('seller_id', $this->getCustomerId());
        foreach ($model as $value) {
            $data = 1;
        }

        return $data;
    }

    // check whether or not seller has right to update product
    public function isRightSellerforProduct($id = '')
    {
        $data = 0;
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                    ->getCollection()
                    ->addFieldToFilter('mageproduct_id', $id)
                    ->addFieldToFilter('seller_id', $this->getCustomerId());
        foreach ($model as $value) {
            $data = 1;
        }

        return $data;
    }

    // get currency customer Id
    public function getCustomerId()
    {
        return $this->_mpHelper->getCustomerId();
    }

    // check cod available for pwrticular weight and seller or not
    public function getCodAvailablility($weight, $sellerId)
    {
        $result = 0;
        $mpcodRates = $this->_pricerulesFactory->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', ['eq' => $sellerId])
            ->addFieldToFilter('weight_from', ['lteq' => $weight])
            ->addFieldToFilter('weight_to', ['gteq' => $weight]);
        if (count($mpcodRates)) {
            $result = 1;
        }

        return $result;
    }

    public function getProduct($productId)
    {
        $product = $this->_catalogProduct->create()->load($productId);
        return $product;
    }

    // get codAvailable product attribute value
    public function getProductCodAvailable($productId = '')
    {
        $product = $this->_catalogProduct->create()->load($productId);
        return $product->getCodAvailable();
    }

    // get value of mpcashon delivery payment method active field
    public function getCodEnable()
    {
        return  $this->scopeConfig->getValue(
            'payment/mpcashondelivery/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    // get system config marketplace cash on delivery description field value
    public function getCodDescription()
    {
        return  $this->scopeConfig->getValue(
            'payment/mpcashondelivery/coddescription',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    // get system config marketplace cash on delivery new order status field value
    public function getCodOrderStatus()
    {
        $status = $this->scopeConfig->getValue(
            'payment/mpcashondelivery/order_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$status) {
            return 'pending';
        }
        return $status;
    }

    // get max order total value available on marketplace cash on delivery module
    public function getMaxTotal()
    {
        return  $this->scopeConfig->getValue(
            'payment/mpcashondelivery/maxtotal',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    // get min order total value of mpcod system config field
    public function getMinTotal()
    {
        return  $this->scopeConfig->getValue(
            'payment/mpcashondelivery/mintotal',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    // get specific country field value of mpcod
    public function getSpecificCountry()
    {
        return  $this->scopeConfig->getValue(
            'payment/mpcashondelivery/specificcountry',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    // get allow country field value of mpcod system config
    public function getAllowCountry()
    {
        return  $this->scopeConfig->getValue(
            'payment/mpcashondelivery/allowspecific',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    // get payment amount title field value
    public function getPymentAnountTitle()
    {
        return  $this->scopeConfig->getValue(
            'payment/mpcashondelivery/paymentamounttitle',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    // get payment title field value
    public function getPaymentTitle()
    {
        return  $this->scopeConfig->getValue(
            'payment/mpcashondelivery/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    // calculate price according to customer's address
    public function getAppliedPriceRules()
    {
        $handling = 0;
        $session = $this->_checkoutSession;
        if ($session->getQuote()->getShippingAddress()) {
            $postcode = $session->getQuote()->getShippingAddress()->getPostcode();
            $countrycode = $session->getQuote()->getShippingAddress()->getCountry();
            $destRegionId = $session->getQuote()->getShippingAddress()->getRegionId();
            if ($destRegionId == null || $destRegionId == '') {
                $destRegionId = '*';
            }
            $postcode = str_replace('-', '', $postcode);
            $mpcodSellerColl = [];
            $mpcodBuyeraddressColl = [
                'countrycode' => $countrycode,
                'postalcode' => $postcode,
                'regionId' => $destRegionId,
            ];

            $codAvailableError = '';
            foreach ($session->getQuote()->getAllVisibleItems() as $item) {
                $proid = $item->getProductId();
                $codAvailable = $this->getProductCodAvailable($proid);
                if ($codAvailable) {
                    $itemOption = $this->_objectManager
                    ->create('Magento\Quote\Model\Quote\Item\Option')
                    ->getCollection()
                    ->addFieldToFilter('item_id', ['eq' => $item->getId()])
                    ->addFieldToFilter('code', ['eq' => 'info_buyRequest']);
                     $optionValue = '';
                    if (count($itemOption)) {
                        foreach ($itemOption as $value) {
                            $optionValue = $value->getValue();
                        }
                    }
                    $mpassignproductId = '';
                    if ($optionValue != '') {
                        $temp = $optionValue;
                        $mpassignproductId = isset($temp['mpassignproduct_id']) ? $temp['mpassignproduct_id'] : 0;
                    }
                    $partner = '';
                    if ($mpassignproductId) {
                        $mpassignModel = $this->_objectManager
                                        ->create('Webkul\MpAssignProduct\Model\Items')
                                        ->load($mpassignproductId);
                        $partner = $mpassignModel->getSellerId();
                    } else {
                        $collection = $this->_objectManager
                                    ->create('Webkul\Marketplace\Model\Product')
                                    ->getCollection()
                                    ->addFieldToFilter('mageproduct_id', $proid);
                        foreach ($collection as $temp) {
                            $partner = $temp->getSellerId();
                        }
                        if ($partner == '') {
                            $partner = 0;
                        }
                    }
                    if (!isset($mpcodSellerColl[$partner])) {
                        $mpcodSellerColl[$partner] = [];
                    }
                    if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                        if ($codAvailableError == '') {
                            $codAvailableError = $item->getName();
                        } else {
                            $codAvailableError = $codAvailableError.','.$item->getName();
                        }
                        continue;
                    }
                    $childWeight = 0;
                    $childPrice = 0;
                    if ($item->getHasChildren()) {
                        $_product = $this->getProduct($item->getProductId());
                        if ($_product->getTypeId() == 'bundle') {
                            foreach ($item->getChildren() as $child) {
                                $childProduct = $this->getProduct($child->getProductId());
                                $productWeight = $child->getWeight();
                                $childWeight += $productWeight * $child->getQty();
                                $childPrice += $childProduct->getPrice();
                            }
                            $price = $childPrice * $item->getQty();
                            $weight = $childWeight * $item->getQty();
                        } elseif ($_product->getTypeId() == 'configurable') {
                            foreach ($item->getChildren() as $child) {
                                $childProduct = $this->getProduct($child->getProductId());
                                $productWeight = $childProduct->getWeight();
                                $price = $childProduct->getPrice() * $item->getQty();
                                $weight = $productWeight * $item->getQty();
                            }
                        }
                    } else {
                        $simpleproduct = $this->getProduct($proid);
                        $productWeight = $simpleproduct->getWeight();
                        $weight = $productWeight * $item->getQty();
                        if ($mpassignproductId) {
                            $mpassignModel = $this->_objectManager->create('Webkul\MpAssignProduct\Model\Items')
                            ->load($mpassignproductId);
                            $price = $mpassignModel->getPrice() * $item->getQty();
                        } else {
                            $price = $simpleproduct->getPrice() * $item->getQty();
                        }
                    }
                    array_push(
                        $mpcodSellerColl[$partner],
                        [
                            'items_weight' => $weight,
                            'items_price' => $price,
                            'items_name' => $item->getName(),
                            'item_id' => $proid
                        ]
                    );
                } else {
                    if ($codAvailableError == '') {
                        $codAvailableError = $item->getName();
                    } else {
                        $codAvailableError = $codAvailableError.','.$item->getName();
                    }
                }
            }
            if ($codAvailableError == '') {
                $codpricedetail = $this->getPricedetail(
                    $mpcodSellerColl,
                    $mpcodBuyeraddressColl
                );
                if ($codpricedetail['errormsg'] !== '') {
                    $this->_checkoutSession->setPaymentCustomError($codpricedetail['errormsg']);
                    $codMessage = $codpricedetail['errormsg'];

                    return ['cod_message' => $codMessage,'error' => 1,'handlingfee' => 0];
                } else {
                    $currencycode = $this->getCurrencySymbol($this->getCurrentCurrencyCode());
                    $codMessage = __(
                        'You will be charged an extra fee of %1',
                        $currencycode.$codpricedetail['handlingfee']
                    );

                    return [
                        'cod_message' => $codMessage,
                        'error' => 0,
                        'handlingfee' => $codpricedetail['handlingfee'],
                    ];
                }
            } else {
                $codMessage = __(
                    'COD is not available for product(s) %1 ,
                    so remove these product(s) to use COD as payment method',
                    $codAvailableError
                );
                return ['cod_message' => $codMessage,'error' => 1,'handlingfee' => 0];
            }
        } else {
            return ['cod_message' => 'Not calculated Yet','error' => 1,'handlingfee' => 0];
        }
    }

    // calculate price accordign to customer address and products
    public function getAppliedQuotePriceRules()
    {
        $handling = 0;
        $session = $this->_checkoutSession;
        if ($session->hasQuote() || $session->getQuote()) {
            if ($session->getQuote()->getShippingAddress()) {
                $postcode = $session->getQuote()->getShippingAddress()->getPostcode();
                $countrycode = $session->getQuote()->getShippingAddress()->getCountry();
                $regionId = $session->getQuote()->getShippingAddress()->getRegionId();
                if ($regionId == null || $regionId == '') {
                    $regionId = '*';
                }
                $postcode = str_replace('-', '', $postcode);
                $mpcodSellerColl = [];
                $mpcodBuyeraddressColl = [
                    'countrycode' => $countrycode,
                    'postalcode' => $postcode,
                    'regionId' => $regionId,
                ];
                $codAvailableError = '';
                foreach ($session->getQuote()->getAllVisibleItems() as $item) {
                    $proid = $item->getProductId();
                    $codAvailable = $this->getProduct($proid)->getCodAvailable();
                    if ($codAvailable) {
                            $itemOption = $this->_objectManager
                        ->create('Magento\Quote\Model\Quote\Item\Option')
                        ->getCollection()
                        ->addFieldToFilter('item_id', ['eq' => $item->getId()])
                        ->addFieldToFilter('code', ['eq' => 'info_buyRequest']);
                         $optionValue = '';
                        if (count($itemOption)) {
                            foreach ($itemOption as $value) {
                                $optionValue = $value->getValue();
                            }
                        }
                        if ($optionValue != '') {
                            $temp = $optionValue;
                            $mpassignproductId = isset($temp['mpassignproduct_id']) ? $temp['mpassignproduct_id'] : 0;
                        } else {
                            $mpassignproductId = '';
                        }
                        $partner = '';
                        if ($mpassignproductId) {
                            $mpassignModel = $this->_objectManager
                                            ->create('Webkul\MpAssignProduct\Model\Items')
                                            ->load($mpassignproductId);
                            $partner = $mpassignModel->getSellerId();
                        } else {
                            $collection = $this->_objectManager
                                        ->create('Webkul\Marketplace\Model\Product')
                                        ->getCollection()
                                        ->addFieldToFilter('mageproduct_id', $proid);
                            foreach ($collection as $temp) {
                                $partner = $temp->getSellerId();
                            }
                            if ($partner == '') {
                                $partner = 0;
                            }
                        }
                        if (!isset($mpcodSellerColl[$partner])) {
                            $mpcodSellerColl[$partner] = [];
                        }

                        if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                            if ($codAvailableError == '') {
                                $codAvailableError = $item->getName();
                            } else {
                                $codAvailableError = $codAvailableError.','.$item->getName();
                            }
                            continue;
                        }
                        $childWeight = 0;
                        $childPrice = 0;
                        if ($item->getHasChildren()) {
                            $_product = $this->getProduct($item->getProductId());
                            if ($_product->getTypeId() == 'bundle') {
                                foreach ($item->getChildren() as $child) {
                                    $childProduct = $this->getProduct($child->getProductId());
                                    $productWeight = $child->getWeight();
                                    $childWeight += $productWeight * $child->getQty();
                                    $childPrice += $childProduct->getPrice();
                                }
                                $price = $childPrice * $item->getQty();
                                $weight = $childWeight * $item->getQty();
                            } elseif ($_product->getTypeId() == 'configurable') {
                                foreach ($item->getChildren() as $child) {
                                    $childProduct = $this->getProduct($child->getProductId());
                                    $productWeight = $childProduct->getWeight();
                                    $price = $childProduct->getPrice() * $item->getQty();
                                    $weight = $productWeight * $item->getQty();
                                }
                            }
                        } else {
                            $simpleproduct = $this->getProduct($proid);
                            $productWeight = $simpleproduct->getWeight();
                            $weight = $productWeight * $item->getQty();
                            $price = $simpleproduct->getPrice() * $item->getQty();
                        }
                        array_push(
                            $mpcodSellerColl[$partner],
                            [
                                'items_weight' => $weight,
                                'items_price' => $price,
                                'items_name' => $item->getName(),
                                'item_id' => $proid
                            ]
                        );
                    } else {
                        if ($codAvailableError == '') {
                            $codAvailableError = $item->getName();
                        } else {
                            $codAvailableError = $codAvailableError.','.$item->getName();
                        }
                    }
                }
                if ($codAvailableError == '') {
                    $codpricedetail = $this->getPricedetail(
                        $mpcodSellerColl,
                        $mpcodBuyeraddressColl
                    );
                    if ($codpricedetail['errormsg'] !== '') {
                        return 0;
                    } else {
                        /*store cod in session*/
                        $this->_checkoutSession->setSellerCodInfo(
                            $codpricedetail['seller_cod_info']
                        );

                        return $codpricedetail['handlingfee'];
                    }
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        }
    }

    // calculate price
    public function getPricedetail($codSellerCollection, $codBuyerAddressCollection)
    {
        $sellerCodInfoArrays = [];
        $msg = '';
        $handling = 0;
        $codAvailableError = '';
        $codMessage = '';
        $price = 0;
        foreach ($codSellerCollection as $key => $value) {
            $sellerCodInfoArray = [];
            $sellerId = $key;
            foreach ($value as $key1 => $value1) {
                if (!is_numeric($codBuyerAddressCollection['postalcode'])) {
                    $codRatesCollection = $this->_pricerulesFactory
                        ->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'dest_country_id',
                            [
                                'eq' => $codBuyerAddressCollection['countrycode']
                            ]
                        )
                        ->addFieldToFilter(
                            'dest_region_id',
                            $codBuyerAddressCollection['regionId']
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            ['eq' => $sellerId]
                        )
                        ->addFieldToFilter(
                            'zipcode',
                            [
                                'eq' => $codBuyerAddressCollection['postalcode']
                            ]
                        );
                } else {
                    $codRatesCollection = $this->_pricerulesFactory
                        ->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'dest_country_id',
                            [
                                'eq' => $codBuyerAddressCollection['countrycode']
                            ]
                        )
                        ->addFieldToFilter('dest_region_id', $codBuyerAddressCollection['regionId'])
                        ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                        ->addFieldToFilter(
                            'zipcode',
                            [
                                'eq' => $codBuyerAddressCollection['postalcode']
                            ]
                        );
                    if (count($codRatesCollection) == 0) {
                        $codRatesCollection = $this->_pricerulesFactory
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'dest_country_id',
                                [
                                    'eq' => $codBuyerAddressCollection['countrycode']
                                ]
                            )
                            ->addFieldToFilter('dest_region_id', $codBuyerAddressCollection['regionId'])
                            ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                            ->addFieldToFilter(
                                'dest_zip_from',
                                [
                                    'lteq' => intval($codBuyerAddressCollection['postalcode'])
                                ]
                            )
                            ->addFieldToFilter(
                                'dest_zip_to',
                                [
                                    'gteq' => intval($codBuyerAddressCollection['postalcode'])
                                ]
                            );
                        if (count($codRatesCollection) == 0) {
                            $codRatesCollection = $this->_pricerulesFactory
                                ->create()
                                ->getCollection()
                                ->addFieldToFilter(
                                    'dest_country_id',
                                    [
                                        'eq' => $codBuyerAddressCollection['countrycode']
                                    ]
                                )
                                ->addFieldToFilter(
                                    'dest_region_id',
                                    [
                                        'eq' => $codBuyerAddressCollection['regionId'],
                                    ]
                                )
                                ->addFieldToFilter(
                                    'seller_id',
                                    ['eq' => $sellerId]
                                )
                                ->addFieldToFilter('dest_zip_from', ['eq' => '*'])
                                ->addFieldToFilter('dest_zip_to', ['eq' => '*']);
                            if (count($codRatesCollection) == 0) {
                                $codRatesCollection = $this->_pricerulesFactory
                                    ->create()
                                    ->getCollection()
                                    ->addFieldToFilter(
                                        'dest_country_id',
                                        [
                                            'eq' => $codBuyerAddressCollection['countrycode']
                                        ]
                                    )
                                    ->addFieldToFilter('dest_region_id', ['eq' => '*'])
                                    ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                                    ->addFieldToFilter('dest_zip_from', ['eq' => '*'])
                                    ->addFieldToFilter('dest_zip_to', ['eq' => '*']);
                                if (count($codRatesCollection) == 0) {
                                    $codRatesCollection = $this->_pricerulesFactory
                                        ->create()
                                        ->getCollection()
                                        ->addFieldToFilter(
                                            'dest_country_id',
                                            [
                                                'eq' => $codBuyerAddressCollection['countrycode']
                                            ]
                                        )
                                        ->addFieldToFilter('dest_region_id', ['eq' => '*'])
                                        ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                                        ->addFieldToFilter(
                                            'dest_zip_from',
                                            [
                                                'lteq' => intval($codBuyerAddressCollection['postalcode'])
                                            ]
                                        )
                                        ->addFieldToFilter('dest_zip_to', ['eq' => '*']);
                                    if (count($codRatesCollection) == 0) {
                                        $codRatesCollection = $this->_pricerulesFactory
                                            ->create()
                                            ->getCollection()
                                            ->addFieldToFilter(
                                                'dest_country_id',
                                                [
                                                    'eq' => $codBuyerAddressCollection['countrycode']
                                                ]
                                            )
                                            ->addFieldToFilter('dest_region_id', ['eq' => '*'])
                                            ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                                            ->addFieldToFilter('dest_zip_from', ['eq' => '*'])
                                            ->addFieldToFilter(
                                                'dest_zip_to',
                                                [
                                                    'gteq' => intval($codBuyerAddressCollection['postalcode'])
                                                ]
                                            );
                                        if (count($codRatesCollection) == 0) {
                                            $codRatesCollection = $this->_pricerulesFactory
                                                ->create()
                                                ->getCollection()
                                                ->addFieldToFilter(
                                                    'dest_country_id',
                                                    [
                                                        'eq' => $codBuyerAddressCollection['countrycode']
                                                    ]
                                                )
                                                ->addFieldToFilter(
                                                    'dest_region_id',
                                                    [
                                                        'eq' => $codBuyerAddressCollection['regionId']
                                                    ]
                                                )
                                                ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                                                ->addFieldToFilter('dest_zip_from', ['eq' => '*'])
                                                ->addFieldToFilter(
                                                    'dest_zip_to',
                                                    [
                                                        'gteq' => intval($codBuyerAddressCollection['postalcode'])
                                                    ]
                                                );
                                            if (count($codRatesCollection) == 0) {
                                                $codRatesCollection = $this->_pricerulesFactory
                                                    ->create()
                                                    ->getCollection()
                                                    ->addFieldToFilter(
                                                        'dest_country_id',
                                                        [
                                                            'eq' => $codBuyerAddressCollection['countrycode']
                                                        ]
                                                    )
                                                    ->addFieldToFilter(
                                                        'dest_region_id',
                                                        [
                                                            'eq' => $codBuyerAddressCollection['regionId']
                                                        ]
                                                    )
                                                    ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                                                    ->addFieldToFilter(
                                                        'dest_zip_from',
                                                        [
                                                            'lteq' => intval($codBuyerAddressCollection['postalcode'])
                                                        ]
                                                    )
                                                    ->addFieldToFilter('dest_zip_to', ['eq' => '*']);
                                                if (count($codRatesCollection) == 0) {
                                                    $codRatesCollection = $this->_pricerulesFactory
                                                        ->create()
                                                        ->getCollection()
                                                        ->addFieldToFilter(
                                                            'dest_country_id',
                                                            [
                                                                'eq' => $codBuyerAddressCollection['countrycode']
                                                            ]
                                                        )
                                                        ->addFieldToFilter(
                                                            'dest_region_id',
                                                            [
                                                                'eq' => '*'
                                                            ]
                                                        )
                                                        ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                                                        ->addFieldToFilter(
                                                            'dest_zip_from',
                                                            [
                                                                'lteq' => intval(
                                                                    $codBuyerAddressCollection['postalcode']
                                                                )
                                                            ]
                                                        )
                                                        ->addFieldToFilter(
                                                            'dest_zip_to',
                                                            ['gteq' => intval($codBuyerAddressCollection['postalcode'])]
                                                        );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                foreach ($codRatesCollection as $rates) {
                    if ($rates->getPriceType()) {
                        $percentage = $rates->getPercentagePrice();
                        $price = ($value1['items_price'] * $percentage) / 100;
                    } else {
                        $price = $rates->getFixedPrice();
                    }
                    $sellerCodInfoArray[$value1['item_id']] = $price;
                }
                foreach ($codRatesCollection as $key => $value) {
                    $fWeight = $value->getWeightFrom();
                    $tWeight = $value->getWeightTo();
                    if ($fWeight >= $value1['items_weight']) {
                        $codMessage = __(
                            'COD is not available for product(s) <b>%1</b> due to less weight, so remove these 
                            product(s) to use COD as payment method <br/>',
                            $value1['items_name']
                        );
                    } elseif ($tWeight <= $value1['items_weight']) {
                        $codMessage = __(
                            'COD is not available for product(s) <b>%1</b> due to excess weight, so remove these 
                            product(s) to use COD as payment method <br/>',
                            $value1['items_name']
                        );
                    }
                }
                if (count($codRatesCollection) == 0) {
                    if ($codAvailableError == '') {
                        $codAvailableError = $value1['items_name'];
                    } else {
                        $codAvailableError = $codAvailableError.','.$value1['items_name'];
                    }
                    $codMessage = __(
                        'COD is not available for product(s) <b>%1</b> ,
                        so remove these product(s) to use COD as payment method <br/>',
                        $codAvailableError
                    );
                }
                $handling = $handling + $price;
            }
            array_push(
                $sellerCodInfoArrays,
                [
                    'seller_id' => $sellerId,
                    'codinfo' => $sellerCodInfoArray
                ]
            );
        }
        $msg = $codMessage;

        return [
            'handlingfee' => $this->getConvertedAmount($handling),
            'seller_cod_info' => $sellerCodInfoArrays,
            'errormsg' => $msg
        ];
    }

    // get currency symbol by currency code
    public function getCurrencySymbol($currencycode)
    {
        $currency = $this->_localeCurrency->getCurrency($currencycode);

        return $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
    }

    // get currency currency code
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    // get base currency code
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    // convert amount from $from currenct to $to currency
    public function convertCurrency($amount, $from, $to)
    {
        $finalAmount = $this->_objectManager
            ->create('Magento\Directory\Helper\Data')
            ->currencyConvert($amount, $from, $to);

        return $finalAmount;
    }

    // get Base currency amount
    public function baseCurrencyAmount($amount, $store)
    {
        if ($store == null) {
            $store = $this->_storeManager->getStore()->getStoreId();
        }
        $rate = $this->_priceCurrency->convert($amount, $store) / $amount;
        $amount = $amount / $rate;

        return $this->_priceCurrency->round($amount);
    }
    public function getConvertedAmount($amount)
    {
        $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceCurrencyObject = $objectManager->get('Magento\Framework\Pricing\PriceCurrencyInterface');
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $store = $storeManager->getStore()->getStoreId();
        $rate = $priceCurrencyObject->convert($amount, $store, $currency);
        return $rate;
    }
}
