jQuery(document).ready(function() 
{
    jQuery('input[data-event=visible]').change(function(){ 
       
        var className = jQuery(this).attr("name") + '-' + jQuery(this).data('event');

        if(jQuery(this).attr("checked")){ 
            
            jQuery('.'+className).fadeIn();         
        } else { 
            jQuery('.'+className).fadeOut(100);
        } 
    });

    jQuery('select[data-event=visible]').change(function(){ 
        var className = jQuery(this).attr("name") + '-' + jQuery(this).data('event');

        if(jQuery(this).val() == 'disable'){ 
            jQuery('.'+className).fadeOut(100);      
        } else { 
            jQuery('.'+className).fadeIn();
        }
    });
    
    jQuery('.festi-checkout-steps-wizard-help-tip').poshytip({
        className: 'tip-twitter',
        showTimeout:100,
        alignTo: 'target',
        alignX: 'center',
        alignY: 'bottom',
        offsetY: 5,
        allowTipHover: false,
        fade: true,
        slide: false
    });
    
    jQuery(document).ready(function() {
        jQuery( "#sortable" ).sortable();
    });

    jQuery('.festi-disabled').each(function(index) {
            
            if (jQuery(this).attr('name') == 'enablePlugin') {
                return;
            }
        
            jQuery(this).before( '<div class="festi-overlay festi-tooltipe" id="festi-overlay-'+ index +'"></div>');
            if (jQuery(this).attr('id') != 'sortable') {
                var height = jQuery(this).outerHeight()+3;
        
                var width = jQuery(this).outerWidth()+3;
            } else { 
                var height = jQuery(this).parent().outerHeight()-20;
            
                var width = 170; 
            } 

            
            jQuery('.festi-overlay#festi-overlay-' + index).outerHeight(height);
            jQuery('.festi-overlay#festi-overlay-' + index).outerWidth(width);
    });
    
    jQuery('.festi-tooltipe').attr('title',  'To enable option - get PRO version');

    jQuery('.festi-tooltipe').poshytip({
        className: 'tip-twitter',
        showTimeout:100,
        alignTo: 'target',
        alignX: 'center',
        alignY: 'bottom',
        offsetY: 5,
        allowTipHover: false,
        fade: true,
        slide: false
    });

}); 