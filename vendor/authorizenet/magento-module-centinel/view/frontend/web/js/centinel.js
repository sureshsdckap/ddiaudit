define(
    [
        'uiElement',
        'jquery',
        'mage/url',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data',
        'Magento_Ui/js/modal/alert',
        'songbird'
    ],
    function (
        Component,
        $,
        url,
        quote,
        customerData,
        alert
    ) {
        'use strict';

        return Component.extend({

            /**
             * @param {String} paymentCode
             */
            setPaymentCode: function (paymentCode) {
                this.paymentCode = paymentCode;
                return this;
            },

            /**
             * @param {Object} ccData
             */
            setCcData: function (ccData) {
                this.ccData = ccData;
                return this;
            },

            initCca: function () {

                Cardinal.configure({
                    logging: { debug: 'Off' },
                    cca: { view: 'modal' }
                });

                this.addCcaEventHandlers();

                $.ajax({
                    url: url.build('/anet_centinel/cca/getToken')
                }).done(function (response) {
                    Cardinal.setup('init', { jwt: response.jwt });
                }).fail(function (xhr) {
                    alert({ content: xhr.responseJSON.error });
                });

                return this;
            },

            processCca: function () {
                Cardinal.start('cca', this.prepareCcaData());
                return this;
            },

            prepareCcaData: function () {
                var shippingAddress = quote.shippingAddress();
                var billingAddress = quote.billingAddress();
                var totals = quote.totals();

                var total = Math.round(totals['base_grand_total'] * 100);
                var currencyCode = totals['base_currency_code'];

                var cardName = billingAddress.firstname + ' ' + billingAddress.lastname;
                var cardMonth = this.ccData.month.length == 1 ? '0' + this.ccData.month : this.ccData.month;
                var email = typeof window.customerData.email !== 'undefined'
                    ? window.customerData.email
                    : customerData.getValidatedEmailValue();

                return {
                    OrderDetails: {
                        Amount: total,
                        CurrencyCode: currencyCode
                    },
                    Consumer: {
                        Email1: email,
                        BillingAddress: {
                            FirstName: billingAddress.firstname,
                            LastName: billingAddress.lastname,
                            Address1: billingAddress.street[0],
                            City: billingAddress.city,
                            State: billingAddress.region,
                            PostalCode: billingAddress.postcode,
                            CountryCode: billingAddress.countryId,
                            Phone1: billingAddress.telephone
                        },
                        ShippingAddress: {
                            FirstName: shippingAddress.firstname,
                            LastName: shippingAddress.lastname,
                            Address1: shippingAddress.street[0],
                            City: shippingAddress.city,
                            State: shippingAddress.region,
                            PostalCode: shippingAddress.postcode,
                            CountryCode: shippingAddress.countryId,
                            Phone1: shippingAddress.telephone
                        },
                        Account: {
                            AccountNumber: this.ccData.cardNumber,
                            NameOnAccount: cardName,
                            ExpirationMonth: cardMonth,
                            ExpirationYear: this.ccData.year,
                            CardCode: this.ccData.cardCode
                        }
                    }
                };
            },

            addCcaEventHandlers: function () {

                Cardinal.on("payments.setupComplete", function () {
                    console.log('Cardinal payments.setupComplete');
                });

                Cardinal.on('payments.validated', function (data, jwt) {
                    var result = false;

                    $.ajax({
                        url: url.build('/anet_centinel/cca/handleResponse'),
                        type: 'POST',
                        data: { 'jwt': jwt }
                    }).done(function (response) {
                        result = response.status;
                    }).fail(function (xhr) {
                        alert({ content: xhr.responseJSON.error });
                    }).always(function () {
                        $('body').trigger('anet.centinel.cca.validation', [ result ]);
                    });
                });
            },

            /**
             * @returns {Boolean}
             */
            isCentinelActive: function () {
                return this.getMethodConfig()['centinelActive'] === true;
            },

            getMethodConfig: function () {
                return window.checkoutConfig.payment[this.paymentCode];
            }
        });
    }
);
