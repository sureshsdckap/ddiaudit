define([
    'jquery',
    'AuthorizeNet_VisaCheckout/js/action/save-vc-tokens',
    'Magento_Ui/js/modal/alert',
    'visaSdk',
    'jquery/ui',
    'mage/translate',
    'mage/mage',
    'mage/validation'
], function ($, saveVcTokensAction, alert) {
    'use strict';

    $.widget('mage.vcReviewButton', {
        options: {
            callId: '',
            apiKey: '',
            editButtonSelector: '.info-edit'
        },
        _create: function () {
            this._vcInit();
            var that = this;
            $(this.options.editButtonSelector).on('click', function () {
                $(that.element).find('img.v-button').trigger('click');
            })
        },
        _updateTokens: function (callId, encKey, encPaymentData) {

            $('#review-please-wait').show();
            saveVcTokensAction(callId, encKey, encPaymentData)
                .done(function () {
                    window.location.reload();
                })
                .fail(function () {
                    $('#review-please-wait').hide();
                });
        },
        _vcInit: function () {
            var self = this;
            V.init({
                apikey: this.options.apiKey,
                referenceCallID: this.options.callId
            });
            V.on("payment.success", function (payment) {
                self._updateTokens(payment.callid, payment.encKey, payment.encPaymentData);
            });

            V.on("payment.error", function (payment, error) {

            });
        }
    });

    return $.mage.vcReviewButton;
});
