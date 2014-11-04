
function resize992(){
    var w = $(window).width();
    var h = $(window).height();
    //console.clear();
    //console.log(w);
    if(w>=992){
        //alert(w);
        $('#slider1 ul').removeAttr('style');
        var listProductHeight =  $('#list-product-of-cat').height();
        var listProductParentHeight = $('#list-product-of-cat').parent().height();
        //alert(listProductParentHeight);
        $('#list-product-of-cat').css('top',(listProductParentHeight-listProductHeight)/2);
    }
}
$(window).load(function(){
    resize992();
});
$(document).ready(function(){
    $('#slider1').tinycarousel({axis   : "y"});
    $('#slider2').tinycarousel({axis   : "x"});
    $('img.ls-l').each(function(){
        var w = $(window).width();
        console.log('window '+w);
        $(this).css('width',w+'px');
        var h = (w*824/1600)+'px';
        $(this).css('height',h);
        $(this).parent().css('height',h);
        $(this).parents('.ls-inner').css('height',h);
        $('#layerslider_2').css('height',h);
        $('.ls-wp-fullwidth-helper,.ls-wp-fullwidth-container,#site-content').css('height',h);
        $('#site-content').css('min-height',h);
    });
});
$( window ).resize(function() {
     resize992();
      $('img.ls-l').each(function(){
        var w = $(window).width();
        console.log('window '+w);
        $(this).css('width',w+'px');
        var h = (w*824/1600)+'px';
        $(this).css('height',h);
        $(this).parent().css('height',h);
        $(this).parents('.ls-inner').css('height',h);
        $('#layerslider_2').css('height',h);
        $('.ls-wp-fullwidth-helper,.ls-wp-fullwidth-container,#site-content').css('height',h);
        $('#site-content').css('min-height',h);
    });
});

