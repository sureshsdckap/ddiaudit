define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'visaSdk',
        'AuthorizeNet_VisaCheckout/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/set-billing-address',
        'mage/url',
        'jquery/ui',
        'mage/translate'
    ],
    function (
        $,
        Component,
        visaSdk,
        setPaymentMethodAction,
        additionalValidators,
        quote,
        customerData,
        setBillingAddress,
        url
    ) {
        'use strict';

        return Component.extend({
            additionalData: {},
            defaults: {
                active: false,
                template: 'AuthorizeNet_VisaCheckout/payment/authorizenet-form',
                code: 'anet_visacheckout',
                grandTotalAmount: null,
                currencyCode: null,
                imports: {
                    onActiveChange: 'active'
                }
            },
            initObservable: function () {
                var self = this;
                this._super()
                    .observe(['active']);
                this.grandTotalAmount = quote.totals()['base_grand_total'];
                this.currencyCode = quote.totals()['base_currency_code'];

                quote.totals.subscribe(function () {
                    if (self.grandTotalAmount !== quote.totals()['base_grand_total']) {
                        self.grandTotalAmount = quote.totals()['base_grand_total'];
                    }

                    if (self.currencyCode !== quote.totals()['base_currency_code']) {
                        self.currencyCode = quote.totals()['base_currency_code'];
                    }
                });

                return this;
            },
            onActiveChange: function (isActive) {
                if (!isActive) {
                    return;
                }

                this.bindClickHandlers();
                this.initVisaCheckout(this.currencyCode, this.grandTotalAmount);

            },
            bindClickHandlers: function () {
                $('img.v-button').click(this.vcButtonClickHandler.bind(this));
            },
            vcButtonClickHandler: function (event) {
                if (!this.validate() || !additionalValidators.validate()) {
                    event.stopImmediatePropagation();
                }
            },
            getCode: function () {
                return this.code;
            },
            isActive: function () {
                var active = this.getCode() === this.isChecked();

                this.active(active);

                return active;
            },
            getTitle: function () {
                return window.checkoutConfig.payment['anet_visacheckout'].title;
            },
            getApiKey: function () {
                return window.checkoutConfig.payment['anet_visacheckout'].api_key;
            },
            getData: function () {
                var data = {
                    'method': this.item.method,
                    'additional_data': {}
                };

                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

                return data;
            },
            getButtonUrl: function () {
                return "https://sandbox.secure.checkout.visa.com/wallet-services-web/xo/button.png";
            },
            placeOrderBefore: function (callId, encKey, encPaymentData) {

                this.additionalData.callId = callId;
                this.additionalData.encKey = encKey;
                this.additionalData.encPaymentData = encPaymentData;
                quote.billingAddress(null);
                this.placeOrder();
            },

            initVisaCheckout: function (currencyCode, totalAmount) {
                var self = this;
                V.init({
                    apikey: self.getApiKey(),
                    paymentRequest: {
                        currencyCode: currencyCode,
                        shippingHandling: quote.totals()['base_shipping_amount'],
                        subtotal: quote.totals()['base_subtotal'],
                        tax: quote.totals()['base_tax_amount'],
                        discount: Math.abs(quote.totals()['base_discount_amount']),
                        total: totalAmount
                    },
                    enableUserDataPrefill: true,
                    settings: {
                        dataLevel: 'FULL', // we must request for full info as Authorize.Net rejects the payment otherwise
                        shipping: {
                            collectShipping: false
                        },
                        review: {
                            buttonAction: 'Pay'
                        }
                    }
                });
                V.on('pre-payment.user-data-prefill', function () {
                    return {
                        userFirstName: quote.shippingAddress().firstname,
                        userLastName: quote.shippingAddress().lastname,
                        userEmail: quote.guestEmail,
                        userPhone: quote.shippingAddress().telephone
                    }
                });
                V.on("payment.success", function (payment) {
                    self.placeOrderBefore(payment.callid, payment.encKey, payment.encPaymentData);
                });

                V.on("payment.error", function (payment, error) {

                    if (error.code === 401) {
                        self.messageContainer.addErrorMessage({'message': $.mage.__('Visa checkout is not available at this time')});
                        return;
                    }
                    self.messageContainer.addErrorMessage(error.message);
                });
            }
        });
    }
);
