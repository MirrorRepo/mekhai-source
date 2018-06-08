define(
    [
        'Webkul_Mpcashondelivery/js/view/checkout/summary/mpcashondelivery',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, quote) {
        'use strict';

        return Component.extend({

            /**
             * @override
             */
            isDisplayed: function () {
                return !!quote.shippingMethod();
            }
        });
    }
);