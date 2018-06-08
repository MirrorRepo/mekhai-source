/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpfreeshipping
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Webkul_Mpfreeshipping/js/model/shipping-rates-validator',
        'Webkul_Mpfreeshipping/js/model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        freeShippingRatesValidator,
        freeShippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('mpfreeshipping', freeShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('mpfreeshipping', freeShippingRatesValidationRules);

        return Component;
    }
);
