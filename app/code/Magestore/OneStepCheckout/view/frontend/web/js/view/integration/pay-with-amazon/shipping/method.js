/*global define*/
define(
    [
        'jquery',
        'Magestore_OneStepCheckout/js/view/shipping/methods',
        'Amazon_Payment/js/model/storage',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Customer/js/model/customer',
        'Magestore_OneStepCheckout/js/action/showLoader',
        'Magestore_OneStepCheckout/js/action/save-default-payment'
    ],
    function (
        $,
        Component,
        amazonStorage,
        setShippingInformationAction,
        customer,
        showLoader,
        saveDefaultPayment
    ) {
        'use strict';
        return Component.extend({
            setShippingInformation: function () {
                var self = this;
                function setShippingInformationAmazon()
                {
                    setShippingInformationAction().done(
                        function () {
                            showLoader.payment(false);
                            showLoader.review(false);
                            self.loading(false);
                        }
                    ).fail(
                        function () {
                            showLoader.payment(false);
                            showLoader.review(false);
                            self.loading(false);
                        }
                    ).always(function(){
                        saveDefaultPayment();
                    });
                }
                if (amazonStorage.isAmazonAccountLoggedIn() && customer.isLoggedIn()) {
                    setShippingInformationAmazon();
                } else if (amazonStorage.isAmazonAccountLoggedIn() && !customer.isLoggedIn()) {
                    if (this.validateGuestEmail()) {
                        setShippingInformationAmazon();
                    }
                } else {
                    setShippingInformationAmazon();
                }
            },
            validateGuestEmail: function () {
                var loginFormSelector = 'form[data-role=email-with-possible-login]';
                $(loginFormSelector).validation();
                return $(loginFormSelector + ' input[type=email]').valid();
            },
        });
    }
);