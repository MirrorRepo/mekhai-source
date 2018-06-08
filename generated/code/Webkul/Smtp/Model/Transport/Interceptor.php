<?php
namespace Webkul\Smtp\Model\Transport;

/**
 * Interceptor class for @see \Webkul\Smtp\Model\Transport
 */
class Interceptor extends \Webkul\Smtp\Model\Transport implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Mail\MessageInterface $message, \Webkul\Smtp\Helper\Data $smtpHelper, \Webkul\Smtp\Logger\Logger $logger)
    {
        $this->___init();
        parent::__construct($message, $smtpHelper, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessage()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'sendMessage');
        if (!$pluginInfo) {
            return parent::sendMessage();
        } else {
            return $this->___callPlugins('sendMessage', func_get_args(), $pluginInfo);
        }
    }
}
