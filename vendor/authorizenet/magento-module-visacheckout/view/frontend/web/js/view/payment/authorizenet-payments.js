/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        var methodCode = 'anet_visacheckout';

        rendererList.push(
            {
                type: methodCode,
                component: 'AuthorizeNet_VisaCheckout/js/view/payment/method-renderer/authorizenet-visa-method'
            }
        );
        
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
