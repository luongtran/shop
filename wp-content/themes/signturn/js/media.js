
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
function hideTinyButtons(){
    $('#product-category-container .buttons.next,#product-category-container .buttons.prev,#product-category-container  #trigger-prev').hide();
}
function showTinyButtons(){
    $('#product-category-container  .buttons.next,#product-category-container .buttons.prev,#product-category-container  #trigger-prev').show();
}
function bindTinyCarouselY(){
     var w = $(window).width();
     console.log($("#slider1 .overview li").size());
     console.log(w);
     if(w>1200){
         if($("#slider1 .overview li").size()>4){
             showTinyButtons();
             $('#slider1').tinycarousel({axis   : "y"});
         }else{
             hideTinyButtons(); 
         }
     }else{
         if($("#slider1 .overview li").size()>3){
             showTinyButtons();
             $('#slider1').tinycarousel({axis   : "y"});
         }else{
             hideTinyButtons(); 
         } 
     }
}
function bindTinyCarouselX(){
    var w = $(window).width();
    var limit = -1;
    if(w<=430){
        limit = 2;
    }else if(w<=440){
        limit = 3;
    }else if(w<=560){
        limit = 4;
    }else if(w<=690){
        limit = 5;
    }else if(w<=820){
        limit = 6;
    }else if(w<=950){
        limit = 7;
    }else if(w<=991){
        limit = 7;
    }
    if($("#slider2 .overview li").not('.mirrored').size()>limit){
        $('#slider2').tinycarousel({axis   : "x"});
    }else{
        hideTinyButtons(); 
        console.log("phai lon hon "+limit+ " - moi co "+$("#slider2 .overview li").size());
    }
}
function bindPositionSlider(){
    var w = $(window).width();
    
    var h = $('#layerslider_2').height();
    var s1 = $('#slideshow-content-1');
    var s1w = $('#slideshow-content-1').width();
    var s1h = $('#slideshow-content-1').height();
    s1.css('right',(w/2 - s1w/2 - w*300/1249)+'px');
    s1.css('top',(h/2 - s1h/2 - h*50/525)+'px'); 
    
    var s2 = $('#slideshow-content-2');
    var s2w = $('#slideshow-content-2').width();
    var s2h = $('#slideshow-content-2').height();
    s2.css('right',(w/2 - s2w/2 - w*150/1249)+'px');
    s2.css('top',(h*80/525)+'px'); 
    
    var s3 = $('#slideshow-content-3');
    var s3w = $('#slideshow-content-3').width();
    var s3h = $('#slideshow-content-3').height();
    s3.css('right',(w/2 - s3w/2 - w*200/1249)+'px');
    s3.css('top',(h*120/525)+'px'); 
    
    var s4 = $('#slideshow-content-4');
    var s4w = $('#slideshow-content-4').width();
    var s4h = $('#slideshow-content-4').height();
    s4.css('right',(w/2 - s4w/2 - w*200/1249)+'px');
    s4.css('bottom',(h*100/525)+'px');
}
function setSliderSize(){
    
    var w = $(window).width();
    var h = (w*824/1600)+'px';
     console.log('height image '+h);
    $('img.ls-l').css('width',w+'px');

    $('img.ls-l').css('height',h);
    $('img.ls-l').parent().css('height',h);
    $('img.ls-l').parents('.ls-inner').css('height',h);
    $('#layerslider_2').css('height',h);
    $('.ls-wp-fullwidth-helper,.ls-wp-fullwidth-container,#site-content.site-content-home').css('height',h);
    $('#site-content.site-content-home').css('min-height',h);
    $('img.ls-l').parent().find('div.ls-l').css('width',w+'px');
    $('img.ls-l').parent().find('div.ls-l').css('height',h);
    $('img.ls-l').parent().find('div.ls-l').css('top',0);
    $('img.ls-l').parent().find('div.ls-l').css('left',0);
    $('img.ls-l').each(function(){
       
    });
}
function setLeftCarWinzar(){
    var w = $(window).width();
    if(w<=480){
       var winzarWidth = $('#booking-winzar').parent().find('h1').width()+60;
       console.log("window :" + w + "size "+winzarWidth);
       $('#booking-winzar').parent().find('h1').css('left',((w-winzarWidth)/2)+"px");
    }else{
        $('#booking-winzar').parent().find('h1').removeAttr('style');
    }
}
function setLeftForgotPassH1(){
    var w = $(window).width();
    if(w<=480){
       var h1Width = $('.page-id-16 h1').width()+60;
       $('.page-id-16 h1').css('left',((w-h1Width)/2)+"px");
    }else{
        $('.page-id-16 h1').removeAttr('style');
    }
}
$(document).ready(function(){
    bindTinyCarouselY();
    bindTinyCarouselX();
    setSliderSize();
    bindPositionSlider();
    setLeftCarWinzar();
    setLeftForgotPassH1();
});
var resizeId;
$( window ).resize(function() {
     resize992();
     bindTinyCarouselY();
     bindTinyCarouselX();
     setLeftCarWinzar();
     setLeftForgotPassH1();
     clearTimeout(resizeId);
     resizeId = setTimeout(doneResizing, 500);
});
function doneResizing(){
     setSliderSize();
     bindPositionSlider();
     
}
$( window ).on( "orientationchange", function( event ) {
     setSliderSize();
     bindPositionSlider();
     bindTinyCarouselY();
     bindTinyCarouselX();
     setLeftCarWinzar();
     setLeftForgotPassH1();
});

