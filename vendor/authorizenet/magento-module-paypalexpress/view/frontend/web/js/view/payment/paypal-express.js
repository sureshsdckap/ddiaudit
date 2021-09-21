define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'anet_paypal_express',
            component: 'AuthorizeNet_PayPalExpress/js/view/payment/method-renderer/paypal-express'
        }
    );

    return Component.extend({});
});
