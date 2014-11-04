            </div> <!-- end#site-content-->
        </div><!-- end .container -->
        <?php thematic_abovefooter(); ?>
        <div id="footer-container">
        <footer id="footer">
             <div class="container">
                  <div class="row">
                      <div class="col-sm-6 col-xs-12">
                            <div class="row" id="footer-column-1">
                                <div class="col-sm-4 ">
                                    <h3>SHOP</h3>
                                    <ul class="list-unstyled">
                                        <li><a href="#" > For Him</a></li>
                                        <li><a href="#" >For her</a></li>
                                        <li><a href="#" >Floral</a></li>
                                        <li><a href="#" >Sweet</a></li>
                                        <li><a href="#" >Fresh</a></li>
                                        <li><a href="#" >Oriental</a></li>
                                    </ul>
                                </div>
                                <div class="col-sm-4">
                                    <h3>CUSTOMER CARE</h3>
                                    <ul class="list-unstyled">
                                        <li><a href="about-us" >About Us</a></li>
                                        <li><a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" >Account Info</a></li>
                                        <li><a href="privacy-policy" >Privacy Policy</a></li>
                                        <li><a href="delivery-returns" >Delivery & Returns</a></li>
                                        <li><a href="terms-conditions" >Terms & Conditions</a></li>
                                        <li><a href="faqs" >FAQ's</a></li>
                                    </ul>
                                </div>
                                
                                <div class="col-sm-4">
                                    <h3>COMMUNITY</h3>
                                    <ul class="list-unstyled">
                                        <li><a href="#" ><i class="fa fa-phone" ></i> Contact Us</a></li>
                                        <li><a href="#" ><i class="fa fa-facebook"></i> Facebook</a></li>
                                        <li><a href="#" ><i class="fa fa-twitter"></i> Twitter</a></li>
                                        <li><a href="#" ><i class="fa fa-google-plus"></i> Google+</a></li>
                                        <li><a href="#" ><i class="fa fa-instagram"></i> Instagram</a></li>
                                    </ul>
                                </div>
                          </div>
                      </div>
                    
                      <div class="col-sm-6 col-xs-12">
                          <h3>HOW IT ALL BEGAN</h3>
                          <p>Signature Fragrances™ was founded by two friends from London. Having an passion for all things perfumed, the pair decided to actualize their strong passion for perfumery by introducing a diverse range of fragrances that everybody would enjoy. </p>
                      </div>
                  </div>
              </div>
        </footer>
            <div id="copyright">
            <div class="container">
                &copy; Signature Fragrances All Rights Reserved 2014
            </div>
        </div>
        </div>
        <?php thematic_belowfooter();?>
        <?php wp_footer(); ?>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="<?=TEMPLATE_URL?>/js/bootstrap-hover-dropdown.js"></script>
        <script src="<?=TEMPLATE_URL?>/js/jquery-2.0.3.min.js"></script>
        <script src="<?=TEMPLATE_URL?>/js/bootstrap.min.js"></script>
        <script src="<?=TEMPLATE_URL?>/js/bootstrap-hover-dropdown.js"></script>
        <script src="<?=TEMPLATE_URL?>/js/iCheck/icheck.min.js"></script>
        <script src="<?=TEMPLATE_URL?>/js/jquery.tinycarousel.min.js"></script>
        <script src="<?=TEMPLATE_URL?>/js/jquery.raty.js"></script>
        <script src="<?=TEMPLATE_URL?>/js/jquery.pajinate.min.js"></script>
        <script src="<?=TEMPLATE_URL?>/js/jquery.validate.js"></script>
        <script src="<?=TEMPLATE_URL?>/js/wow/wow.min.js"></script>
        <script type="text/javascript">
            function activeToggle(dom){
                $('#primary-menu .toggle-list li').removeClass('active');
                $(dom).addClass('active');
            }
            function removeActiveToggle(){
                 $('#primary-menu .toggle-list li').removeClass('active');
            }
            $('#primary-menu .toggle-list li[data-toggle]').on('click',function(){
               
               console.firebug=true;
                var itemDom =  $($(this).data('toggle'));
                if($('#primary-menu-items').is(':visible')){
                    console.log('show');
                    if(itemDom.is(':visible')){
                        $('#primary-menu-items').slideUp(500);
                        removeActiveToggle();
                    }else{
                        $('#primary-menu-items .desktop-menu > li').not(itemDom).hide();
                        itemDom.show();
                        activeToggle(this);
                    }
                }else{
                   if(itemDom.not(':visible')){
                        $('#primary-menu-items .desktop-menu > li').not(itemDom).hide();
                        itemDom.show();
                        $('#primary-menu-items').slideDown(500);
                        activeToggle(this);
                    } 
                }
            });
            $('#collection-list-img li').mouseover(function(){
                //alert('fefe');
                var domClass = $(this).data('toggle');
                var currentDom = $('.collection-des'+domClass);
                $('.collection-des').not(currentDom).hide();
                currentDom.show();
                console.log(currentDom);
            });
            $(window).load(function(){
                new WOW().init();
            });
            $('form.jqueryvalidate').validate();
            
            $('.reviews-item-raty').raty(
                {
                    starOff : '<?=TEMPLATE_URL?>/images/radio-off.png',
                    starOn  : '<?=TEMPLATE_URL?>/images/radio-on.png',
                    scoreName : 'rating',
                    score : 4
                });
             $('.reviews-item-ratied').each(function(){
                    var score = $(this).data('score');
                    $(this).raty({
                        starOff : '<?=TEMPLATE_URL?>/images/radio-off.png',
                        starOn  : '<?=TEMPLATE_URL?>/images/radio-on.png',
                        scoreName : 'rating',
                        score : score,
                        readOnly   :true
                    });
             });
            $("#customer-reviews-close").on('click',function(){
                $('#customer-reviews').hide();
            });
            $("#shipping-data-close").on('click',function(){
                $('#shipping-data').hide();
            });
            $("#customer-service-data-close").on('click',function(){
               $('#customer-service').toggle();
            });
            $('#shipping-popover').on('click',function(){
                $('#shipping-data').toggle();
                $('#customer-reviews').hide();
                $('#customer-service').hide();
            });
            $('#review-popover').on('click',function(){
                $('#customer-reviews').toggle();
                $('#shipping-data').hide();
                $('#customer-service').hide();
            });
            $('#customer-service-popver').on('click',function(){
                $('#customer-service').toggle();
                $('#customer-reviews').hide();
                $('#shipping-data').hide();
            });
            $('#single-product-description p').each(function(){
                //alert($(this).html()+'fefefe');
                if( $.trim($(this).html())==='&nbsp;' || $.trim($(this).html())===''){
                    $(this).remove();
                }else{
                   
                }
                 
            });
            
        </script>
        <script type="text/javascript">
            $(window).load(function() {
                $("#page-loader").fadeOut("slow",function(){
                    $('body').removeClass('loading');
                });
            });
                $('#trigger-prev').on('click',function(){
                    $('#slider1 .prev').trigger('click');
                });
	</script>
        <?php thematic_after();  ?>
        <script>
            $('#checkout-delivery-builling1 input[type="radio"]').iCheck({
                radioClass: 'iradio_minimal',
            });
//            $('#shipping-popover').popover({
//                'html':true,
//                'content':$('#shipping-data').html(),
//                'template':'<div class="popover shipping" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
//            });
//            $('#shipping-popover').on('hover',function(){
//               $('#shipping-popover').popover('show');
//            });
//            $('#shipping-popover').on('focusout',function(){
//                 $(this).popover('hide');
//            });
        </script>
        
        <script src="<?=TEMPLATE_URL?>/js/media.js"></script>
    </body>
</html>