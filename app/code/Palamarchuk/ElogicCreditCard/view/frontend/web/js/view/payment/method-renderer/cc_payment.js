/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Payment/js/view/payment/cc-form',
    'Magento_Payment/js/model/credit-card-validation/validator'
], function($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Palamarchuk_ElogicCreditCard/payment/cc_payment',
            code: 'cc_payment'
        },

        /**
         * @returns {String}
         */
        getCode: function () {
            return this.code;
        },

        /**
         * @returns {Boolean}
         */
        isActive: function () {
            return this.getCode() === this.isChecked();
        },

        /**
         * @returns {*|jQuery|(function(): void)}
         */
        validate: function () {
            var form = $(this.getSelector('payment-form'));

            form.validation({focusInvalid: false});
            $('form').off('invalid-form.validate');

            return form.valid();
        },

        /**
         * @param {String} field
         * @returns {String}
         */
        getSelector: function (field) {
            return '#' + this.getCode() + '_' + field;
        },
    });
});
