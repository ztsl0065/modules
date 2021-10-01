define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Rokanthemes_OpCheckout/js/action/cancel-coupon',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/error-processor',
        'Magento_SalesRule/js/model/payment/discount-messages',
        'mage/storage',
        'mage/translate',
        'Magento_Checkout/js/model/totals',
        'Rokanthemes_OpCheckout/js/action/reload-shipping-method'
    ],
    function ($, ko, Component, quote, cancelCouponAction, getPaymentInformationAction, urlManager, errorProcessor, messageContainer, storage, $t, totalsProcessor, reloadShippingMethod) {
        'use strict';
        var totals = quote.getTotals();
        var couponCode = ko.observable(null);
        var couponBlank = ko.observable(null);
        var validCoupon = ko.observable(null);
        var temp = [];
        if (totals()) {
            couponCode(totals()['coupon_code']);
            validCoupon(totals()['coupon_code']);
        }
        var isApplied = ko.observable(couponCode() != null);
        var isLoading = ko.observable(false);
        var result;
        return Component.extend({
            defaults: {
                template: 'Zehntech_MultiCoupon/payment/discount'
            },
            couponCode: couponCode,
            couponBlank: couponBlank, 
            validCoupon: validCoupon,
            isShowDiscount: ko.observable(window.checkoutConfig.show_discount),
            /**
             * Applied flag
             */
            isApplied: isApplied,
            isLoading: isLoading,
            /**
             * Coupon code application procedure
             */
            apply: function() {
                if (this.validate()) {
                    this.showOverlay();
                    isLoading(true);
                    var discountCode = $('#discount-code-coupons').val()
                    var codes = $('#discount-code').val();
                    
                    temp = codes ? codes.split(",") : [];
                    if(temp.indexOf(discountCode) !== -1){
                        messageContainer.addErrorMessage({'message': "Already Applied"});
                        return;
                    }

                    if(codes){
                        discountCode = codes.concat(',',discountCode)    
                    }
                    couponCode(discountCode);
                    this.applyCoupon(couponCode(), isApplied, isLoading, "Your coupon was successfully applied.");
                    couponBlank('');
                }
            },
            /**
             * Cancel using coupon
             */
            cancel: function() {
                // if (this.validate()) {
                    this.showOverlay();
                    isLoading(true);
                    couponCode('');
                    couponBlank('');
                    validCoupon('');
                    cancelCouponAction(isApplied, isLoading);
                // }
            },

            showOverlay: function () {
                $('#ajax-loader3').show();
                $('#control_overlay_review').show();
            },

            hideOverlay: function () {
                $('#ajax-loader3').hide();
                $('#control_overlay_review').hide();
            },


            /**
             * Coupon form validation
             *
             * @returns {boolean}
             */
            validate: function() {
                var form = '#discount-form';
                return $(form).validation() && $(form).validation('isValid');
            },
            cancelOne: function(coupon) {
                var coupons = couponCode().split(",");
                coupons.splice(coupons.indexOf(coupon),1)
                if(coupons.length){
                    coupons = coupons.toString();
                    couponCode(coupons);
                    this.applyCoupon(couponCode(), isApplied, isLoading, "coupon was removed successfully.");
                }else{
                    this.cancel();
                }
            },
            applyCoupon: function(couponCode, isApplied, isLoading, msg){
                var quoteId = quote.getQuoteId();
                var url = urlManager.getApplyCouponUrl(couponCode, quoteId);
                var message = $t(msg);
                storage.put(
                    url,
                    {},
                    false
                ).done(
                    function (response) {
                        if (response) {
                            var deferred = $.Deferred();
                            isLoading(false);
                            isApplied(true);
                            getPaymentInformationAction(deferred);
                            reloadShippingMethod();
                            $.when(deferred).done(function () {
                                $('#ajax-loader3').hide();
                                $('#control_overlay_review').hide();
                            });
                            validCoupon(couponCode);
                            messageContainer.addSuccessMessage({'message': message});
                        }
                    }
                ).fail(
                    function (response) {
                        isLoading(false);
                        totalsProcessor.isLoading(false);
                        $('#ajax-loader3').hide();
                        $('#control_overlay_review').hide();
                        validCoupon(validCoupon());
                        errorProcessor.process(response, messageContainer);
                    }
                );
            }
        });
    }
);
