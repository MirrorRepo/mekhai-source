<?php
namespace Sm\CartQuickPro\Controller\Wishlist\Index\Fromcart;

/**
 * Interceptor class for @see \Sm\CartQuickPro\Controller\Wishlist\Index\Fromcart
 */
class Interceptor extends \Sm\CartQuickPro\Controller\Wishlist\Index\Fromcart implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider, \Magento\Wishlist\Helper\Data $wishlistHelper, \Magento\Checkout\Model\Cart $cart, \Magento\Checkout\Helper\Cart $cartHelper, \Magento\Framework\Escaper $escaper, \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator)
    {
        $this->___init();
        parent::__construct($context, $wishlistProvider, $wishlistHelper, $cart, $cartHelper, $escaper, $formKeyValidator);
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
