jQuery( document ).ready(function() {    

    jQuery(".woocommerce .showlogin").parent().hide();
    
    var festiWizardBlock = jQuery("div#festi-checkout-steps-wizard");
    festiWizardBlock.clone(true).prependTo(".woocommerce .checkout");
    festiWizardBlock.remove();
    
    if (jQuery.inArray('login', fesiCheckoutSteps.disableSteps) < 0 && fesiCheckoutSteps.isAuthorizedUser != true) {
        var loginForm = jQuery("form.login");
        loginForm.clone(true).appendTo('.festi-wizard-step-login');
        loginForm.remove();
        jQuery(".festi-wizard-step-login form.login").show();
    }
    
    

    var woocommerceBilling = jQuery("form.checkout .woocommerce-billing-fields");
    woocommerceBilling.clone(true).appendTo('.festi-wizard-step-billing');
    woocommerceBilling.remove();
    
    var woocommerceShipping = jQuery("form.checkout .woocommerce-shipping-fields");
    
    if (jQuery.inArray('shipping', fesiCheckoutSteps.disableSteps) < 0) {
        woocommerceShipping.appendTo('.festi-wizard-step-shipping');
    } else{
        woocommerceShipping.hide();
    }
        
    var woocommercePayment = jQuery("#payment");
    if (jQuery.inArray('payment', fesiCheckoutSteps.disableSteps) < 0) {
        woocommercePayment.clone(true).appendTo('.festi-wizard-step-payment');
    } else{
        jQuery("#place_order").hide();
        jQuery("#place_order").clone(true).appendTo(".woocommerce .checkout");
        woocommercePayment.hide();
    }
    
    var woocommerceOrder = jQuery("#order_review");
    if (jQuery.inArray('reviewOrder', fesiCheckoutSteps.disableSteps) < 0) {
        woocommerceOrder.clone(true).appendTo('.festi-wizard-step-view-order');
        woocommerceOrder.remove();
    } else {
        woocommerceOrder.hide();
    }

    jQuery("#order_review_heading").remove();
    jQuery(".woocommerce-billing-fields h3").remove();
    
    jQuery('div.festi-wizard-step-payment').on('click', '#payment input[name="payment_method"]', function() 
    {      
        var selector = jQuery(this).attr('id');
        jQuery('.payment_box').slideUp(200);
        jQuery('.payment_box.' + selector).slideDown(200);
    });
    
    if (jQuery('.festi-wizard-step-custom').length > 0) {
        jQuery('.festi-wizard-step-custom').append( "<div class='festi-woocommerce-custom-fields'></div>" );
        
        jQuery.each(fesiCheckoutSteps.customFields, function() {
            var field = jQuery("#" + this + "_field");
            field.clone(true).appendTo('.festi-woocommerce-custom-fields');
            field.remove();
        });
    }

    if (jQuery('form.checkout input#terms').length > 0) {
        var woocommerceTermsCondition = jQuery('#terms').parent();
        woocommerceTermsCondition.clone(true).appendTo('.festi-wizard-step-' + fesiCheckoutSteps.termsLocation);
        woocommerceTermsCondition.remove();
    }
})