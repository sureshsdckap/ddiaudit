define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'mage/translate',
    'Magento_Payment/js/model/credit-card-validation/validator'
], function ($, Class, alert) {
    'use strict';

    return Class.extend({

        defaults: {
            $selector: null,
            selector: 'edit_form',
            container: 'payment_form_anet_creditcard'
        },

        initialize: function () {
            this._super();

            this.$selector = $('#' + this.selector);

            this.$selector.off('changePaymentMethod.' + this.code)
                .on('changePaymentMethod.' + this.code, this.changePaymentMethod.bind(this));
            this.$selector.off('contentUpdated.' + this.code)
                .on('contentUpdated.' + this.code, this.setPaymentMethod.bind(this));
            this.$selector.off('submitOrder.' + this.code)
                .on('submitOrder.' + this.code, this.submitOrder.bind(this));

            $.validator.addClassRules('validate-cc-exp', {'validate-cc-exp': '#' + this.code + '_expiration_yr'});
            $.validator.addClassRules('validate-cc-cvn', {'validate-cc-cvn': '#' + this.code + '_cc_type'});

            return this;
        },

        setPaymentMethod: function (event) {
            if (this.isMethodSelected()) {
                window.order.addExcludedPaymentMethod(this.code);
                this.enableSubmitOrderEventListener();
            } else {
                this.$selector.off('submitOrder.' + this.code)
            }
        },

        enableSubmitOrderEventListener: function () {
            this.$selector.off('submitOrder');
            this.$selector.on('submitOrder.' + this.code, this.submitOrder.bind(this));
        },

        submitOrder: function (event, method) {

            this.$selector.validate().form();

            if (this.$selector.validate().errorList.length || !this.isMethodSelected()) {
                this.$selector.trigger('processStop');
                return false;
            }

            var secureData = {}, authData = {}, cardData = {};

            cardData.cardNumber = $('#' + this.code + '_cc_number').val();
            cardData.month      = $('#' + this.code + '_expiration').val();
            cardData.year       = $('#' + this.code + '_expiration_yr').val();
            cardData.cardCode   = $('#' + this.code + '_cc_cid').val();

            secureData.cardData = cardData;

            authData.clientKey = this.clientKey;
            authData.apiLoginID = this.loginId;
            secureData.authData = authData;

            Accept.dispatchData(secureData, this.processAndPlace.bind(this));
        },

        changePaymentMethod: function (event, method) {
            if (method === this.code) {
                this.enableSubmitOrderEventListener();
            }
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
                    content: errorMessage
                });

                this.$selector.trigger('processStop');
                return false;
            }

            $('#payment_form_' + this.code).find('input,select').not(':hidden').prop('disabled', true);
            $('#' + this.code + '_opaque_data').val(JSON.stringify(response.opaqueData));

            this.$selector.trigger('realOrder');
        },

        isMethodSelected: function () {
            return this.$selector.find(':radio[name="payment[method]"]:checked').val() == this.code;
        }
    });
});
