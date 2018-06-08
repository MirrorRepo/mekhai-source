define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'mpcashondelivery',
                component: 'Webkul_Mpcashondelivery/js/view/payment/method-renderer/mpcashondelivery'
            }
        );

        return Component.extend({});
    }
);