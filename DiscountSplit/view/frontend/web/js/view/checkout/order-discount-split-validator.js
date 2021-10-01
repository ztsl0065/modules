define(
    [
        'jquery',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/url',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Ui/js/model/messageList',
        'mage/translate'		
    ],
	function ($, customer, quote, urlBuilder, urlFormatter, errorProcessor, messageContainer, __) {			
        'use strict';

        return {

            /**
             * Make an ajax PUT request to save order comment in the quote.
             *
             * @returns {Boolean}
             */
            validate: function () {
                var isCustomer = customer.isLoggedIn();
                // var form = $('.payment-method input[name="payment[method]"]:checked').parents('.payment-method').find('form.order-comment-form');
                var form = $('form.order_split_discount');

                var quoteId = quote.getQuoteId();
                var url;
				
                // validate max length
                var discount = form.find('#split_discount').val();
			
                if (isCustomer) {
                    url = urlBuilder.createUrl('/carts/mine/split-discount', {})
                } else {
                    url = urlBuilder.createUrl('/guest-carts/:cartId/split-discount', {cartId: quoteId});
                }

                var payload = {
                    cartId: quoteId,
                    orderDiscount: {
                        split_discount: discount
                    }
                };

                if (!payload.orderDiscount.split_discount) {
                    return true;
                }

                var result = true;

                $.ajax({
                    url: urlFormatter.build(url),
                    data: JSON.stringify(payload),
                    global: false,
                    contentType: 'application/json',
                    type: 'PUT',
                    async: false
                }).done(
                    function (response) {
                        result = true;
                    }
                ).fail(
                    function (response) {
                        result = false;
                        errorProcessor.process(response);
                    }
                );

                return result;
            },	
	
        };
    }
);