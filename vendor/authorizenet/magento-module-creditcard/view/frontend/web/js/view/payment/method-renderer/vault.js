define([
    'jquery',
    'underscore',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Checkout/js/checkout-data'
], function ($, _, VaultComponent, selectPaymentMethod, checkoutData) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'AuthorizeNet_CreditCard/payment/vault/form',
            additionalData: {}
        },

        getMaskedCard: function () {
            return 'XXXX-' + this.details.cardNumber.substr(-4);
        },

        getExpirationDate: function () {
            return this.details.cardExpMonth + '/' + this.details.cardExpYear;
        },

        getCardType: function () {
            return this.details.cardType;
        },

        /**
         * @returns {Object}
         */
        getData: function () {
            var data = {
                'method': this.code,
                'additional_data': {
                    'public_hash': this.publicHash
                }
            };

            if (this.getVaultRequireCvv()) {
                data['additional_data']['vault_cvv'] = $('#vault_cvv_' + this.getId()).val();
            }

            data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

            return data;
        },

        selectPaymentMethod: function () {
            selectPaymentMethod(
                {
                    method: this.getId()
                }
            );
            checkoutData.setSelectedPaymentMethod(this.getId());

            if (this.getVaultRequireCvv()) {
                $('.anet_creditcard-vault-cvv').prop('disabled', true).parents('.anet_creditcard-vault-cvv-wrapper').hide();
                $('#vault_cvv_' + this.getId()).prop('disabled', false).parents('.anet_creditcard-vault-cvv-wrapper').show();
            }

            return true;
        },

        getVaultRequireCvv: function () {
            return this.vaultRequreCvv;
        }
    });
});
