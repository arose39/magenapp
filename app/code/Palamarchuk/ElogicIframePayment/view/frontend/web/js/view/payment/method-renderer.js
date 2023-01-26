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
                type: 'elogic_iframe_payment',
                component: 'Palamarchuk_ElogicIframePayment/js/view/payment/method-renderer/elogic_iframe_payment'
            }
        );
        return Component.extend({});
    }
);
