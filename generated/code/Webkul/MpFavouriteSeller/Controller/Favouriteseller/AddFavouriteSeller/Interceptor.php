<?php
namespace Webkul\MpFavouriteSeller\Controller\Favouriteseller\AddFavouriteSeller;

/**
 * Interceptor class for @see \Webkul\MpFavouriteSeller\Controller\Favouriteseller\AddFavouriteSeller
 */
class Interceptor extends \Webkul\MpFavouriteSeller\Controller\Favouriteseller\AddFavouriteSeller implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Webkul\MpFavouriteSeller\Helper\Data $dataHelper, \Webkul\MpFavouriteSeller\Model\Mpfavouriteseller $mpFavouritesellerModel, \Webkul\MpFavouriteSeller\Model\MpfavouritesellerRepository $mpFavouritesellerRepository, \Magento\Customer\Model\Session $customerSession, \Magento\Customer\Model\Customer $customerModel, \Magento\Framework\Stdlib\DateTime\DateTime $date, \Webkul\Marketplace\Helper\Data $mpHelper, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate)
    {
        $this->___init();
        parent::__construct($context, $dataHelper, $mpFavouritesellerModel, $mpFavouritesellerRepository, $customerSession, $customerModel, $date, $mpHelper, $storeManager, $resultPageFactory, $localeDate);
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
