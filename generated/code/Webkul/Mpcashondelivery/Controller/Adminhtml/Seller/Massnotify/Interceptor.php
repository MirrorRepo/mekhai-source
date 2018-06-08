<?php
namespace Webkul\Mpcashondelivery\Controller\Adminhtml\Seller\Massnotify;

/**
 * Interceptor class for @see \Webkul\Mpcashondelivery\Controller\Adminhtml\Seller\Massnotify
 */
class Interceptor extends \Webkul\Mpcashondelivery\Controller\Adminhtml\Seller\Massnotify implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder)
    {
        $this->___init();
        parent::__construct($context, $inlineTranslation, $storeManager, $transportBuilder);
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
