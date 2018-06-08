<?php
namespace Webkul\Mobikul\Helper\Data;

/**
 * Proxy class for @see \Webkul\Mobikul\Helper\Data
 */
class Proxy extends \Webkul\Mobikul\Helper\Data implements \Magento\Framework\ObjectManager\NoninterceptableInterface
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
     * @var \Webkul\Mobikul\Helper\Data
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
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Webkul\\Mobikul\\Helper\\Data', $shared = true)
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
     * @return \Webkul\Mobikul\Helper\Data
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
    public function getPassword()
    {
        return $this->_getSubject()->getPassword();
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($dir)
    {
        return $this->_getSubject()->getUrl($dir);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthorized($authKey, $apiKey, $apiPassword)
    {
        return $this->_getSubject()->isAuthorized($authKey, $apiKey, $apiPassword);
    }

    /**
     * {@inheritdoc}
     */
    public function log($data, $key, $wholeData)
    {
        return $this->_getSubject()->log($data, $key, $wholeData);
    }

    /**
     * {@inheritdoc}
     */
    public function printLog($data, $flag = 1, $filename = 'mobikul.log')
    {
        return $this->_getSubject()->printLog($data, $flag, $filename);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($wholeData, $key)
    {
        return $this->_getSubject()->validate($wholeData, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigData($path, $scope = 'store')
    {
        return $this->_getSubject()->getConfigData($path, $scope);
    }

    /**
     * {@inheritdoc}
     */
    public function canReorder(\Magento\Sales\Model\Order $order)
    {
        return $this->_getSubject()->canReorder($order);
    }

    /**
     * {@inheritdoc}
     */
    public function isModuleOutputEnabled($moduleName = null)
    {
        return $this->_getSubject()->isModuleOutputEnabled($moduleName);
    }
}
