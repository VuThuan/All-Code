/**
 * OneStepCheckout
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to One Step Checkout AS software license.
 *
 * License is available through the world-wide-web at this URL:
 * https://www.onestepcheckout.com/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@onestepcheckout.com so we can send you a copy immediately.
 *
 * @category   onestepcheckout
 * @package    onestepcheckout_iosc
 * @copyright  Copyright (c) 2017 OneStepCheckout  (https://www.onestepcheckout.com/)
 * @license    https://www.onestepcheckout.com/LICENSE.txt
 */
define(
    [
        "uiComponent",
        "uiRegistry",
        "mage/utils/wrapper",
        "jquery",
        "ko",
        "Magento_Checkout/js/view/form/element/email",
        "Magento_Ui/js/lib/view/utils/dom-observer",
        "Magento_Checkout/js/model/payment/additional-validators"
    ],
    function (uiComponent, uiRegistry, wrapper, jQuery, ko, emailView, domObserver, additionalValidators) {
        "use strict";
        return uiComponent.extend({
            initialize: function () {
                this.source = false;
                this.showPwd = ko.observable(false);
                this.isMoved = ko.observable(false);
                this.isExistingPasswordVisible = ko.observable(false);
                this.passwordErrorMessage = "";
                this._super();

                uiRegistry.async("checkout.steps.shipping-step.shippingAddress.customer-email")(
                    function (emailView) {

                        if (this.cnf.registeronsuccess !== "1") {
                            domObserver.get("#customer-email-fieldset span.note", function (elem) {
                                jQuery(elem).remove();
                            });
                        }

                        if (this.cnf.optionalpwd !== "1") {
                            emailView.checkEmailAvailability = wrapper.wrap(emailView.checkEmailAvailability, function (originalMethod) {
                                return true;
                            });
                        }

                        if (this.cnf.optionalpwd == "1") {
                            emailView.isPasswordVisible.subscribe(
                                function (newValue) {
                                    this.isExistingPasswordVisible(newValue);
                                }.bind(this)
                            );
                            uiRegistry.async('checkoutProvider')(
                                function (checkoutProvider) {
                                    this.source = checkoutProvider;
                                }.bind(this)
                            );
                            domObserver.get(".opc-wrapper .form-login > .fieldset", function (elem) {
                                domObserver.get("#iosc-summary .iosc-registration", function (subelem) {
                                    jQuery(elem).append(jQuery('#iosc-summary .iosc-registration'));
                                    jQuery('.opc-sidebar .iosc-registration').remove();
                                    this.isMoved(true);
                                    this.isExistingPasswordVisible(emailView.isPasswordVisible());
                                    return false;
                                }.bind(this));
                                return false;
                            }.bind(this));
                            domObserver.get(".iosc-register-pwd input", function (elem) {
                                jQuery(elem)
                                    .attr('data-password-min-length', this.pwdrules["password-min-length"])
                                    .attr('data-password-min-character-sets', this.pwdrules["password-min-character-sets"]);
                                
                            }.bind(this));
                            uiRegistry.async('checkout.iosc.ajax')(
                                function (ajax) {
                                    ajax.addMethod('params', 'registration', this.paramsHandler.bind(this));
                                }.bind(this)
                            );
                            additionalValidators.registerValidator(this.getValidator());
                        }

                    }.bind(this)
                );
            },

            paramsHandler: function () {

                var params = false;
                    params = this.source.get("iosc.registration");
                return params;
            },

            getValidator: function () {
                return {
                    validate: this.validationHandler.bind(this)
                };
            },

            validationHandler: function () {
                var isValid = false;

                if (this.showPwd()) {
                    this.source.set('params.invalid', false);
                    this.source.trigger("data.validate");
                    if (!this.source.get('params.invalid')) {
                        isValid = true;
                    }
                } else {
                    isValid = true;
                }

                return isValid;
            }
        });
    }
);
