<?php
namespace Mbattain\PriceDecimal\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{


    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }


    public function getPricePrecision($currency = null)
    {
//        if(is_null($currency)){
//            $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
//        }
//        if($currency=="LAK"){
//            return 0;
//        }
        return 2;
    }
}