<?php
/**
 * @category   Webkul
 * @package    Webkul_MpFavouriteSeller
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpFavouriteSeller\Block;

use Webkul\Marketplace\Model\Seller;
use Webkul\Marketplace\Helper\Data as Mphelper;
use Webkul\MpFavouriteSeller\Model\MpfavouritesellerRepository;

/**
 * Webkul MpFavouriteSeller FavouriteSellerList Block
 */
class FavouriteSellerList extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Webkul\MpFavouriteSeller\Model\MpfavouritesellerRepository
     */
    protected $_mpFavouriteSellerRepository;

    /**
     * @var Magento\Customer\Model\Customer
     */
    protected $_customerModel;

    /**
     * @var Webkul\Marketplace\Model\Seller
     */
    protected $_mpSellerModel;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $_urlinterface;

    /**
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager
     * @param MpfavouritesellerRepository                      $mpFavouriteSellerRepository
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Customer\Model\Customer                 $customerModel
     * @param Mphelper                                         $mpHelper
     * @param Seller                                           $mpSellerModel
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        MpfavouritesellerRepository $mpFavouriteSellerRepository,
        \Magento\Customer\Model\Customer $customerModel,
        Mphelper $mpHelper,
        Seller $mpSellerModel,
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpFavouriteSeller\Helper\Data $helper,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_mpHelper = $mpHelper;
        $this->_mpFavouriteSellerRepository = $mpFavouriteSellerRepository;
        $this->_customerModel = $customerModel;
        $this->_mpSellerModel = $mpSellerModel;
        $this->_urlinterface = $context->getUrlBuilder();
        $this->helper = $helper;
        parent::__construct($context, $data);
    }
    
    public function getFavouriteSellerList()
    {
        $filter = $this->getRequest()->getParam('s')!=""?
                $this->getRequest()->getParam('s'):"";

        $customerId = $this->helper->getCurrentCustomer();
        $collection  = $this->_mpFavouriteSellerRepository
                            ->getsellersCollectionByCustomerId($customerId);

        $eavAttribute = $this->_objectManager->get(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute'
        );
        $firstName = $eavAttribute->getIdByCode("customer", "firstname");
        $lastName = $eavAttribute->getIdByCode("customer", "lastname");

        $customerEntity = $this->_objectManager->create(
            'Magento\Customer\Model\ResourceModel\Customer\Collection'
        )->getTable('customer_entity');

        $marketplaceUserdata = $this->_objectManager->create(
            'Webkul\Marketplace\Model\ResourceModel\Seller\Collection'
        )->getTable('marketplace_userdata');

        $collection->getSelect()->join(
            $customerEntity.' as cpev',
            'main_table.seller_id = cpev.entity_id',
            ['firstname','lastname']
        )->where(
            "cpev.firstname like '%".$filter."%' OR 
            cpev.lastname like '%".$filter."%'"
        );

        $collection->getSelect()->join(
            $marketplaceUserdata.' as mpud',
            'main_table.seller_id = mpud.seller_id',
            ['shop_url','logo_pic']
        );
        $collection->setOrder('entity_id', 'DESC');
        return $collection;
    }

    public function getWkFavouriteSellerList()
    {
        $filter = $this->getRequest()->getParam('s')!=""?
                $this->getRequest()->getParam('s'):"";
        
        $customerId = $this->helper->getCurrentCustomer();
        $collection  = $this->_mpFavouriteSellerRepository
                            ->getsellersCollectionByCustomerId($customerId);
        
        $eavAttribute = $this->_objectManager->get(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute'
        );
        $firstName = $eavAttribute->getIdByCode("customer", "firstname");
        $lastName = $eavAttribute->getIdByCode("customer", "lastname");

        $customerEntity = $this->_objectManager->create(
            'Magento\Customer\Model\ResourceModel\Customer\Collection'
        )->getTable('customer_entity');

        $marketplaceUserdata = $this->_objectManager->create(
            'Webkul\Marketplace\Model\ResourceModel\Seller\Collection'
        )->getTable('marketplace_userdata');

        $tableName = $collection->getTable('customer_grid_flat');

        $collection->getSelect()->join(
            $tableName.' as cgf',
            'main_table.seller_id = cgf.entity_id',
            [
                'name'=>'name',
            ]
        )->where(
            "cgf.name like '%".trim($filter)."%'"
        );

        $storeId = $this->_storeManager->getStore()->getId();

        $collection->getSelect()->join(
            $marketplaceUserdata.' as mpud',
            'main_table.seller_id = mpud.seller_id',
            ['shop_url','logo_pic','store_id']
        )->where(
            "mpud.store_id = '".$storeId."'"
        );
        // echo $collection->getSelect();

        // // $storeId = $this->_storeManager->getStore()->getId();
        // // $collection->addFieldToFilter('store_id', '1');
        // echo "<pre>";
        // print_r($this->_storeManager->getStore()->getId());
        // print_r($collection->getData());
        // die("line 166");

        $collection->getSelect()->group('main_table.seller_id');
        $collection->setOrder('entity_id', 'DESC');
        return $collection;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getWkFavouriteSellerList()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.product.list.pager'
            )->setCollection(
                $this->getWkFavouriteSellerList()
            );
            $this->setChild('pager', $pager);
            $this->getWkFavouriteSellerList()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * get customer name
     * @param  int $customerId contain customer id
     * @return String
     */
    public function getCustomerName($customerId)
    {
        return $this->_customerModel
                ->load($customerId)->getName();
    }

    /**
     * get seller logo image
     * @param  string $logoPic
     * @return string
     */
    public function getSellerLogo($logoPic)
    {
        if ($logoPic) {
            return $this->_mpHelper
            ->getMediaUrl().'avatar/'.$logoPic;
        } else {
            return $this->_mpHelper
            ->getMediaUrl().'avatar/noimage.png';
        }
        
    }

    /**
     * return collection url of seller
     * @param  string $shopUrl
     * @return string
     */
    public function getSellerCollectionUrl($shopUrl)
    {
        return $this->_mpHelper->getRewriteUrl('marketplace/seller/collection/shop/'.$shopUrl);
    }

    /**
     * return seller profile url
     * @param  string $shopUrl
     * @return string
     */
    public function getSellerProfileUrl($shopUrl)
    {
        return $this->_mpHelper->getRewriteUrl('marketplace/seller/profile/shop/'.$shopUrl);
    }

    /**
     * get current url
     * @return string
     */
    public function getCurrentSiteUrl()
    {
        return $this->_urlinterface->getCurrentUrl();
    }

    /**
     * get filter Params
     * @return string
     */
    public function getFilterParam()
    {
        $filter = $this->getRequest()->getParam('s')!=""?
                $this->getRequest()->getParam('s'):"";
        return $filter;
    }
}
