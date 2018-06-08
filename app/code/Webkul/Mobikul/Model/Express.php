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

    namespace Webkul\Mobikul\Model;

    class Express extends \Magento\Paypal\Model\Express     {

        public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)   {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $request = $om->get("Magento\Framework\App\RequestInterface");
            if ($request->getHeader("authKey"))
                return true;
            parent::authorize($payment, $amount);
        }

    }