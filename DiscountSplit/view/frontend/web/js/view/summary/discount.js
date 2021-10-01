define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils'
    ],
    function ($, Component, quote, totals, priceUtils) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Zehntech_DiscountSplit/checkout/totals/discount'
            },
            totals: quote.getTotals(),

            isDisplayedDiscounts: function () {

                if (totals.getSegment('split_discount')) {

                    return true;
                }

                return false;

            },

            getAllDiscount: function() {
                var discount = 0;
                if (this.totals()) {
                    discount = totals.getSegment('split_discount').value;
                }
                //return this.getFormattedPrice(price);

                var discountArray = [];

                if(typeof discount[i] === 'string' || discount[i] instanceof String) {
                    return JSON.parse(discount);
                } 
                else{
                    for(var i=0;i<discount.length;i++){
                        if(typeof discount[i] === 'string' || discount[i] instanceof String)
                            discountArray.push(JSON.parse(discount[i]));
                    }
                    if(discountArray.length)
                        return discountArray;
                }
                return discount;
            },

            getDiscountData: function() {
                var discount = 0;
                if (this.totals()) {
                    discount = totals.getSegment('split_discount').value;
                }
                return discount;
            }           
        });
    }
);
