define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery'
    ],
    function (Component, $) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Palamarchuk_ElogicIframePayment/payment/elogic_iframe_payment',
            },

            getPaymentData: function () {
                let serverData = [];
                $.ajax({
                    url: '/iframe_payment/info',
                    type: "POST",
                    data: {
                        'XDEBUG_SESSION_START': 'PHPSTORM',
                        'currency': window.checkoutConfig.quoteData.quote_currency_code,
                        'amount': window.checkoutConfig.quoteData.grand_total,
                    },
                    async: false,
                    showLoader: true,
                    cache: false,
                    crossDomain: true,
                    dataType: 'json',
                    success: function (response) {
                        window.checkoutConfig.quoteData.reserved_order_id = response.reserved_order_id;

                        serverData = [response.data];
                    }
                })
                return serverData
            },
            getPaymentSignature: function () {
                let serverSignature = [];
                $.ajax({
                    url: '/iframe_payment/info',
                    type: "POST",
                    data: {
                        'XDEBUG_SESSION_START': 'PHPSTORM',
                        'currency': window.checkoutConfig.quoteData.quote_currency_code,
                        'amount': window.checkoutConfig.quoteData.grand_total,
                    },
                    async: false,
                    showLoader: true,
                    cache: false,
                    crossDomain: true,
                    dataType: 'json',
                    success: function (response) {
                        window.checkoutConfig.quoteData.reserved_order_id = response.reserved_order_id;

                        serverSignature = [response.signature];
                    }
                })
                return serverSignature
            },

        });
    }
);
