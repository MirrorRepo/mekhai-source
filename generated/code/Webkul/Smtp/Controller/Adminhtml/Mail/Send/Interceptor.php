<?php
namespace Webkul\Smtp\Controller\Adminhtml\Mail\Send;

/**
 * Interceptor class for @see \Webkul\Smtp\Controller\Adminhtml\Mail\Send
 */
class Interceptor extends \Webkul\Smtp\Controller\Adminhtml\Mail\Send implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Webkul\Smtp\Helper\Email $smtpEmailHelper)
    {
        $this->___init();
        parent::__construct($context, $resultJsonFactory, $smtpEmailHelper);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        if (!$pluginInfo) {
            return parent::dispatch($request);
        } else {
            return $this->___callPlugins('dispatch', func_get_args(), $pluginInfo);
        }
    }
}
