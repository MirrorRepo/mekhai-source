/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/cart/totals-processor/default'
    ],
    function (ko, $, Component, additionalValidators, totals, quote, defaultTotal) {
        'use strict';
        var codConfig = window.checkoutConfig.payment.mpcashondelivery;
        var instruction = ko.observable(codConfig.codmessage);
        var amount = ko.observable(codConfig.amount);
        return Component.extend({
            defaults: {
                template : 'Webkul_Mpcashondelivery/payment/mpcashondelivery',
                instruction : instruction,
                amount : amount,
                currecysymbol : codConfig.currencysymbol,
                ajaxurl : codConfig.ajaxurl
            },
            totals: quote.getTotals(),
            initialize: function () {
                var self = this;
                this._super();
                var mainthis = this;
                if (quote.paymentMethod() && quote.paymentMethod().method == 'mpcashondelivery') {
                    mainthis.getCodAmount();
                }
                $("body").delegate(".payment-method .radio", "click", function () {
                    if ($(this).val()=='mpcashondelivery') {
                        mainthis.getCodAmount();
                    } else {
                        defaultTotal.estimateTotals();
                    }
                });
                
            },
            getGrandTotal:function () {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('grand_total').value;
                }
                return price;
            },
            getUpdatedTotal:function () {
                return this.amount()+this.getGrandTotal();
            },
            getCodAmount:function () {
                totals.isLoading(true);
                var paymentmethod = this;
                var ajaxreturn = $.ajax({
                    url:paymentmethod.ajaxurl,
                    type:"POST",
                    dataType:'json',
                    data:{},
                    success:function (content) {
                        totals.isLoading(false);
                        paymentmethod.instruction(content.cod_message);
                        paymentmethod.amount(content.handlingfee);
                        var totalData = paymentmethod.totals();
                        if (!totalData.total_segments[0].value) {
                            totalData.total_segments[0].value = content.handlingfee;
                            totalData.total_segments[4].value = totalData.total_segments[4].value+content.handlingfee;
                            totalData.total_segments[1].value = totalData.total_segments[4].value+content.handlingfee;
                        }
                        quote.setTotals(totalData);
                    }
                });
                if (ajaxreturn) {
                    return true;
                }
            }
        });
    }
);