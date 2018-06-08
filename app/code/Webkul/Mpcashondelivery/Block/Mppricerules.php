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
namespace Webkul\Mpcashondelivery\Block;

use Magento\Directory\Model\ResourceModel\Country;
use Webkul\Mpcashondelivery\Model\ResourceModel\Pricerules;
use Webkul\Marketplace\Model\ResourceModel\Seller;
use Webkul\Mpcashondelivery\Model\PricerulesFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;

class Mppricerules extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;
    /**
     * @var Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;
    /**
     * @var \Webkul\Mpcashondelivery\Model\ResourceModel\
     *                                                    Pricerules\CollectionFactory
     */
    protected $_pricerulesCollectionFactory;
    /**
     * @var Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory
     */
    protected $_sellerCollectionFactory;
    /**
     * @var \PricerulesCollection
     */
    protected $_priceruleCollection;
    /**
     * @var Webkul\Mpcashondelivery\Model\PricerulesFactory
     */
    protected $_priceruleFactory;
    /**
     * @var \Webkul\Mpcashondelivery\Helper\Data
     */
    protected $_mpcodHelper;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @param FilterProvider                         $filterProvider
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param Session                                $_customerSession
     * @param Country\CollectionFactory              $countryCollectionFactory
     * @param Pricerules\CollectionFactory           $pricerulesCollectionFactory
     * @param Seller\CollectionFactory               $sellerCollectionFactory
     * @param PricerulesFactory                      $pricerulesFactory
     * @param array                                  $data
     */
    public function __construct(
        FilterProvider $filterProvider,
        \Magento\Catalog\Block\Product\Context $context,
        Session $_customerSession,
        Country\CollectionFactory $countryCollectionFactory,
        Pricerules\CollectionFactory $pricerulesCollectionFactory,
        Seller\CollectionFactory $sellerCollectionFactory,
        PricerulesFactory $pricerulesFactory,
        \Webkul\Mpcashondelivery\Helper\Data $mpcodHelper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        array $data = []
    ) {
        $this->_pricerulesCollectionFactory = $pricerulesCollectionFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_filterProvider = $filterProvider;
        $this->_customerSession = $_customerSession;
        $this->_sellerCollectionFactory = $sellerCollectionFactory;
        $this->_priceruleFactory = $pricerulesFactory;
        $this->_mpcodHelper = $mpcodHelper;
        $this->_mpHelper = $mpHelper;
        parent::__construct($context, $data);
    }
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getPriceruleCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'mpcashondelivery.pager'
            )
            ->setCollection(
                $this->getPriceruleCollection()
            );
            $this->setChild('pager', $pager);
            $this->getPriceruleCollection()->load();
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
     * Prepare HTML content.
     *
     * @return string
     */
    public function getCmsFilterContent($value = '')
    {
        $html = $this->_filterProvider->getPageFilter()->filter($value);

        return $html;
    }
    /**
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    public function getCountryCollection()
    {
        $collection = $this->_countryCollectionFactory
                    ->create()->loadByStore();

        return $collection;
    }
    /**
     * Retrieve list of top destinations countries.
     *
     * @return array
     */
    protected function getTopDestinations()
    {
        $destinations = (string) $this->_scopeConfig->getValue(
            'general/country/destinations',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return !empty($destinations) ? explode(',', $destinations) : [];
    }
    /**
     * Retrieve list of countries option array.
     *
     * @return array
     */
    public function getCountryOptionArray()
    {
        $options = $this->getCountryCollection()
                ->setForegroundCountries($this->getTopDestinations())
                ->toOptionArray();
        $options[0]['label'] = 'Please select Country';

        return $options;
    }
    // get sellerOther information
    public function getsellerOthersInfo()
    {
        $collection = $this->_sellerCollectionFactory->create()
                    ->addFieldToFilter('seller_id', $this->getCustomerId());
        foreach ($collection as $key => $value) {
            return $value->getOthersInfo();
        }
    }
    // get Customer Id from session
    public function getCustomerId()
    {
        return $this->_mpHelper->getCustomerId();
    }
    // get pricerule collection of seller
    public function getPriceruleCollection()
    {
        if (!$this->_priceruleCollection) {
            $collection = $this->_pricerulesCollectionFactory
                    ->create()->addFieldToSelect('*')
                    ->addFieldToFilter('seller_id', $this->getCustomerId());
            $this->_priceruleCollection = $collection;
        }

        return $this->_priceruleCollection;
    }
    // return pricerule data by of particular pricerule
    public function getpriceruleData($priceruleId)
    {
        $priceruleModel = $this->_priceruleFactory->create()
                        ->load($priceruleId);

        return $priceruleModel;
    }
    public function getMpCodHelper()
    {
        return $this->_mpcodHelper;
    }
    public function getMpHelper()
    {
        return $this->_mpHelper;
    }
    public function getFormattedPrice($price)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
        $formattedPrice = $priceHelper->currency($price, true, false);
        return $formattedPrice;
    }
}
