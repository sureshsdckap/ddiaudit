define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/modal/alert',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Vault/js/view/payment/vault-enabler',
        'AuthorizeNet_Centinel/js/centinel'
    ],
    function (
        Component,
        $,
        validator,
        additionalValidators,
        fullScreenLoader,
        alert,
        redirectOnSuccessAction,
        VaultEnabler,
        CentinelService
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'AuthorizeNet_CreditCard/payment/form'
            },

            /** @inheritdoc */
            initialize: function () {
                this._super();

                this.vaultEnabler = new VaultEnabler();
                this.vaultEnabler.setPaymentCode(this.getVaultCode());

                this.centinelService = new CentinelService();
                this.centinelService.setPaymentCode(this.getCode());

                if (this.centinelService.isCentinelActive()) {
                    this.centinelService.initCca();
                }

                return this;
            },

            /**
             * Set list of observable attributes
             *
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                this._super()
                    .observe(['creditCardNumber']);

                return this;
            },

            getMethodConfig: function () {
                return window.checkoutConfig.payment[this.getCode()];
            },

            /**
             * Get payment method code.
             */
            getCode: function () {
                return this.item.method;
            },

            validate: function () {
                var form = $('#' + this.getCode() + '-form');
                return form.validation() && form.validation('isValid');
            },

            sendPaymentDataToAnet: function () {
                if (!this.validate() || !additionalValidators.validate()) {
                    return;
                }

                var secureData = {}, authData = {}, cardData = {};

                cardData.cardNumber = this.creditCardNumber();
                cardData.month = this.creditCardExpMonth();
                cardData.year = this.creditCardExpYear();
                cardData.cardCode = this.creditCardVerificationNumber();

                secureData.cardData = cardData;

                authData.clientKey = this.getMethodConfig().clientKey;
                authData.apiLoginID = this.getMethodConfig().loginId;
                secureData.authData = authData;

                fullScreenLoader.startLoader();

                Accept.dispatchData(secureData, this.processAndPlace.bind(this));
            },

            processAndPlace: function (response) {
                if (response.messages.resultCode === "Error") {
                    var i = 0;
                    var errorMessage = '';

                    while (i < response.messages.message.length) {
                        errorMessage += response.messages.message[i].code + ": " + response.messages.message[i].text + "<br />",
                            i = i + 1;
                    }

                    alert({
                        content: errorMessage,
                        actions: {
                            always: fullScreenLoader.stopLoader(true)
                        }
                    });

                    return;
                }

                this.opaqueData = JSON.stringify(response.opaqueData);

                // Centinel
                if (this.centinelService.isCentinelActive()) {
                    var ccData = {};

                    ccData.cardNumber = this.creditCardNumber();
                    ccData.month = this.creditCardExpMonth();
                    ccData.year = this.creditCardExpYear();
                    ccData.cardCode = this.creditCardVerificationNumber();

                    this.centinelService.setCcData(ccData).processCca();
                    this.addCcaValidationResultHandler();

                    return;
                }

                this.placeOrder();
            },

            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

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
            },

            addCcaValidationResultHandler: function () {
                var self = this;

                $('body').on('anet.centinel.cca.validation', function (event, result) {
                    if (!result) {
                        fullScreenLoader.stopLoader(true);
                        self.resetData();
                        return;
                    }
                    if (self.isPlaceOrderActionAllowed()) {
                        self.placeOrder();
                    }
                });
            },

            resetData: function () {
                this.creditCardNumber('');
                this.creditCardExpMonth('');
                this.creditCardExpYear('');
                this.creditCardVerificationNumber('');
            },

            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            },

            getVaultCode: function () {
                return this.getMethodConfig().vaultCode;
            },

            /**
             * @return {Object}
             */
            getData: function () {
                var data = {
                    method: this.getCode(),
                    additional_data: {
                        opaque_data: this.opaqueData,
                        cardExpYear: this.creditCardExpYear(),
                        cardExpMonth: this.creditCardExpMonth()
                    }
                };

                this.vaultEnabler.visitAdditionalData(data);

                return data;
            }
        });
    }
);
