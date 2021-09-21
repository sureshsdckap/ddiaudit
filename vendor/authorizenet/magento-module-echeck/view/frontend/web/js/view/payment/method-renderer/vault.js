define([
    'jquery',
    'underscore',
    'Magento_Vault/js/view/payment/method-renderer/vault'
], function ($, _, VaultComponent) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'AuthorizeNet_ECheck/payment/vault/form',
            additionalData: {}
        },

        initialize: function () {

            this._super();

            return this;
        },

        getAccountNumber: function () {
            return 'XXXX-' + this.details.accountNumber;
        },

        getAccountName: function () {
            return this.details.accountName;
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

            data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

            return data;
        }
    });
});
