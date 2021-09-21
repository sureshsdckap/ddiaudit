require([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/url'
], function ($, alert, urlBuilder) {
    'use strict';

    function createWebhooks()
    {
        $('body').trigger('processStart');
        $.ajax({
            type: 'POST',
            data: {'fromKey':window.FORM_KEY},
            url: urlBuilder.build('anet_webhooks/create/index'),
            success: function (result) {
                $('body').trigger('processStop');
                alert({
                    content: result.message
                });
            },
            error: function () {
                $('body').trigger('processStop');
                alert({
                    content: 'An error occurred'
                });
            },
            dataType: 'json'
        });
    }

    function deleteWebhooks()
    {
        $('body').trigger('processStart');
        $.ajax({
            type: 'POST',
            data: {'fromKey':window.FORM_KEY},
            url: urlBuilder.build('anet_webhooks/delete/index'),
            success: function (result) {
                $('body').trigger('processStop');
                alert({
                    content: result.message
                });
            },
            error: function () {
                $('body').trigger('processStop');
                alert({
                    content: 'An error occurred'
                });
            },
            dataType: 'json'
        });
    }

    window.createWebhooks = createWebhooks;
    window.deleteWebhooks = deleteWebhooks;
});
