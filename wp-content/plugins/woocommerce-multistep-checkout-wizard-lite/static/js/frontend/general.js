jQuery(document).ready(function() {
    // Init Wizard

    if (fesiCheckoutSteps.isAuthorizedUser == false && jQuery.inArray('login', fesiCheckoutSteps.disableSteps) < 0) {
       var nextButtonTitle = fesiCheckoutSteps.noAccountButton
    } else{
       var nextButtonTitle = fesiCheckoutSteps.nextButton
    }
    var nextButtonTitle

    jQuery("#festi-checkout-steps-wizard").steps(
        {
            transitionEffectSpeed: 0,
            startIndex: 0,
            autoFocus: true,
            enableAllSteps: false,
            transitionEffect: 'slideLeft',
            titleTemplate: fesiCheckoutSteps.titleTemplate,
            cssClass: "festi-wizard",
            labels: {
                cancel: "Cancel",
                pagination: "Pagination",
                finish: fesiCheckoutSteps.finishButton,
                next: nextButtonTitle,
                previous: fesiCheckoutSteps.previousButton,
                loading: "Loading ..."
            },
            onFinished: function (event, currentIndex) {
                jQuery("#place_order").click();
            },
            onStepChanged: function (event, currentIndex, priorIndex)
            {
                if (currentIndex == 0 && fesiCheckoutSteps.isAuthorizedUser == false && jQuery.inArray('login', fesiCheckoutSteps.disableSteps) < 0) {
                    jQuery('#festi-checkout-steps-wizard a[href="#next"]').html(fesiCheckoutSteps.noAccountButton);
                    jQuery('#festi-checkout-steps-wizard a[href="#previous"]').hide();
                } else {
                    jQuery('#festi-checkout-steps-wizard a[href="#next"]').html(fesiCheckoutSteps.nextButton);
                    jQuery('#festi-checkout-steps-wizard a[href="#previous"]').show();
                }
            },
        }
    );
    
    var height = jQuery('#festi-checkout-steps-wizard-p-0').css('position', 'relative');
    jQuery('#festi-checkout-steps-wizard.festi-wizard .content').css('height', height + 'px');
    
    jQuery("#festi-checkout-steps-wizard").show();
    jQuery('form[name="checkout"]').css('visibility', 'visible');

    // User Login
    function appendErrorRequiredClasses(selector)
    {
        jQuery('form.login input#' + selector).parent().removeClass("woocommerce-validated");
        jQuery('form.login input#' + selector).parent().addClass("woocommerce-invalid woocommerce-invalid-required-field");
        jQuery('form.login input#' + selector).parent().addClass("validate-required");
    }
    
    jQuery('form.login').submit(function() 
    {
        var form = 'form.login';
        var error = false;
        
        if (jQuery(form + ' input#username').val() == false) {
            error = true;
            appendErrorRequiredClasses('username');
        }
        
        if (jQuery(form + ' input#password').val() == false) {
           error = true;
           appendErrorRequiredClasses('password');
        }
        
        if (error != false)
        {
            return false;
        }
        

        if (jQuery(form + ' input#rememberme').is(':checked') == false) {
            rememberme = false;
        } else {
            rememberme = true; 
        }

        var data = {
            action: 'login_user_wizard_step',
            username: jQuery(form + ' input#username').val(),
            password: jQuery(form + ' input#password').val(),
            rememberme: rememberme
        };
        
         jQuery.post(fesiCheckoutSteps.ajaxurl, data, function(response) {
            if (response == 'successfully') {
                location.reload();
            } else {
                jQuery('div.festi-wizard-login-error').remove();
                jQuery('form.login').prepend(response);
            }
        })
        return false;
    });
    
})