<?php
namespace Webkul\Marketplace\Block\Profile;

/**
 * Interceptor class for @see \Webkul\Marketplace\Block\Profile
 */
class Interceptor extends \Webkul\Marketplace\Block\Profile implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Block\Product\Context $context, \Magento\Framework\Data\Helper\PostHelper $postDataHelper, \Magento\Framework\Url\Helper\Data $urlHelper, \Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Customer\Model\Customer $customer, \Magento\Customer\Model\Session $session, array $data = array())
    {
        $this->___init();
        parent::__construct($context, $postDataHelper, $urlHelper, $objectManager, $customer, $session, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileDetail($value = '')
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getProfileDetail');
        if (!$pluginInfo) {
            return parent::getProfileDetail($value);
        } else {
            return $this->___callPlugins('getProfileDetail', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($product, $imageId, $attributes = array())
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getImage');
        if (!$pluginInfo) {
            return parent::getImage($product, $imageId, $attributes);
        } else {
            return $this->___callPlugins('getImage', func_get_args(), $pluginInfo);
        }
    }
}
