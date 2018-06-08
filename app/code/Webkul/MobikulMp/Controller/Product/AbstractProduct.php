<?php
    /**
     * Webkul Software.
     *
     * @category  Webkul
     * @package   Webkul_MobikulMp
     * @author    Webkul
     * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
     * @license   https://store.webkul.com/license.html
     */

    namespace Webkul\MobikulMp\Controller\Product;
    use Magento\Framework\App\Action\Context;

    abstract class AbstractProduct extends \Webkul\Mobikul\Controller\ApiController     {

        protected $_helper;
        protected $_product;
        protected $_protected;
        protected $_productUrl;
        protected $_entityModel;
        protected $_categoryTree;
        protected $_attributeModel;
        protected $_marketplaceHelper;

        public function __construct(
            Context $context,
            \Webkul\Mobikul\Helper\Data $helper,
            \Magento\Eav\Model\Entity $entityModel,
            \Magento\Catalog\Model\Product $product,
            \Magento\Catalog\Model\Category $category,
            \Magento\Catalog\Model\Product\Url $productUrl,
            \Webkul\Mobikul\Model\Category\Tree $categoryTree,
            \Webkul\Marketplace\Helper\Data $marketplaceHelper,
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeModel
        ) {
            $this->_helper            = $helper;
            $this->_product           = $product;
            $this->_protected         = $protected;
            $this->_productUrl        = $productUrl;
            $this->_entityModel       = $entityModel;
            $this->_categoryTree      = $categoryTree;
            $this->_attributeModel    = $attributeModel;
            $this->_marketplaceHelper = $marketplaceHelper;
            parent::__construct($helper, $context);
        }

    }