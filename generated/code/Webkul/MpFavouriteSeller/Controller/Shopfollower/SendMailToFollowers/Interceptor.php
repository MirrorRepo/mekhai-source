<?php
namespace Webkul\MpFavouriteSeller\Controller\Shopfollower\SendMailToFollowers;

/**
 * Interceptor class for @see \Webkul\MpFavouriteSeller\Controller\Shopfollower\SendMailToFollowers
 */
class Interceptor extends \Webkul\MpFavouriteSeller\Controller\Shopfollower\SendMailToFollowers implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Webkul\MpFavouriteSeller\Model\MpfavouritesellerRepository $mpFavouritesellerRepository, \Magento\Customer\Model\Session $customerSession, \Webkul\MpFavouriteSeller\Helper\Data $helperData, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory)
    {
        $this->___init();
        parent::__construct($context, $mpFavouritesellerRepository, $customerSession, $helperData, $resultPageFactory, $resultJsonFactory);
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
