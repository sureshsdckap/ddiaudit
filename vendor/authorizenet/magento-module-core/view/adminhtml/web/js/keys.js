define([
    'jquery',
    'underscore',
    'AuthorizeNet_Core/js/config-page',
    'Magento_Ui/js/modal/alert'
], function ($, _, Component, alert) {
    'use strict';

    return Component.extend({
        defaults: {},
        visit: function () {
            return true;
        },
        leave: function () {

            var dfd = $.Deferred(), that = this;

            if (!this.onValidate()) {
                dfd.reject();
                return dfd.promise();
            }
            $('body').trigger('processStart');

            $.ajax({
                type: 'POST',
                data: that.source.data,
                url: that.detailsUrl,
                dataType: 'json'
            }).always(function () {
                $('body').trigger('processStop');
            }).fail(function () {
                alert({content: 'An error occurred'});
                dfd.reject();
            }).done(function (result) {
                if (!result.status) {
                    alert({content: result.message});
                    dfd.reject();
                    return;
                }

                _.each(result.details, function (value, key) {
                    that.source.set(key, value);
                });

                dfd.resolve();
            });

            return dfd.promise();
        }
    });
});
