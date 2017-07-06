/*
 * *
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *  
 */
define(
    [
        'jquery',
        'uiComponent',
        'ko',
        'mage/translate',
        'Magento_Checkout/js/model/quote',
        'Magestore_OneStepCheckout/js/action/validate-shipping-info',
        'Magestore_OneStepCheckout/js/action/showLoader',
        'Magestore_OneStepCheckout/js/action/save-shipping-address',
        'Magestore_OneStepCheckout/js/action/set-shipping-information',
        'Magestore_OneStepCheckout/js/model/shipping-rate-service',
        'Magestore_OneStepCheckout/js/action/save-additional-information',
        'Magento_Ui/js/modal/alert',
        'Magento_Checkout/js/checkout-data',
        'Magestore_OneStepCheckout/js/model/validate-shipping',
        'Magestore_OneStepCheckout/js/view/shipping/methods',
        'mage/url',
        'Magestore_OneStepCheckout/js/view/gift-message'
    ],
    function (
        $,
        Component,
        ko,
        $t,
        quote,
        ValidateShippingInfo,
        Loader,
        SaveAddressBeforePlaceOrder,
        setShippingInformationAction,
        shippingRateService,
        saveAdditionalInformation,
        alertPopup,
        checkoutData,
        ValidateShipping,
        shippingMethods,
        url,
        giftMessageView
    ) {
        'use strict';
        return Component.extend({
            logoUrl: ko.observable(window.logoUrl),
            logoLink: ko.observable(window.logoLink),
            supportBlockHtml: ko.observable(window.supportBlockHtml),
            shippingStepText: "Where should we ship this order?",
            billingStepText: "What's your billing address?",
            reviewStepText: "Let's review your order!",
            defaults: {
                template: 'Magestore_OneStepCheckout/onestepcheckout'
            },
            errorMessage: ko.observable(),
            paymentMethodAllowPopup: ['braintree_paypal'],
            isVirtual:quote.isVirtual,
            enableCheckout: ko.pureComputed(function(){
                return (Loader.loading())?false:true;
            }),
            placingOrder: ko.observable(false),
            initialize: function () {
                this._super();
            },
            getPrivacyPolicyUrl: function() {
                return url.build('#');
            },
            getTermsConditionUrl: function () {
                return url.build('#');
            },
            shippingFormData: function() {
                if (checkoutData.getShippingAddressFromData() != null) {
                    return checkoutData.getShippingAddressFromData();
                }
            },
            selectShippingMethod: function() {
                var selcted = $("#carrier_select").val();
                jQuery(".methods-shipping input[value="+selcted+"]").click();
            },
            paymentData: function() {
                return $(".payment-method input[type=radio]:checked").parent('.payment-method-title').find('.label').find('span').text();
            },
            continueShippingStep: function() {
                // console.log(shippingMethods.getShippingList());
                $('.nav-step').removeClass('active');
                $('#shipping_step').addClass('active');
                $('.btn-proceed-checkout').hide();
                $('#onestepcheckout-button-continue-to-billing').css('display', 'block');;
                $('.address-information.address-info-3-columns').show();
                $('.onestepcheckout-shipping-payment-review').hide();
                $('.onestepcheckout-order-review').hide();
            },
            continueBillingStep: function() {
                if($('#shipping_step').hasClass('done')){
                    $('.nav-step').removeClass('active');
                    $('#billing_step').addClass('active');
                    $('.btn-proceed-checkout').hide();
                    $('#onestepcheckout-button-continue-to-review').css('display', 'block');;
                    $('.address-information.address-info-3-columns').hide();
                    $('.onestepcheckout-shipping-payment-review').show();
                    $('.onestepcheckout-order-review').hide();
                }
            },
            continueReviewStep: function() {
                if($('#billing_step').hasClass('done')){
                    $('.nav-step').removeClass('active');
                    $('#review_step').addClass('active');
                    $('.btn-proceed-checkout').hide();
                    $('#onestepcheckout-button-place-order').css('display', 'block');;
                    $('.address-information.address-info-3-columns').hide();
                    $('.onestepcheckout-shipping-payment-review').hide();
                    $('.onestepcheckout-order-review').show();
                }
            },
            continueBilling: function(){
                var payment = quote.paymentMethod();
                if(payment) {
                    var shippingFormData = checkoutData.getShippingAddressFromData();
                    console.log(this.validateFormData(shippingFormData));
                    if(this.validateFormData(shippingFormData)) {
                        $('#onestepcheckout-button-continue-to-billing').hide();
                        $('.address-information.address-info-3-columns').hide();
                        $('#shipping_step').addClass('done');
                        $('.nav-step').removeClass('active');
                        $('#billing_step').addClass('active');
                        $('.onestepcheckout-shipping-payment-review').show();
                        $('#onestepcheckout-button-continue-to-review').css('display', 'block');;
                    } else {
                        this.showErrorMessage($t('Please check your address.'));
                    }
                }
                    
            },
            validateFormData: function(shippingFormData){
                console.log(shippingFormData);
                var toReturn = true;
                var toReturn = true;
                if(shippingFormData.firstname != null && shippingFormData.firstname.length == 0) {
                    toReturn = false;
                }
                if(shippingFormData.lastname != null && shippingFormData.lastname.length == 0) {
                    toReturn = false;
                }
                if(shippingFormData.street != null) {
                    if(shippingFormData.street[0].length == 0) {
                        toReturn = false;
                    }
                }
                    
                if(shippingFormData.city != null && shippingFormData.city.length == 0) {
                    toReturn = false;
                }
                if(shippingFormData.telephone != null && shippingFormData.telephone.length == 0) {
                    toReturn = false;
                }
                if(shippingFormData.country_id != null &&shippingFormData.country_id.length == 0) {
                    toReturn = false;
                }
                if(shippingFormData.postcode.length == 0) {
                    toReturn = false;
                }
                if(shippingFormData.region_id != null) {
                    if(shippingFormData.region_id.length == 0 && shippingFormData.region.length == 0) {
                        toReturn = false;
                    }
                }
                return toReturn;
            },
            continueReview: function(){
                var payment = quote.paymentMethod();
                if(payment) {
                    var paymentTitle = payment.title;
                    console.log(payment);
                    $('#onestepcheckout-button-continue-to-review').hide();
                    $('.onestepcheckout-shipping-payment-review').hide();
                    $('#billing_step').addClass('done');
                    $('.nav-step').removeClass('active');
                    $('#review_step').addClass('active');
                    $('.onestepcheckout-order-review').show();
                    $('#onestepcheckout-button-place-order').css('display', 'block');
                    var paymentReviewText = $(".payment-method input[type=radio]:checked").parent('.payment-method-title').find('.label').find('span').text();
                    $('#payment-method-review').text(paymentReviewText);
                }
            },
            prepareToPlaceOrder: function(){
                var self = this;
                // Disable button
                $("#onestepcheckout-button-place-order").prop('disabled',true);

                if(!($("#agree_terms").is(":checked"))) {
                    this.showErrorMessage($t('Please agree to terms and conditions.'));
                    $("#onestepcheckout-button-place-order").prop('disabled',false);
                    return false;
                }
                if (!quote.paymentMethod()) {
                    alertPopup({
                        content: $t('Please choose a payment method!'),
                        autoOpen: true,
                        clickableOverlay: true,
                        focus: "",
                        actions: {
                            always: function(){

                            }
                        }
                    });
                    self.continueBillingStep();
                    $("#onestepcheckout-button-place-order").prop('disabled',false);
                }
                if(self.validateInformation() == true){
                    self.placingOrder(true);
                    Loader.all(true);
                    var deferred = saveAdditionalInformation();
                    var payment = quote.paymentMethod();
                    var paymentMethod = payment.method;
                    if (self.paymentMethodAllowPopup.indexOf(paymentMethod) > -1) {
                        self.placeOrder();
                    } else {
                        deferred.done(function () {
                            if ($('#allow_gift_messages').length > 0) {
                                var giftMessageDeferred;
                                if ($('#allow_gift_messages').attr('checked') == 'checked') {
                                    giftMessageDeferred = giftMessageView().submitOptions();
                                    giftMessageDeferred.done(function () {
                                        self.placeOrder();
                                    });
                                } else {
                                    giftMessageDeferred = giftMessageView().deleteOptions();
                                    giftMessageDeferred.done(function () {
                                        self.placeOrder();
                                    });
                                }
                            } else {
                                self.placeOrder();
                            }
                        });
                    }
                }else{
                    $("#onestepcheckout-button-place-order").prop('disabled',false);
                    //false
                }
            },

            placeOrder: function () {
                var self = this;
                var payment = quote.paymentMethod();
                var paymentMethod = payment.method;
                var checkoutButton = $("#co-payment-form ._active button[type='submit']");
                SaveAddressBeforePlaceOrder();
                if(this.isVirtual()){
                    if(checkoutButton.length > 0){
                        if (paymentMethod == 'braintree_paypal') {
                            self.braintreePaypalCheckout();
                        } else {
                            checkoutButton.click();
                        }
                        self.placingOrder(false);
                        Loader.all(false);
                    }
                }else{
                    if (self.paymentMethodAllowPopup.indexOf(paymentMethod) > -1) {
                        setShippingInformationAction().always(
                            function () {
                                shippingRateService().stop(false);
                            }
                        );
                        if(checkoutButton.length > 0){
                            if (paymentMethod == 'braintree_paypal') {
                                self.braintreePaypalCheckout();
                            } else {
                                checkoutButton.click();
                            }

                            self.placingOrder(false);
                            Loader.all(false);
                        }
                    } else {
                        setShippingInformationAction().always(
                            function () {
                                shippingRateService().stop(false);
                                if(checkoutButton.length > 0){
                                    checkoutButton.click();
                                    self.placingOrder(false);
                                    Loader.all(false);
                                }
                            }
                        );
                    }
                }
            },

            validateInformation: function(){
                var shipping = (this.isVirtual())?true:ValidateShippingInfo();
                var billing = this.validateBillingInfo();
                return shipping && billing;
            },
            
            afterRender: function(){
                $('#checkout-loader').removeClass('show');
            },
            
            validateBillingInfo: function(){
                if($("#co-payment-form ._active button[type='submit']").length > 0){
                    if($("#co-payment-form ._active button[type='submit']").hasClass('disabled')){
                        if($("#co-payment-form ._active button.update-address-button").length > 0){
                            this.showErrorMessage($t('Please update your billing address'));
                        }
                        return false;
                    }else{
                        return true;
                    }
                }
                return false;
            },
            showErrorMessage: function(message){
                var self = this;
                var timeout = 5000;
                self.errorMessage($t(message));
                setTimeout(function(){
                    self.errorMessage('');
                },timeout);
            },

            braintreePaypalCheckout: function () {
                var checkoutButton = $("#co-payment-form ._active button[type='submit']");
                var element = checkoutButton.get(0);
                var viewModel = ko.dataFor(element);

                if ($('.payment-method-description').is(":visible")) {
                    viewModel.placeOrder();
                } else {
                    viewModel.payWithPayPal();
                }
            }
        });
    }
);
