define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'mage/translate'
], function ($, Class, alert) {
    'use strict';

    return Class.extend({

        defaults: {
            $selector: null,
            selector: 'edit_form',
            container: 'payment_form_anet_echeck'
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

            var secureData = {}, authData = {}, bankData = {};

            bankData.routingNumber = $('#' + this.code + '_routing_number').val();
            bankData.accountNumber = $('#' + this.code + '_account_number').val();
            bankData.nameOnAccount = $('#' + this.code + '_account_name').val();
            bankData.accountType   = $('#' + this.code + '_account_type').val();
            bankData.echeckType    = 'WEB';

            secureData.bankData = bankData;

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

            var accountNumber = $('#' + this.code + '_account_number');
            var routingNumber = $('#' + this.code + '_routing_number');
            var opaqueData = $('#' + this.code + '_opaque_data');

            accountNumber.val(accountNumber.val().substr(-4));
            routingNumber.val(routingNumber.val().substr(-4));
            opaqueData.val(JSON.stringify(response.opaqueData));

            this.$selector.trigger('realOrder');
        },

        isMethodSelected: function () {
            return this.$selector.find(':radio[name="payment[method]"]:checked').val() == this.code;
        }
    });
});
