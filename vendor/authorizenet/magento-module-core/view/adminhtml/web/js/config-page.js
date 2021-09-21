define([
    'jquery',
    'Magento_Ui/js/form/components/fieldset'
], function ($, Component) {
    'use strict';

    return Component.extend({
        // defaults: {
        //     listens: {
        //         '${ $.provider }:data.validate': 'onValidate'
        //     },
        //     collapsible: false,
        //     opened: true
        // },
        visit: function () {
            return true;
        },
        leave: function () {

            var dfd = $.Deferred();

            if (this.onValidate()) {
                dfd.resolve();
            } else {
                dfd.reject();
            }
            return dfd.promise();
        },
        validate: function (elem) {
            var result,
                invalid;

            if (typeof elem.validate ==='undefined') {
                return true;
            }

            result = elem.validate();

            invalid = _.find(result, function (item) {
                return !item.valid;
            });

            return invalid;
        },
        onValidate: function () {
            return !!this.elems.some(this.validate, this);
        }
    });
});
