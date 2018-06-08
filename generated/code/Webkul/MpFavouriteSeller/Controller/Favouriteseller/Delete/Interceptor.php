<?php
namespace Webkul\MpFavouriteSeller\Controller\Favouriteseller\Delete;

/**
 * Interceptor class for @see \Webkul\MpFavouriteSeller\Controller\Favouriteseller\Delete
 */
class Interceptor extends \Webkul\MpFavouriteSeller\Controller\Favouriteseller\Delete implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Webkul\MpFavouriteSeller\Model\MpfavouritesellerRepository $mpFavouritesellerRepository, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Webkul\MpFavouriteSeller\Helper\Data $helper)
    {
        $this->___init();
        parent::__construct($context, $mpFavouritesellerRepository, $customerSession, $resultPageFactory, $helper);
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
