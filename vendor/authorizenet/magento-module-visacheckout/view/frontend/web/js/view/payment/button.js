define(
    [
        'jquery',
        'uiComponent',
        'AuthorizeNet_VisaCheckout/js/action/save-vc-tokens',
        'visaSdk',
        'mage/url',
        'jquery/ui',
        'mage/translate',
        'mage/mage'
    ],
    function (
        $,
        Component,
        saveTokensAction,
        visaSdk,
        url
    ) {
        'use strict';

        var vcButtonComponent;

        return Component.extend({
            additionalData: {},
            defaults: {
                active: false,
                code: 'anet_visacheckout',
                apiKey: '',
                blockContainerSelector: '',
                isCatalogProduct: false
            },
            initialize: function () {
                this._super();
                
                var block = $(this.blockContainerSelector);
                if (block) {
                    if (-1 !== window.location.pathname.indexOf('anet_visacheckout/checkout/review')) {
                        block.hide();
                        return;
                    }
                    block.children('.v-button').on('click', this.vcButtonClickHandler.bind(this));
                }

                this.initVisaCheckout();

            },
            vcButtonClickHandler: function (event) {
                vcButtonComponent = this;
                this.validateForm(event);
            },
            validateForm: function (event) {

                if (!this.isCatalogProduct) {
                    return;
                }

                var $form = $(this.blockContainerSelector).closest('form');

                if (!$form.valid()) {
                    event.stopImmediatePropagation();
                }

            },
            getApiKey: function () {
                return this.apiKey;
            },
            placeOrderBefore: function (callId, encKey, encPaymentData) {
                var that = this;
                var $form = $(this.blockContainerSelector).closest('form');
                saveTokensAction(callId, encKey, encPaymentData).done(function () {

                    var reviewUrl = url.build('anet_visacheckout/checkout/review');
                    
                    if (!that.isCatalogProduct) {
                        $.mage.redirect(reviewUrl);
                        return;
                    }

                    var redirectInput = $form.find('[data-role=vc-review-url]')[0];

                    if (!redirectInput) {
                        redirectInput = $('<input type="hidden" data-role="vc-review-url" name="return_url" value=""/>');
                        redirectInput.appendTo($form);
                    }

                    $(redirectInput).val(reviewUrl);

                    $form.submit();

                });
            },
            initVisaCheckout: function () {
                var self = this;
                V.init({
                    apikey: self.getApiKey(),
                    settings: {
                        dataLevel: 'FULL' // we must request for full info as Authorize.Net rejects the payment otherwise
                    }
                });
                V.on("payment.success", function (payment) {

                    if (vcButtonComponent) {
                        vcButtonComponent
                            .placeOrderBefore.call(vcButtonComponent, payment.callid, payment.encKey, payment.encPaymentData);
                        return;
                    }
                    self.placeOrderBefore(payment.callid, payment.encKey, payment.encPaymentData);
                });

                V.on("payment.cancel", function (payment) {
                    console.log('VC cancelled');
                });

                V.on("payment.error", function (payment, error) {

                });
            }
        });
    }
);
