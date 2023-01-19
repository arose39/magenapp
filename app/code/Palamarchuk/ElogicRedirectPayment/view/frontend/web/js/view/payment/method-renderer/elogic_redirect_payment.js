/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Palamarchuk_ElogicRedirectPayment/js/action/set-payment-method-action'
    ],
    function (ko, $, Component, setPaymentMethodAction) {
        'use strict';
        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: false,
                template: 'Palamarchuk_ElogicRedirectPayment/payment/elogic_redirect_payment'
            },
            afterPlaceOrder: function () {
                setPaymentMethodAction(this.messageContainer);
                return false;
            }
        });
    }
);
