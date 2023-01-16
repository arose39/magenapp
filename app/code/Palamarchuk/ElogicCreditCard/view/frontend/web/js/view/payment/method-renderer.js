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
                type: 'cc_payment',
                component: 'Palamarchuk_ElogicCreditCard/js/view/payment/method-renderer/cc_payment'
            }
        );
        return Component.extend({});
    }
);
