<?php
namespace Webkul\Mobikul\Helper\Catalog;

/**
 * Proxy class for @see \Webkul\Mobikul\Helper\Catalog
 */
class Proxy extends \Webkul\Mobikul\Helper\Catalog implements \Magento\Framework\ObjectManager\NoninterceptableInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Proxied instance name
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Proxied instance
     *
     * @var \Webkul\Mobikul\Helper\Catalog
     */
    protected $_subject = null;

    /**
     * Instance shareability flag
     *
     * @var bool
     */
    protected $_isShared = null;

    /**
     * Proxy constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param bool $shared
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Webkul\\Mobikul\\Helper\\Catalog', $shared = true)
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
        $this->_isShared = $shared;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['_subject', '_isShared', '_instanceName'];
    }

    /**
     * Retrieve ObjectManager from global scope
     */
    public function __wakeup()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Clone proxied instance
     */
    public function __clone()
    {
        $this->_subject = clone $this->_getSubject();
    }

    /**
     * Get proxied instance
     *
     * @return \Webkul\Mobikul\Helper\Catalog
     */
    protected function _getSubject()
    {
        if (!$this->_subject) {
            $this->_subject = true === $this->_isShared
                ? $this->_objectManager->get($this->_instanceName)
                : $this->_objectManager->create($this->_instanceName);
        }
        return $this->_subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentStoreId()
    {
        return $this->_getSubject()->getCurrentStoreId();
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeInputType($attribute)
    {
        return $this->_getSubject()->getAttributeInputType($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function _renderRangeLabel($fromPrice, $toPrice, $storeId)
    {
        return $this->_getSubject()->_renderRangeLabel($fromPrice, $toPrice, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceFilter($priceFilterModel, $storeId)
    {
        return $this->_getSubject()->getPriceFilter($priceFilterModel, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeFilter($attributeFilterModel, $_filter)
    {
        return $this->_getSubject()->getAttributeFilter($attributeFilterModel, $_filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryArray($queryStringArray)
    {
        return $this->_getSubject()->getQueryArray($queryStringArray);
    }

    /**
     * {@inheritdoc}
     */
    public function getIfTaxIncludeInPrice()
    {
        return $this->_getSubject()->getIfTaxIncludeInPrice();
    }

    /**
     * {@inheritdoc}
     */
    public function getOneProductRelevantData($product, $storeId, $width, $customerId = 0)
    {
        return $this->_getSubject()->getOneProductRelevantData($product, $storeId, $width, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreData()
    {
        return $this->_getSubject()->getStoreData();
    }

    /**
     * {@inheritdoc}
     */
    public function stripTags($data)
    {
        return $this->_getSubject()->stripTags($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleCodes($store)
    {
        return $this->_getSubject()->getLocaleCodes($store);
    }

    /**
     * {@inheritdoc}
     */
    public function showOutOfStock()
    {
        return $this->_getSubject()->showOutOfStock();
    }

    /**
     * {@inheritdoc}
     */
    public function getPageSize()
    {
        return $this->_getSubject()->getPageSize();
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceRangeCalculation()
    {
        return $this->_getSubject()->getPriceRangeCalculation();
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxQueryLength()
    {
        return $this->_getSubject()->getMaxQueryLength();
    }

    /**
     * {@inheritdoc}
     */
    public function formatDate($date, $format = null)
    {
        return $this->_getSubject()->formatDate($date, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function escapeHtml($text)
    {
        return $this->_getSubject()->escapeHtml($text);
    }

    /**
     * {@inheritdoc}
     */
    public function getBasePath($folder = 'media')
    {
        return $this->_getSubject()->getBasePath($folder);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageUrl($product, $resize, $imageType = 'product_page_image_large', $keepFrame = true)
    {
        return $this->_getSubject()->getImageUrl($product, $resize, $imageType, $keepFrame);
    }

    /**
     * {@inheritdoc}
     */
    public function resizeNCache($basePath, $newPath, $width, $height, $forCustomer = false)
    {
        return $this->_getSubject()->resizeNCache($basePath, $newPath, $width, $height, $forCustomer);
    }

    /**
     * {@inheritdoc}
     */
    public function isModuleOutputEnabled($moduleName = null)
    {
        return $this->_getSubject()->isModuleOutputEnabled($moduleName);
    }
}
