define([
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/modal/alert',
    'mage/url',
    'Magento_Ui/js/form/form'
], function ($, ko, _, alert, urlBuilder, Component) {
    'use strict';

    var steps = ko.observableArray();

    return Component.extend({
        stepsOrder: [],
        currentStepIndex: false,
        steps: steps,
        isNotLastStep: ko.observable(),
        isNotFirstStep: ko.observable(),
        isLastStep: ko.observable(),
        initialize: function () {
            var that = this;

            this._super();

            this.isNotLastStep.subscribe(function (value) {
                that.isLastStep(!value);
            });

            this.isNotFirstStep(false);
            this.isNotLastStep(true);

        },
        initElement: function (elem) {

            var stepOrder = this.findStepOrder(elem);

            if (stepOrder) {
                this.registerStep(elem, stepOrder);
            }

            return this._super(elem);
        },
        findStepOrder: function (step) {
            return _.findKey(this.stepsOrder, function (i) {
                return i === step.index
            });
        },
        registerStep: function (elem, position) {
            steps.push({
                name: elem.index,
                element: elem,
                position: parseInt(position),
                isVisible: elem.visible
            });
        },
        sortItems: function (one, two) {
            return one.position > two.position ? 1 : -1;
        },
        nextStep: function () {
            this._changeStep(1);
        },
        backStep: function () {
            this._changeStep(-1);
        },
        _changeStep: function (shift) {
            var activeIndex, activeStep, that = this;
            steps.sort(this.sortItems).forEach(function (element, index) {
                if (element.isVisible()) {
                    activeStep = element;
                    activeIndex = index;
                }
            });

            if (!activeStep) {
                return;
            }

            activeStep.element.leave().done(function () {
                activeStep.isVisible(false);

                if (typeof activeIndex === 'undefined' || activeIndex + shift < 0 || activeIndex + shift > steps().length - 1) {
                    activeIndex = (shift < 0) ? 0 : steps().length - 1;
                    shift = 0;
                }

                that._showStepByIndex(activeIndex + shift);

            }).fail(function () {
                console.log('step')
            });

        },
        _showStepByIndex: function (index) {
            var nextStep = steps.sort(this.sortItems)[index];
            nextStep.isVisible(true);

            this.isNotLastStep(index !== steps().length - 1);
            this.isNotFirstStep(index !== 0);

        }
    });
});
