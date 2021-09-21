define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'anet_echeck',
            component: 'AuthorizeNet_ECheck/js/view/payment/method-renderer/echeck'
        }
    );

    return Component.extend({});
});
