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

use \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;

class Mpproducts extends \Magento\Framework\View\Element\Template
{
    protected $_productCollection;
    protected $_productModel;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;
    /**
     * @var Session
     */
    protected $_customerSession;
    /**
     * @var \_mpcodproductCollection
     */
    protected $_mpcodproductCollection;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_mageProductCollection;
    /**
     * @var \Webkul\Mpcashondelivery\Helper\Data
     */
    protected $_mpcodHelper;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;
    
    /**
     * @param \Magento\Catalog\Block\Product\Context                         $context
     * @param CollectionFactory                                              $productCollection
     * @param FilterProvider                                                 $filterProvider
     * @param Session                                                        $_customerSession
     * @param \Magento\Catalog\Model\Product                                 $productModel
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $mageProductCollection
     * @param array                                                          $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        CollectionFactory $productCollection,
        FilterProvider $filterProvider,
        Session $_customerSession,
        \Magento\Catalog\Model\ProductFactory $productModel,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $mageProductCollection,
        \Webkul\Mpcashondelivery\Helper\Data $mpcodHelper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        array $data = []
    ) {
        $this->_productCollection = $productCollection;
        $this->_filterProvider = $filterProvider;
        $this->_customerSession = $_customerSession;
        $this->_productModel = $productModel;
        $this->_mageProductCollection = $mageProductCollection;
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
        if ($this->getMpProductCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'mpcashondelivery.pager'
            )
            ->setCollection(
                $this->getMpProductCollection()
            );
            $this->setChild('pager', $pager);
            $this->getMpProductCollection()->load();
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
     * Prepare HTML content
     *
     * @return string
     */
    public function getCmsFilterContent($value = '')
    {
        $html = $this->_filterProvider->getPageFilter()->filter($value);
        return $html;
    }
    public function getCustomerId()
    {
        return $this->_mpHelper->getCustomerId();
    }

    public function getMpProductCollection()
    {
        if (!$this->_mpcodproductCollection) {
            $mpProductCollection = $this->_productCollection
                    ->create()
                    ->addFieldToFilter('seller_id', $this->getCustomerId())
                    ->addFieldToSelect('mageproduct_id');
            $products = $mpProductCollection->getData();
            $mageProductCollection = $this->_mageProductCollection
                    ->create()
                    ->addFieldToFilter(
                        'entity_id',
                        ['in' => $products]
                    )
                    ->addFieldToFilter(
                        'type_id',
                        ['nin'=>['virtual','downloadable']]
                    );
            $this->_mpcodproductCollection = $mageProductCollection;
        }
        return $this->_mpcodproductCollection;
    }

    public function getProductData($productId)
    {
        $product = $this->_productModel->create()->load($productId);
        $productData = [];
        if ($product->getId()) {
            $productData['product_name'] = $product->getName();
            $productData['cod_status'] = $product->getCodAvailable();
        }
        return $productData;
    }
    public function getMpCodHelper()
    {
        return $this->_mpcodHelper;
    }
    public function getMpHelper()
    {
        return $this->_mpHelper;
    }
}
