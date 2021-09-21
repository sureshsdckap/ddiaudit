define([
    'Magento_Checkout/js/view/payment/default',
    'ko',
    'jquery',
    'mustache',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Ui/js/modal/alert',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/action/redirect-on-success',
    'Magento_Vault/js/view/payment/vault-enabler',
    'mage/validation'
], function (
    Component,
    ko,
    $,
    mustache,
    quote,
    priceUtils,
    fullScreenLoader,
    alert,
    additionalValidators,
    redirectOnSuccessAction,
    VaultEnabler
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'AuthorizeNet_ECheck/payment/form',
            routingNumber: '',
            accountNumber: '',
            accountName: '',
            accountType: '',
            opaqueData: {},
            agreementText: ''
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());

            var that = this;

            $.validator.addMethod(
                'validate-routing-number',
                function (value) {
                    return that.isValidRoutingNumber(value);
                },
                $.mage.__('Routing Number is not valid')
            );

            this.agreementText(that.processAgreementTemplate());

            this.accountType.subscribe(function () {
                that.agreementText(that.processAgreementTemplate());
            });

            quote.getTotals().subscribe(function () {
                that.agreementText(that.processAgreementTemplate());
            });

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe([
                    'routingNumber',
                    'accountNumber',
                    'accountName',
                    'accountType',
                    'agreementText'
                ]);

            return this;
        },

        /**
         * @return {Object}
         */
        getData: function () {
            var data = {
                method: this.item.method,
                additional_data: {
                    opaque_data: this.opaqueData,
                    routingNumber: this.routingNumber().substr(-4),
                    accountNumber: this.accountNumber().substr(-4),
                    accountName: this.accountName(),
                    accountType: this.accountType()
                }
            };

            this.vaultEnabler.visitAdditionalData(data);

            return data;
        },

        isVaultEnabled: function () {
            return this.vaultEnabler.isVaultEnabled();
        },

        getVaultCode: function () {
            return this.getMethodConfig().vaultCode;
        },

        /**
         * @return array
         */
        getAccountTypeOptions: function () {
            return this.getMethodConfig().accountTypeOptions;
        },

        /**
         * @return {jQuery}
         */
        validate: function () {
            var form = 'form[data-role=anet-echeck-form]';
            return $(form).validation() && $(form).validation('isValid');
        },

        isValidRoutingNumber: function (routing) {

            if (routing.length !== 9) {
                return false;
            }

            if (! $.isNumeric(routing)) {
                return false;
            }

            if (routing[0] == '5') {
                return false;
            }

            var checksumTotal = (7 * (parseInt(routing.charAt(0),10) + parseInt(routing.charAt(3),10) + parseInt(routing.charAt(6),10))) +
                (3 * (parseInt(routing.charAt(1),10) + parseInt(routing.charAt(4),10) + parseInt(routing.charAt(7),10))) +
                (9 * (parseInt(routing.charAt(2),10) + parseInt(routing.charAt(5),10) + parseInt(routing.charAt(8),10)));

            return checksumTotal % 10 === 0;
        },

        sendPaymentDataToAnet: function () {

            if (!this.validate() || !additionalValidators.validate()) {
                return;
            }

            var secureData = {}, authData = {}, bankData = {};

            bankData.routingNumber = this.routingNumber();
            bankData.accountNumber = this.accountNumber();
            bankData.nameOnAccount = this.accountName();
            bankData.accountType   = this.accountType();
            bankData.echeckType    = 'WEB';

            secureData.bankData = bankData;

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
            } else {
                this.opaqueData = JSON.stringify(response.opaqueData);
                this.placeOrder();
            }
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

        getMethodConfig: function () {
            return window.checkoutConfig.payment[this.getCode()];
        },

        processAgreementTemplate: function () {

            var formattedGrandTotal;
            var totals = quote.getTotals()();

            if (totals) {
                formattedGrandTotal = priceUtils.formatPrice(
                    totals['grand_total'],
                    quote.getPriceFormat()
                );
            }

            var today = new Date();
            var formattedDate = 'm-d-Y'
                .replace('Y', today.getFullYear())
                .replace('m', today.getMonth()+1)
                .replace('d', today.getDate());

            var data = {
                accountType: this.accountType(),
                total: formattedGrandTotal,
                date: formattedDate
            };

            var template = this.getMethodConfig().agreementTemplate;
            return template ? mustache.render(template, data) : '';
        }
    });
});
