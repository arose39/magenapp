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
                type: 'elogic_redirect_payment',
                component: 'Palamarchuk_ElogicRedirectPayment/js/view/payment/method-renderer/elogic_redirect_payment'
            }
        );
        return Component.extend({});
    }
);
