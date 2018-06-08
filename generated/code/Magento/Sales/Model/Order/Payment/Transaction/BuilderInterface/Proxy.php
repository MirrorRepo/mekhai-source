<?php
namespace Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;

/**
 * Proxy class for @see \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
 */
class Proxy implements \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface, \Magento\Framework\ObjectManager\NoninterceptableInterface
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
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
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
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Magento\\Sales\\Model\\Order\\Payment\\Transaction\\BuilderInterface', $shared = true)
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
     * @return \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
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
    public function setPayment(\Magento\Sales\Api\Data\OrderPaymentInterface $payment)
    {
        return $this->_getSubject()->setPayment($payment);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        return $this->_getSubject()->setOrder($order);
    }

    /**
     * {@inheritdoc}
     */
    public function setSalesDocument(\Magento\Sales\Model\AbstractModel $document)
    {
        return $this->_getSubject()->setSalesDocument($document);
    }

    /**
     * {@inheritdoc}
     */
    public function setFailSafe($failSafe)
    {
        return $this->_getSubject()->setFailSafe($failSafe);
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        return $this->_getSubject()->setMessage($message);
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionId($transactionId)
    {
        return $this->_getSubject()->setTransactionId($transactionId);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdditionalInformation(array $value)
    {
        return $this->_getSubject()->setAdditionalInformation($value);
    }

    /**
     * {@inheritdoc}
     */
    public function addAdditionalInformation($key, $value)
    {
        return $this->_getSubject()->addAdditionalInformation($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        return $this->_getSubject()->reset();
    }

    /**
     * {@inheritdoc}
     */
    public function build($type)
    {
        return $this->_getSubject()->build($type);
    }
}
