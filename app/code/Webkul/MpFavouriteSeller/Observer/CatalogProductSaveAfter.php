<?php
/**
 * @category   Webkul
 * @package    Webkul_MpFavouriteSeller
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */ 
namespace Webkul\MpFavouriteSeller\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpFavouriteSeller\Model\MpfavouritesellerRepository;
use Webkul\MpFavouriteSeller\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;

/**
 * Webkul MpFavouriteSeller CatalogProductSaveAfter Observer Model.
 */
class CatalogProductSaveAfter implements ObserverInterface
{
    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Webkul\MpFavouriteSeller\Model\MpfavouritesellerRepository
     */
    protected $_mpFavouritesellerRepository;

    /**
     * @var Magento\Catalog\Model\Product
     */
    protected $_productMageModel;

    /**
     * @var Magento\Catalog\Model\Category
     */
    protected $_categoryMageModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceMageHelper;

    /**
     * @param \Magento\Framework\Event\Manager           $eventManager
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param MpfavouritesellerRepository                $mpFavouritesellerRepository
     * @param Data                                       $helperData
     * @param Product                                    $productMageModel
     * @param Category                                   $categoryMageModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\Marketplace\Helper\Data            $mpHelper
     * @param \Magento\Framework\Pricing\Helper\Data     $priceMageHelper
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        MpfavouritesellerRepository $mpFavouritesellerRepository,
        Data $helperData,
        Product $productMageModel,
        Category $categoryMageModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Pricing\Helper\Data $priceMageHelper
    ) {
    
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_mpFavouritesellerRepository = $mpFavouritesellerRepository;
        $this->_helperData = $helperData;
        $this->_productMageModel = $productMageModel;
        $this->_categoryMageModel = $categoryMageModel;
        $this->_storeManager = $storeManager;
        $this->_mpHelper = $mpHelper;
        $this->_priceMageHelper = $priceMageHelper;
    }

    /**
     * MpFavouriteSeller product approve event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $sellerId = null;
        $product = $observer->getProduct();
        $sellerProduct = $this->_mpHelper->getSellerProductDataByProductId($product->getId());
        
        if ($sellerProduct->getSize()) {
            foreach($sellerProduct as $productData) {
                $sellerId = $productData->getSellerId();
                $productStatus = $productData->getStatus();
            }
        }
        if ($productStatus === '1') {
            $collection = $followersCollection = $this->_mpFavouritesellerRepository
                ->getCustomersCollectionBySellerId($sellerId);

            $seller = $this->_helperData->getCustomerData($sellerId);
            $product = $this->_productMageModel->load($product->getId());
            $categoryIds = $product->getCategoryIds();
            if (!empty($categoryIds)) {
                $firstCategoryId = $categoryIds[0];
            } else {
                $firstCategoryId='';
            }
            
            $_category = $this->_categoryMageModel->load($firstCategoryId);
            $productUrl = $product->getProductUrl();
            if (!empty($collection)) {
                foreach ($collection as $follower) {
                    $customer = $this->_helperData->getCustomerData($follower->getCustomerId());
                    $emailTempVariables = [];
                    
                    $userList = $this->_mpHelper
                            ->getSellerDataBySellerId($seller->getId());
                    foreach ($userList as $user) {
                        $shopUrl = $user->getShopUrl();
                    }

                    $senderInfo = [];
                    $receiverInfo = [];

                    $receiverInfo = [
                        'name' => $customer->getName(),
                        'email' => $customer->getEmail(),
                    ];
                    $senderInfo = [
                        'name' => $seller->getName(),
                        'email' =>$seller->getEmail(),
                    ];

                    $emailTempVariables['myvar1'] = $customer->getName();
                    $emailTempVariables['myvar2'] = $shopUrl;
                    $emailTempVariables['myvar3'] = $productUrl;
                    $emailTempVariables['myvar4'] = $product->getName();
                    $emailTempVariables['myvar5'] = $_category->getName();
                    $emailTempVariables['myvar6'] = $product->getDescription();
                    $emailTempVariables['myvar7'] = $this->_priceMageHelper
                                                    ->currency(
                                                        $product->getPrice(),
                                                        true,
                                                        false
                                                    );

                    $this->_helperData->followersNotifyProductApprove(
                        $emailTempVariables,
                        $senderInfo,
                        $receiverInfo
                    );
                }
            }
        }
    }
}
