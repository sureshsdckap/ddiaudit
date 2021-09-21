define([
    'Magento_Checkout/js/view/payment/default',
    'ko',
    'jquery',
    'underscore',
    'mage/url',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/action/redirect-on-success',
    'paypalCheckoutJs'
], function (Component, ko, $, _, urlBuilder, fullScreenLoader, additionalValidators, redirectOnSuccessAction) {
    'use strict';

    return Component.extend({
        defaults: {
            active: false,
            template: 'AuthorizeNet_PayPalExpress/payment/form',
            token: '',
            payerId: '',
            initTransId: '',
            imports: {
                onActiveChange: 'active'
            },
            paypalButtonActions: null
        },
        initObservable: function () {
            this._super()
                .observe(['active']);

            return this;
        },
        onActiveChange: function (isActive) {

            if (!isActive) {
                return;
            }
            _.defer(this.checkPayPalAvailability.bind(this));
            _.defer(this.initAgreementClickHandler.bind(this));
        },
        /**
         * Check if payment is active
         *
         * @returns {Boolean}
         */
        isActive: function () {
            var active = this.getCode() === this.isChecked();

            this.active(active);

            return active;
        },
        /**
         * @return {Object}
         */
        getData: function () {
            return {
                method: this.item.method,
                additional_data: {
                    token: this.token,
                    payerId: this.payerId,
                    initTransId: this.initTransId
                }
            };
        },

        placeOrder: function (data, event) {
            var self = this;

            if (event) {
                event.preventDefault();
            }

            if (this.validate() && additionalValidators.validate()) {
                this.isPlaceOrderActionAllowed(false);

                this.getPlaceOrderDeferredObject()
                    .fail(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                            var errorMessages = $('.messages .message-error');
                            if (errorMessages.length) {
                                $(window).scrollTop(errorMessages.first().offset().top - 70);
                            }
                        }
                    ).done(
                        function () {
                            self.afterPlaceOrder();
                            if (self.redirectAfterPlaceOrder) {
                                redirectOnSuccessAction.execute();
                            }
                        }
                    ).always(
                        function () {
                            fullScreenLoader.stopLoader(true);
                        }
                    );

                return true;
            }

            return false;
        },

        initPayPalButton: function () {

            var that = this;

            paypal.Button.render({

                env: that.getMethodConfig().test ? 'sandbox' : 'production',

                commit: true,

                style: {
                    label: 'pay',
                    size:  'medium', // small | medium | large | responsive
                    shape: 'rect', // pill | rect
                    color: 'gold' // gold | blue | silver | black
                },

                validate: function (actions) {
                    that.actions = actions;
                    that.checkPayPalAvailability();
                },

                payment: function () {
                    return paypal.request.post(that.getMethodConfig().initActionUrl)
                        .then(function (res) {

                            if (res.status) {
                                that.token = res.data.token;
                                that.initTransId = res.data.transId;
                                return that.token;
                            } else {
                                window.alert(res.error);
                            }
                        });
                },
                onAuthorize: function (data, actions) {

                    fullScreenLoader.startLoader();

                    that.token = data.paymentToken;
                    that.payerId = data.payerID;

                    that.placeOrder();
                }

            }, '#paypal-button-container');
        },
        checkPayPalAvailability: function () {

            if (!this.actions) {
                return;
            }

            this.actions.enable();

            if (!this.validate() || !additionalValidators.validate()) {
                this.actions.disable();
            }

        },
        initAgreementClickHandler: function () {
            var agreementElements = $('.payment-method._active div.checkout-agreements input');

            agreementElements.click(this.checkPayPalAvailability.bind(this));
        },
        getMethodConfig: function () {
            return window.checkoutConfig.payment[this.getCode()];
        }
    });
});
