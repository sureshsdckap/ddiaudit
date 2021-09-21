
define([
    'uiComponent'
], function (Element) {
    'use strict';

    return Element.extend({

        initObservable: function () {
            this._super()
                .observe(['content']);

            return this;
        }
    });
});
