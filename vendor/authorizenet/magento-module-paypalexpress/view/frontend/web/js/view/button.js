define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Customer/js/customer-data',
    'catalogAddToCart',
    'jquery/ui',
    'mage/mage',
    'paypalCheckoutJs'
], function ($, confirm, customerData, test) {
    'use strict';

    $.widget('mage.authorizeNetPaypalCheckout', {
        options: {
            originalForm:
                'form:not(#product_addtocart_form_from_popup):has(input[name="product"][value=%1])',
            productId: 'input[type="hidden"][name="product"]',
            ppCheckoutSelector: '[data-role=pp-checkout-url]',
            ppCheckoutInput: '<input type="hidden" data-role="pp-checkout-url" name="return_url" value=""/>',
            buttonLabel: 'pay',
            ignoreShippingAddress: false
        },

        /**
         * Initialize store credit events
         * @private
         */
        _create: function () {
            this.initPayPalButton();
        },

        initPayPalButton: function () {

            var that = this;

            paypal.Button.render({
                env: that.options.isSandbox ? 'sandbox' : 'production',
                commit: false,
                style: {
                    label: that.options.buttonLabel,
                    size:  'medium', // small | medium | large | responsive
                    shape: 'rect', // pill | rect
                    color: 'gold' // gold | blue | silver | black
                },
                onClick: function () {
                    that.clickHandler();
                },
                validate: function (actions) {
                    that.bindChangeHandlers(actions);
                },
                payment: function (data, actions) {
                    return that._paymentHandler();
                },
                onAuthorize: function (data, actions) {
                    that.token = data.paymentToken;
                    that.payerId = data.payerID;

                    return paypal.request.post(that.options.saveTokenUrl, {
                        token: data.paymentToken,
                        transId: that.initTransId,
                        form_key: $.cookie('form_key')
                    }).then(function (res) {

                        if (res.status) {
                            actions.redirect();
                            return;
                        }

                        window.alert(res.error);

                    });

                }
            }, this.options.blockContainerSelector);
        },
        clickHandler: function () {

            if (!this.options.isCatalogProduct) {
                //do nothing on cart
                return;
            }

            var $form = $($(this.options.blockContainerSelector).closest('form'));

            $form.valid();

        },
        bindChangeHandlers: function (actions) {

            var $form = $($(this.options.blockContainerSelector).closest('form')),
                that = this;

            if (!this.options.isCatalogProduct) {
                //do nothing on cart
                return;
            }

            //add handlers to form changes, and enable/disable paypal button respectively
            $form.change(function () {
                if (!$form.validate().checkForm()) {
                    actions.disable();
                    return;
                }
                actions.enable();
            });

            // trigger change event for initial state check
            $form.change();

        },
        _triggerAddToCart: function () {

        },
        _paymentHandler: function () {
            var that = this,
                $form = $($(this.options.blockContainerSelector).closest('form'));

            return $.Deferred(function (deferred) {

                $.Deferred(function (add2cardDeferred) {
                    if (!that.options.isCatalogProduct) {
                        add2cardDeferred.resolve();
                        return;
                    }
                    $form.submit();
                    $(document).one('ajax:addToCart', function (event) {
                        add2cardDeferred.resolve();
                    });
                }).promise().done(function () {
                    paypal.request.post(that.options.initActionUrl, {ignore_shipping: that.options.ignoreShippingAddress}).then(function (res) {
                        if (res.status) {
                            that.token = res.data.token;
                            that.initTransId = res.data.transId;
                            deferred.resolve(res.data.token);
                        } else {
                            window.alert(res.error);
                            deferred.reject(new Error(res.error));
                        }
                    })
                });

            }).promise();
        }
    });

    return $.mage.authorizeNetPaypalCheckout;
});
