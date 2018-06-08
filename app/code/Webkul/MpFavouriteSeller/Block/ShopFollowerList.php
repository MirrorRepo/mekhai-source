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
use Webkul\MpFavouriteSeller\Model\Mpfavouriteseller;

/**
 * Webkul MpFavouriteSeller ShopFollowerList Block
 */
class ShopFollowerList extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

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
     * @var Webkul\MpFavouriteSeller\Model\Mpfavouriteseller
     */
    protected $_mpFavouriteSellerModel;

    /**
     * @param \Magento\Cms\Model\Wysiwyg\Config                $wysiwygConfig
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager
     * @param MpfavouritesellerRepository                      $mpFavouriteSellerRepository
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Customer\Model\Customer                 $customerModel
     * @param Mphelper                                         $mpHelper
     * @param Seller                                           $mpSellerModel
     * @param Mpfavouriteseller                                $mpFavouriteSellerModel
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        MpfavouritesellerRepository $mpFavouriteSellerRepository,
        \Magento\Customer\Model\Customer $customerModel,
        Mphelper $mpHelper,
        Seller $mpSellerModel,
        Mpfavouriteseller $mpFavouriteSellerModel,
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpFavouriteSeller\Helper\Data $helper,
        array $data = []
    ) {
    
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_objectManager = $objectManager;
        $this->_mpHelper = $mpHelper;
        $this->_mpFavouriteSellerRepository = $mpFavouriteSellerRepository;
        $this->_customerModel = $customerModel;
        $this->_mpSellerModel = $mpSellerModel;
        $this->_mpFavouriteSellerModel = $mpFavouriteSellerModel;
        $this->_urlinterface = $context->getUrlBuilder();
        $this->helper = $helper;
        parent::__construct($context, $data);
    }
    
    /**
     * get seller followers list
     * @return object
     */
    public function getFollowerList()
    {

        $sellerId = $this->helper->getCurrentCustomer();
        $collection  = $this->_mpFavouriteSellerRepository
                            ->getCustomersCollectionBySellerId($sellerId);

        $paramData = $this->getRequest()->getParams();
        $filter = '';
        $filterDateFrom = '';
        $filterDateTo = '';
        $from = null;
        $to = null;

        if (isset($paramData['s'])) {
            $filter = $paramData['s'] != '' ? $paramData['s'] : '';
        }
        if (isset($paramData['from_date'])) {
            $filterDateFrom = $paramData['from_date'] != '' ? $paramData['from_date'] : '';
        }
        if (isset($paramData['to_date'])) {
            $filterDateTo = $paramData['to_date'] != '' ? $paramData['to_date'] : '';
        }
        if ($filterDateTo) {
            $todate = date_create($filterDateTo);
            $to = date_format($todate, 'Y-m-d 23:59:59');
        }
        if (!$to) {
            $to = date('Y-m-d 23:59:59');
        }
        if ($filterDateFrom) {
            $fromdate = date_create($filterDateFrom);
            $from = date_format($fromdate, 'Y-m-d H:i:s');
        }

        $eavAttribute = $this->_objectManager->get(
            'Magento\Eav\Model\ResourceModel\Entity\Attribute'
        );

        $firstName = $eavAttribute->getIdByCode("customer", "firstname");
        $lastName = $eavAttribute->getIdByCode("customer", "lastname");

        $customerEntity = $this->_objectManager->create(
            'Magento\Customer\Model\ResourceModel\Customer\Collection'
        )->getTable('customer_entity');

        $collection->getSelect()->join(
            $customerEntity.' as cpev',
            'main_table.customer_id = cpev.entity_id',
            ['firstname','lastname']
        )->where(
            "cpev.firstname like '%".$filter."%' OR 
            cpev.lastname like '%".$filter."%'"
        );

        if ($from && $to) {
            $collection->getSelect()->where(
                "main_table.liked_at BETWEEN '". $from ."' AND '". $to ."'"
            );
        }

        $collection->setOrder('entity_id', 'DESC');
          
        return $collection;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getFavouriteSellerList()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.product.list.pager'
            )->setCollection(
                $this->getFavouriteSellerList()
            );
            $this->setChild('pager', $pager);
            $this->getFavouriteSellerList()->load();
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

    /**
     * get from date
     * @return string
     */
    public function filterDateFromParam()
    {
        $paramData = $this->getRequest()->getParams();
        if (isset($paramData['from_date'])) {
            return $paramData['from_date'] != '' ? $paramData['from_date'] : '';
        }
        return false;
    }

    /**
     * get to date
     * @return string
     */
    public function filterDateToParam()
    {
        $paramData = $this->getRequest()->getParams();
        if (isset($paramData['to_date'])) {
            return $paramData['to_date'] != '' ? $paramData['to_date'] : '';
        }
        return false;
    }

    /**
     * get current session of customer
     * @return int
     */
    public function getCurrentCustomerId()
    {
        return $this->helper->getCurrentCustomer();
    }

    /**
     * get config of tiny nce editor
     */
    public function getWysiwygConfig()
    {
        $config = $this->_wysiwygConfig->getConfig();
        $config = json_encode($config->getData());
        return $config;
    }
}
