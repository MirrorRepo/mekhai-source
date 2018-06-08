<?php
namespace Magento\Catalog\Model\Product\Visibility;

/**
 * Proxy class for @see \Magento\Catalog\Model\Product\Visibility
 */
class Proxy extends \Magento\Catalog\Model\Product\Visibility implements \Magento\Framework\ObjectManager\NoninterceptableInterface
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
     * @var \Magento\Catalog\Model\Product\Visibility
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
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Magento\\Catalog\\Model\\Product\\Visibility', $shared = true)
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
     * @return \Magento\Catalog\Model\Product\Visibility
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
    public function getVisibleInCatalogIds()
    {
        return $this->_getSubject()->getVisibleInCatalogIds();
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibleInSearchIds()
    {
        return $this->_getSubject()->getVisibleInSearchIds();
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibleInSiteIds()
    {
        return $this->_getSubject()->getVisibleInSiteIds();
    }

    /**
     * {@inheritdoc}
     */
    public function getFlatColumns()
    {
        return $this->_getSubject()->getFlatColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getFlatIndexes()
    {
        return $this->_getSubject()->getFlatIndexes();
    }

    /**
     * {@inheritdoc}
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->_getSubject()->getFlatUpdateSelect($store);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($attribute)
    {
        return $this->_getSubject()->setAttribute($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute()
    {
        return $this->_getSubject()->getAttribute();
    }

    /**
     * {@inheritdoc}
     */
    public function addValueSortToCollection($collection, $dir = 'asc')
    {
        return $this->_getSubject()->addValueSortToCollection($collection, $dir);
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->_getSubject()->toOptionArray();
    }

    /**
     * {@inheritdoc}
     */
    public function addData(array $arr)
    {
        return $this->_getSubject()->addData($arr);
    }

    /**
     * {@inheritdoc}
     */
    public function setData($key, $value = null)
    {
        return $this->_getSubject()->setData($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetData($key = null)
    {
        return $this->_getSubject()->unsetData($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getData($key = '', $index = null)
    {
        return $this->_getSubject()->getData($key, $index);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataByPath($path)
    {
        return $this->_getSubject()->getDataByPath($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataByKey($key)
    {
        return $this->_getSubject()->getDataByKey($key);
    }

    /**
     * {@inheritdoc}
     */
    public function setDataUsingMethod($key, $args = array())
    {
        return $this->_getSubject()->setDataUsingMethod($key, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataUsingMethod($key, $args = null)
    {
        return $this->_getSubject()->getDataUsingMethod($key, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function hasData($key = '')
    {
        return $this->_getSubject()->hasData($key);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(array $keys = array())
    {
        return $this->_getSubject()->toArray($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToArray(array $keys = array())
    {
        return $this->_getSubject()->convertToArray($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function toXml(array $keys = array(), $rootName = 'item', $addOpenTag = false, $addCdata = true)
    {
        return $this->_getSubject()->toXml($keys, $rootName, $addOpenTag, $addCdata);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToXml(array $arrAttributes = array(), $rootName = 'item', $addOpenTag = false, $addCdata = true)
    {
        return $this->_getSubject()->convertToXml($arrAttributes, $rootName, $addOpenTag, $addCdata);
    }

    /**
     * {@inheritdoc}
     */
    public function toJson(array $keys = array())
    {
        return $this->_getSubject()->toJson($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToJson(array $keys = array())
    {
        return $this->_getSubject()->convertToJson($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function toString($format = '')
    {
        return $this->_getSubject()->toString($format);
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $args)
    {
        return $this->_getSubject()->__call($method, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return $this->_getSubject()->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($keys = array(), $valueSeparator = '=', $fieldSeparator = ' ', $quote = '"')
    {
        return $this->_getSubject()->serialize($keys, $valueSeparator, $fieldSeparator, $quote);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($data = null, &$objects = array())
    {
        return $this->_getSubject()->debug($data, $objects);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        return $this->_getSubject()->offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->_getSubject()->offsetExists($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        return $this->_getSubject()->offsetUnset($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->_getSubject()->offsetGet($offset);
    }
}
