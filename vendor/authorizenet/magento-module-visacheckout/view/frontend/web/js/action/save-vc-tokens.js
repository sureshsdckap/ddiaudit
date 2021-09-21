define(
    [
        'jquery',
        'mage/url',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function ($, urlBuilder, storage) {
        'use strict';

        return function (callId, encKey, encData) {
            var serviceUrl,
                payload;
            
            serviceUrl = 'anet_visacheckout/checkout/saveTokens';
            payload = {
                callId: callId,
                encKey: encKey,
                encData: encData,
                form_key: $.cookie('form_key')
            };

            return $.ajax({
                url: urlBuilder.build(serviceUrl),
                type: 'POST',
                data: payload,
                dataType: 'json'
            });

        };
    }
);
