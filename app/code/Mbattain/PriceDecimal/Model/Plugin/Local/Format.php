<?php

namespace Mbattain\PriceDecimal\Model\Plugin\Local;


class Format
{


    public function __construct(
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->_scopeResolver = $scopeResolver;
        $this->_localeResolver = $localeResolver;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @param $subject
     * @param $result
     *
     * @return mixed
     */
    public function beforeGetPriceFormat($subject, $localeCode = null, $currencyCode = null)
    {


//        $localeCode = $localeCode ?: $this->_localeResolver->getLocale();
//        if ($currencyCode) {
//            $currency = $this->currencyFactory->create()->load($currencyCode);
//        } else {
//            $currency = $this->_scopeResolver->getScope()->getCurrentCurrency();
//        }
//
//        if($localeCode == "lo_LA"){
//            if($currency->getCurrencyCode() != "LAK"){
//                $localeCode = "en_US";
//            }
//        }else{
//            if($currency->getCurrencyCode() == "LAK"){
//                $localeCode = "lo_LA";
//            }
//        }

        $localeCode = "en_US";

        return [$localeCode,$currencyCode];
    }
}
