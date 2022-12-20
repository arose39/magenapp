/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Palamarchuk_LuxuryTax/checkout/summary/luxury_tax'
            },
            initialize: function () {
                this._super();
                console.log(this.totals())
                // this.checkIfEquelTotals();
                return this;
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function () {
                return this.isFullMode();
            },
            getValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('luxury_tax').value;
                }
                return this.getFormattedPrice(price);
            },
            getBaseValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().base_luxury_tax;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            },
            checkIfEquelTotals: function () {

                if (this.totals().grand_total == this.totals().subtotal_incl_tax
                ) {
                    console.log(this.totals())
                    totals.getSegment('grand_total').value = +totals.getSegment('grand_total').value  + totals.getSegment('luxury_tax').value
                }
            }
        });
    }
);
