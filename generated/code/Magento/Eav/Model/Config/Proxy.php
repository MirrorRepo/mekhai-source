<?php
namespace Magento\Eav\Model\Config;

/**
 * Proxy class for @see \Magento\Eav\Model\Config
 */
class Proxy extends \Magento\Eav\Model\Config implements \Magento\Framework\ObjectManager\NoninterceptableInterface
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
     * @var \Magento\Eav\Model\Config
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
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Magento\\Eav\\Model\\Config', $shared = true)
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
     * @return \Magento\Eav\Model\Config
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
    public function getCache()
    {
        return $this->_getSubject()->getCache();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->_getSubject()->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function isCacheEnabled()
    {
        return $this->_getSubject()->isCacheEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType($code)
    {
        return $this->_getSubject()->getEntityType($code);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($entityType)
    {
        return $this->_getSubject()->getAttributes($entityType);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($entityType, $code)
    {
        return $this->_getSubject()->getAttribute($entityType, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityAttributeCodes($entityType, $object = null)
    {
        return $this->_getSubject()->getEntityAttributeCodes($entityType, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityAttributes($entityType, $object = null)
    {
        return $this->_getSubject()->getEntityAttributes($entityType, $object);
    }

    /**
     * {@inheritdoc}
     */
    public function importAttributesData($entityType, array $attributes)
    {
        return $this->_getSubject()->importAttributesData($entityType, $attributes);
    }
}
