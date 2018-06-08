<?php
/**
 * @category   Webkul
 * @package    Webkul_MpFavouriteSeller
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpFavouriteSeller\Block;

use Magento\Customer\Model\Customer;
use Webkul\Marketplace\Model\Seller;

/**
 * Webkul MpFavouriteSeller AddFavouriteSeller Block
 */
class AddFavouriteSeller extends \Magento\Framework\View\Element\Template
{

    /**
     * @var Magento\Customer\Model\Customer
     */
    protected $_customerModel;

    /**
     * @var Webkul\Marketplace\helper\Data
     */
    protected $_dataHelperMarketplace;

    /**
     * @var Webkul\Marketplace\Model\Seller
     */
    protected $_mpSellerModel;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var [type]
     */
    protected $_urlinterface;

    /**
     * @param Seller                                           $mpSellerModel
     * @param Customer                                         $customerModel
     * @param \Webkul\Marketplace\Helper\Data                  $dataHelperMarketplace
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        Seller $mpSellerModel,
        Customer $customerModel,
        \Webkul\Marketplace\Helper\Data $dataHelperMarketplace,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
    
        $this->_registry = $registry;
        $this->_mpSellerModel = $mpSellerModel;
        $this->_dataHelperMarketplace = $dataHelperMarketplace;
        $this->_customerModel = $customerModel;
        $this->_urlinterface = $context->getUrlBuilder();
        parent::__construct($context, $data);
    }

    /**
     * get Seller id by shop url
     * @return int
     */
    public function getSellerIdbyShopUrlOnProfile()
    {
        $shopUrl = $this->_dataHelperMarketplace->getProfileUrl();
        if (!$shopUrl) {
            $shopUrl = $this->getRequest()->getParam('shop');
        }
        $sellerCollection = $this->_mpSellerModel->getCollection()
        ->addFieldToFilter(
            'shop_url',
            $shopUrl
        );
        foreach ($sellerCollection as $seller) {
            $sellerId = $seller->getSellerId();
        }
        return $sellerId;
    }

    /**
     * get Seller id by shop url
     * @return int
     */
    public function getSellerIdbyShopUrlOnCollection()
    {
        $shopUrl = $this->_dataHelperMarketplace->getCollectionUrl();
        if (!$shopUrl) {
            $shopUrl = $this->getRequest()->getParam('shop');
        }
        $sellerCollection = $this->_mpSellerModel->getCollection()
        ->addFieldToFilter(
            'shop_url',
            $shopUrl
        );
        foreach ($sellerCollection as $seller) {
            $sellerId = $seller->getSellerId();
        }
        return $sellerId;
    }
    
    /**
     * get Seller id
     * @return int
     */
    public function getSellerIdOnProductView()
    {
        $_product = $this->_registry->registry('current_product');
        $mpProductCollection = $this->_dataHelperMarketplace
            ->getSellerProductDataByProductId(
                $_product->getId()
            );
        foreach ($mpProductCollection as $productMp) {
            $sellerId = $productMp->getSellerId();
        }
        return $sellerId;
    }

    /**
     * get link url
     * @return string
     */
    public function getFavouriteSellerLink()
    {
        $sellerId = '';
        $collectionArray = [];
        $profileArray =[];
        $currentUrl = $this->_urlinterface->getCurrentUrl();
        $targetUrl = $this->_dataHelperMarketplace->getTargetUrlPath();
        if ($targetUrl) {
            $collectionArray = explode('/collection/', $targetUrl);
            $profileArray = explode('/profile/', $targetUrl);
        } else {
            $collectionArray = explode('/collection/', $currentUrl);
            $profileArray = explode('/profile/', $currentUrl);
        }
        
        if (count($collectionArray) > 1) {
            $sellerId = $this->getSellerIdbyShopUrlOnCollection();
        }
        if (count($profileArray) > 1) {
            $sellerId = $this->getSellerIdbyShopUrlOnProfile();
        }
        if ($sellerId == '') {
            $sellerId =  $this->getSellerIdOnProductView();
        }
        $url = $this->getUrl(
            'mpfavouriteseller/favouriteseller/addfavouriteseller',
            [
                'seller_id'=>$sellerId
            ]
        );
        return $url;
    }

    public function getWkFavouriteSellerLink()
    {
        $sellerId = '';
        $collectionArray = [];
        $profileArray =[];
        $currentUrl = $this->_urlinterface->getCurrentUrl();
        $targetUrl = $this->_dataHelperMarketplace->getWkTargetUrlPath();
        if ($targetUrl) {
            $collectionArray = explode('/collection/', $targetUrl);
            $profileArray = explode('/profile/', $targetUrl);
        } else {
            $collectionArray = explode('/collection/', $currentUrl);
            $profileArray = explode('/profile/', $currentUrl);
        }
        
        if (count($collectionArray) > 1) {
            $sellerId = $this->getSellerIdbyShopUrlOnCollection();
        }
        if (count($profileArray) > 1) {
            $sellerId = $this->getSellerIdbyShopUrlOnProfile();
        }
        if ($sellerId == '') {
            $sellerId =  $this->getSellerIdOnProductView();
        }
        $url = $this->getUrl(
            'mpfavouriteseller/favouriteseller/addfavouriteseller',
            [
                'seller_id'=>$sellerId
            ]
        );
        return $url;
    }
}
