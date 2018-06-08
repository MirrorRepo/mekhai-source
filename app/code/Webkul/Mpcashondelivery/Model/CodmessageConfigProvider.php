<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpcashondelivery
 * @author Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Mpcashondelivery\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class CodmessageConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = PaymentMethod::CODE;

    protected $_method;
    /**
     * @var Webkul\Mpcashondelivery\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var Escaper
     */
    protected $_escaper;
    /**
     * @var \Magento\Framework\App\Request\Http $request
     */
    protected $_request;
    /**
     * @param PaymentHelper                        $paymentHelper
     * @param \Webkul\Mpcashondelivery\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface      $urlBuilder
     * @param Escaper                              $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        \Webkul\Mpcashondelivery\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder,
        Escaper $escaper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_escaper = $escaper;
        $this->_helper = $helper;
        $this->_urlBuilder = $urlBuilder;
        $this->_request = $request;
        $this->_method = $paymentHelper->getMethodInstance($this->methodCode);
    }
    public function getConfig()
    {
        $config = [];
        $priceurles['cod_message'] = '';
        $priceurles['handlingfee'] = '';
        $priceurles['error'] = '';
        $currencysymbol = '';
        $ajaxUrl = '';
        if ($this->_method->isAvailable()) {
            $priceurles = $this->_helper->getAppliedPriceRules();
            $ajaxUrl = $this->getAjaxUrl();
            $currencysymbol = $this->getCurrencySymbol();
        }
        if ($priceurles['error'] == 1) {
            $config['payment']['mpcashondelivery']['codmessage'] = '';
        } else {
            $config['payment']['mpcashondelivery']['codmessage'] = $priceurles['cod_message'];
        }
        $config['payment']['mpcashondelivery']['amount'] = $priceurles['handlingfee'];
        $config['payment']['mpcashondelivery']['ajaxurl'] = $ajaxUrl;
        $config['payment']['mpcashondelivery']['currencysymbol'] = $currencysymbol;

        return $config;
    }
    // get url which calsulate cod amount
    public function getAjaxUrl()
    {
        return $this->_urlBuilder->getUrl(
            'mpcashondelivery/availability/codamount',
            ["_secure" => $this->_request->isSecure()]
        );
    }
    // get currency symbol of currency currency code
    protected function getCurrencySymbol()
    {
        return $this->_helper->getCurrencySymbol($this->_helper->getCurrentCurrencyCode());
    }
}
