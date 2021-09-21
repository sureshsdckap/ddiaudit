define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';
        rendererList.push(
            {
                type: 'anet_creditcard',
                component: 'AuthorizeNet_CreditCard/js/view/payment/method-renderer/creditcard'
            }
        );
        return Component.extend({});
    }
);
