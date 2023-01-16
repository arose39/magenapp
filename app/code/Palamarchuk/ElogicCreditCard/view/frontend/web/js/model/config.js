/*browser:true*/
/*global define*/
define([], function () {
    return {
        /**
         * @return {component: string, type: string}
         */
        getComponent: function (connectionTypes) {
            var connectionType = this.getConnectionType();

            var component = {
                type: 'cc_payment',
                component: connectionTypes[connectionType]
            };

            return component;
        },

        /**
         * @return string
         */
        getConnectionType: function () {
            return window.checkoutConfig.payment.cc_payment.connection_type;
        }
    };
});
