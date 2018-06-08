define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals',
        'mage/translate',
        'jquery'
    ],
    function (Component, quote, priceUtils, totals, $t, $) {
        "use strict";
        return Component.extend({
            defaults: {
                notCalculatedMessage: $t('Not yet calculated'),
                na: $t('NA'),
                template: 'Webkul_Mpcashondelivery/checkout/summary/mpcashondelivery'
            },
            quoteIsVirtual: quote.isVirtual(),
            totals: quote.getTotals(),
            isDisplayed: function () {
                
                return this.totals() && this.isFullMode() && null != quote.shippingMethod();
            },
            isMpcodEnable: function () {

                return null != quote.paymentMethod() && quote.paymentMethod().method == 'mpcashondelivery';
            },
            getValue: function () {

                if (!this.isDisplayed() || !this.isMpcodEnable()) {
                    $('.mpcashondelivery').hide();
                    return this.notCalculatedMessage;
                }
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('mpcashondelivery').value;
                }
                if (price == 0) {
                    $('.mpcashondelivery').show();
                    return this.na;
                }
                $('.mpcashondelivery').show();
                return this.getFormattedPrice(price);
            },
            getBaseValue: function () {
                if (!this.isDisplayed()) {
                    return this.notCalculatedMessage;
                }
                var price = 0;
                if (this.totals()) {
                    price = this.totals().base_mpcashondelivery;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            }
        });
    }
);