<?php
namespace Webkul\Marketplace\Model\Order\Pdf\Shipment;

/**
 * Interceptor class for @see \Webkul\Marketplace\Model\Order\Pdf\Shipment
 */
class Interceptor extends \Webkul\Marketplace\Model\Order\Pdf\Shipment implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Customer\Model\Session $customerSession, \Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Payment\Helper\Data $paymentData, \Magento\Framework\Stdlib\StringUtils $string, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\Filesystem $filesystem, \Magento\Sales\Model\Order\Pdf\Config $pdfConfig, \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory, \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, \Magento\Sales\Model\Order\Address\Renderer $addressRenderer, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Locale\ResolverInterface $localeResolver, array $data = array())
    {
        $this->___init();
        parent::__construct($customerSession, $objectManager, $paymentData, $string, $scopeConfig, $filesystem, $pdfConfig, $pdfTotalFactory, $pdfItemsFactory, $localeDate, $inlineTranslation, $addressRenderer, $storeManager, $localeResolver, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function insertDocumentNumber(\Zend_Pdf_Page $page, $text)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'insertDocumentNumber');
        if (!$pluginInfo) {
            return parent::insertDocumentNumber($page, $text);
        } else {
            return $this->___callPlugins('insertDocumentNumber', func_get_args(), $pluginInfo);
        }
    }
}
