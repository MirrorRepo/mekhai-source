<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mobikul
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mobikul\Plugin;

class Translate
{
    protected $request;
    protected $_store;

    /**
     * @param \Magento\Framework\UrlInterface     $urlInterface
     * @param \Webkul\SellerSubDomain\Helper\Data $helepr
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\Store $store,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->request = $request;
        $this->_store = $store;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Store\Model\Store $subject
     * @param $result
     * @return string
     */
    public function afterGetLocale(
        \Magento\Framework\Translate $subject,
        $result
    ) {
        if ($this->request->getHeader("apiKey") && $this->request->getParam("storeId")) {
            return $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->request->getParam("storeId"));
        }
        return $result;
    }
}
