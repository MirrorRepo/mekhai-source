<?php
namespace Sm\MegaMenu\Controller\Adminhtml\MenuItems\GetChildItems;

/**
 * Interceptor class for @see \Sm\MegaMenu\Controller\Adminhtml\MenuItems\GetChildItems
 */
class Interceptor extends \Sm\MegaMenu\Controller\Adminhtml\MenuItems\GetChildItems implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Sm\MegaMenu\Model\MenuItems $menuItems, array $data = array())
    {
        $this->___init();
        parent::__construct($context, $menuItems, $data);
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
