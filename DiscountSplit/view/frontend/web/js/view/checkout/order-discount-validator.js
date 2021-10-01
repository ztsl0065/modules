define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Zehntech_DiscountSplit/js/view/checkout/order-discount-split-validator'
    ],
    function (Component, additionalValidators, discountValidator) {
        'use strict';

        additionalValidators.registerValidator(discountValidator);

        return Component.extend({});
    }
);