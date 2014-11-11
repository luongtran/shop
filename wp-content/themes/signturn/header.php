<?php
/**
 * Header Template
 *
 * This template calls a series of functions that output the head tag of the document.
 * The body and div #main elements are opened at the end of this file. 
 * 
 * @package Thematic
 * @subpackage Templates
 */
 
	// Create doctype
	thematic_create_doctype();
	echo " ";
	language_attributes();
	echo ">\n";
	
	// Opens the head tag 
	thematic_head_profile();
	
	// Create the meta content type
	thematic_create_contenttype();
	
	// Create the title tag 
	thematic_doctitle();
	
	// Create the meta description
	thematic_show_description();
	
	// Create the tag <meta name="robots"  
	thematic_show_robots();
	
	// Legacy feedlink handling
	if ( current_theme_supports( 'thematic_legacy_feedlinks' ) ) {    
		// Creating the internal RSS links
		thematic_show_rss();
	
		// Create comments RSS links
		thematic_show_commentsrss();
	}
	// Create pingback adress
	thematic_show_pingback();
	
	/* The function wp_head() loads Thematic's stylesheet and scripts.
	 * Calling wp_head() is required to provide plugins and child themes
	 * the ability to insert markup within the <head> tag.
	 */
	wp_head();
?>
<meta name="viewport" content="width=device-width">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
</head>
<?php thematic_body();?>
<div id="page-loader">
    <span>Be Unique</span>
    <div class="loader"></div>
</div>
<div
  class="fb-like"
  data-share="true"
  data-width="450"
  data-show-faces="true">
</div>
<?php thematic_before();?>
<?php thematic_aboveheader(); ?>
<header id="header" >
        <div class="container">
            <div class="row">
                <div class="col-sm-4">
                    <div id="logo">
                        <a href="<?=  bloginfo('home')?>"><img class="img-responsive" src="<?php echo TEMPLATE_URL?>/images/logo.png" alt="" /></a>
                    </div>
                </div>
                <div class="col-sm-8 header-right relative">
                    <div class="top-menu text-right">
                        <ul class="list-unstyled list-inline">
                            <li id="withlove-toggle"><a href="">With Love</a>
                                <div id="widthlove" class="arrowbox col-xs-12 col-sm-6 col-md-6">
                                    <div class="widthlove-row">
                                        <h4>Samples</h4>
                                        <p>Choose a complementary same with each order</p>
                                    </div>
                                    <div class="widthlove-row">
                                        <h4>Gift Wrap</h4>
                                        <p>Present your gift with our Iconic matt black box </p>
                                    </div>
                                    <div class="widthlove-row">
                                        <h4>Bottles</h4>
                                        <p>Enjoy your stylish bottle with complement</p>
                                    </div>
                                </div>
                            </li>
                            <?php if ( is_user_logged_in() ): ?>
                            <li>
                                <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" >My Account</a>
                            </li>
                            <?php else :?>
                            <li>
                                <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" >My Account</a>
                            </li>
                            <?php endif;?>
                            <li id="cart-toggle">
                                <?php global $woocommerce; ?> 
                                <a class="cart-contents" href="<?php echo $woocommerce->cart->get_cart_url(); ?>" title="View your shopping cart">
                                    <span id="bag-count">
                                     <?php echo sprintf(_n('bag (%d)', 'bag (%d)', $woocommerce->cart->cart_contents_count, 'esell'), $woocommerce->cart->cart_contents_count);?> -  
                                    </span>
                                     <i class="fa fa-shopping-cart"></i>
                                </a>
                                <?php include_once STYLESHEETPATH.DIRECTORY_SEPARATOR.'woocommerce'.DIRECTORY_SEPARATOR.'global'.DIRECTORY_SEPARATOR.'cart_header.php'; ?>
                            </li>
                        </ul>
                        
                    </div>
                    <div class="menu" style="display: none;">
                        <nav class="navbar navbar-default" role="navigation">
                            <div class="container-fluid">
                                <!-- Brand and toggle get grouped for better mobile display -->
                                <div class="navbar-header">
                                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                                        <span class="sr-only">Toggle navigation</span>
                                        <span class="icon-bar"></span>
                                        <span class="icon-bar"></span>
                                        <span class="icon-bar"></span>
                                    </button>
                                </div>
                                <?php 
                                      wp_nav_menu(array(
                                        'menu' => 'ShopMenu',
                                        'theme_location' => 'primary',
                                        'depth' => 2,
                                        'container' => 'div',
                                        'container_class' => 'navbar-collapse bs-navbar-collapse collapse',
                                        'container_id' => 'bs-example-navbar-collapse-1',
                                        'menu_class' => 'nav navbar-nav',
                                        'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
                                        'walker' => new wp_bootstrap_navwalker())
                                    );
                                ?>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div id="primary-menu">
        <div class="container relative">
            <ul class="list-inline list-unstyled toggle-list">
                <li <?php if(is_product()|| is_product_category()) echo 'class="actived"' ?> data-toggle=".collections"><a href="#">The Collection</a></li>
                <li <?php 
                        if(is_page('ethical-sourcing') ||is_page('packaging') || is_page('reviews')|| is_page('refill-politique') || is_page('our-bottles') ) echo 'class="actived"'
                    ?>  data-toggle=".world-of-signature"><a href="#">Signature World</a></li>
                <li <?php 
                    $singleNews = false;
                    if(is_single()){
                        $cats = get_the_category();
                        foreach ($cats as $cat) {
                            if($cat->slug==='news'){
                                $singleNews = true;
                                break;
                            }
                        }
                    }
                    if(is_category('news') || ($singleNews)) echo 'class="actived"' 
                    ?> data-toggle=".news"><a href="#">News</a></li>
                <li <?php if(is_page('about-us')) echo 'class="actived"' ?>><a href="<?= get_permalink(get_page_by_path('about-us'))?>">About us</a></li>
                <li <?php if(is_page('contact-us')) echo 'class="actived"' ?>><a href="<?= get_permalink(get_page_by_path('contact-us'))?>">Contact Us</a></li>
            </ul>
        </div>
    </div>
<div id="primary-menu-items" style="display: none">
    <div class="container ">
        <ul class="list-unstyled desktop-menu">
            <li class="collections row ">
                <div class="col-sm-5">
                    <h4>Collections</h4>
                    <?php 
                        $forHimLink = get_term_link('for-men','product_cat'); //get_category_link(get_cat_ID('for-men'));
                        //die($forHimLink);
                        $forHerLink =  get_term_link('women','product_cat'); //get_category_link(get_cat_ID('women'));
                        $floralLink =  get_term_link('floral','product_cat'); //get_category_link(get_cat_ID('floral'));
                        $freshLink =  get_term_link('fresh','product_cat'); // get_category_link(get_cat_ID('fresh'));
                        $orientalWoodsLink = get_term_link('oriental-woods','product_cat'); // get_category_link(get_cat_ID('oriental-woods'));
                        $sweetLink = get_term_link('sweet','product_cat'); // get_category_link(get_cat_ID('sweet'));
                        
                    ?>
                    <ul class="list-unstyled clearfix" id="collection-list" >
                        <li data-toggle=".for-men"><a href="<?php echo $forHimLink?>">For him</a></li>
                        <li data-toggle=".women"><a href="<?=$forHerLink?>">For her</a></li>
                        <li data-toggle=".floral"><a href="<?=$floralLink?>">Floral</a></li>
                        <li data-toggle=".fresh"><a href="<?=$freshLink?>">Fresh</a></li>
                        <li data-toggle=".oriental-woods"><a href="<?=$orientalWoodsLink?>">Oriental woods</a></li>
                        <li data-toggle=".sweet"><a href="<?=$sweetLink?>">Sweet</a></li>
                    </ul>
                    <?php
                        $args = array( 'taxonomy' => 'product_cat' );
                        $terms = get_terms('product_cat', $args);
                            foreach ($terms as $term): 
                            $description = $term->description;
                            $tmpArr = explode(PHP_EOL,$description);
                            $title = '';
                            if($tmpArr[0]!==$description){
                                $title = $tmpArr[0];
                                $description = str_replace($tile,'', $description);
                            }
                    ?>
                    <div class="collection-des <?php echo $term->slug?>">
                        <h5><?php echo $title ?></h5>
                        <p><?php echo $description; ?></p>
                    </div>
                    <?php endforeach;?>
                </div>
                <div class="col-sm-7">
                    <ul class="list-unstyled clearfix" id="collection-list-img">
                        <li  data-toggle=".for-men">
                            <a href="<?=$forHimLink?>">
                                <img class="img-responsive" src="<?php echo TEMPLATE_URL?>/images/menu/for_him.jpg" alt="For Him"/>
                                <p>For Him</p>
                            </a>
                        </li>
                        <li data-toggle=".women">
                            <a href="<?=$forHerLink?>">
                                <img class="img-responsive" src="<?php echo TEMPLATE_URL?>/images/menu/for_her.jpg" alt="For Her"/>
                                <p>For Her</p>
                            </a>
                        </li>
                        <li data-toggle=".floral">
                            <a href="<?=$floralLink?>">
                                <img class="img-responsive" src="<?php echo TEMPLATE_URL?>/images/menu/floral.jpg" alt="Floral"/>
                                <p>Floral</p>
                            </a>
                        </li>
                        <li data-toggle=".sweet">
                            <a href="<?=$sweetLink?>">
                                <img class="img-responsive" src="<?php echo TEMPLATE_URL?>/images/menu/sweet.jpg" alt="Sweet"/>
                                <p>Sweet</p>
                            </a>
                        </li>
                        <li data-toggle=".fresh">
                            <a href="<?=$freshLink?>">
                                <img class="img-responsive" src="<?php echo TEMPLATE_URL?>/images/menu/fresh.jpg" alt="Fresh"/>
                                <p>Fresh</p>
                            </a>
                        </li>
                        <li  data-toggle=".oriental-woods">
                            <a href="<?=$orientalWoodsLink?>">
                                <img class="img-responsive" src="<?php echo TEMPLATE_URL?>/images/menu/oriental_wood.jpg" alt="oriental wood"/>
                                <p>Oriental Wood</p>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="news row">
                <?php if(can_show_newsletter_form()): ?>
                        <script type="text/javascript">
                        //<![CDATA[
                        if (typeof newsletter_check !== "function") {
                        window.newsletter_check = function (f) {
                            var re = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-]{1,})+\.)+([a-zA-Z0-9]{2,})+$/;
                            if (!re.test(f.elements["ne"].value)) {
                                alert("The email is not correct");
                                return false;
                            }
                            if (f.elements["ny"] && !f.elements["ny"].checked) {
                                alert("You must accept the privacy statement");
                                return false;
                            }
                            return true;
                        }
                        }
                        //]]>
                        </script>
                    <div id="what-new">
                        <form class="row" method="post" action="<?php echo site_url()?>/wp-content/plugins/newsletter/do/subscribe.php" onsubmit="return newsletter_check(this)">
                            <div class="col-sm-5 label-what-new" style="line-height: 35px">What New Is Signature Word</div>
                            <div class="col-sm-5">
                                <input  name="ne" size="30" required type="email" placeholder="enter your email address" class="site-input form-control" />
                            </div>
                            <div class="col-sm-2 no-padding">
                                <input style="line-height: 35px" class="newsletter-submit btn-text no-padding" type="submit" value="Sign Up Now"/>
                            </div>
                        </form>
                    </div>
                    <?php endif?>
                <div class="col-sm-4 news-postings">
                    <h4>Our News</h4>
                    <ul class="" style="padding-left: 0;">
                    <?php  query_posts( array ( 'category_name' => 'news', 'posts_per_page' => 3 ) ); ?>
                    <?php 
                        while (have_posts()) : the_post(); 
                    ?>
                        <li>
                            <a href="<?php the_permalink() ?>"><?php the_title() ?></a>
                            <p><?php the_time('F j, Y'); ?></p>
                        </li>    
                    <?php endwhile; 
                        wp_reset_query();
                    ?>
                    </ul>
                    <p style="font-weight: bold"><a href="<?=get_category_link(get_cat_ID('news'))?>">All news items</a></p>
                </div>
                <?php  query_posts( array ( 'category_name' => 'news', 'posts_per_page' => 1 ) );
                    while(have_posts()) :the_post();
                ?>
                <div class="col-sm-4">
                    <?php echo get_the_post_thumbnail(get_the_ID(), 'full',['class'=>'img-responsive news-img']);   ?>
                </div>
                <div class="col-sm-4">
                    <?php the_content();?>
                </div>
                <?php endwhile; 
                    wp_reset_query();
                ?>
            </li>
            <li class="world-of-signature">
                <h4>Signature World</h4>
                <ul class="list-unstyled list-inline">
                    <li><a href="<?= get_permalink(get_page_by_path('ethical-sourcing'))?>">Ethical Sourcing</a></li>
                    <li><a href="<?= get_permalink(get_page_by_path('packaging'))?>">Our Packaging</a></li>
                    <li><a href="<?= get_permalink(get_page_by_path('reviews'))?>">Reviews</a></li>
                    <li><a href="<?= get_permalink(get_page_by_path('refill-politique'))?>">Refill Politique</a></li>
                    <li><a href="<?= get_permalink(get_page_by_path('our-bottles'))?>">Our Bottles</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>
        <?php thematic_belowheader();?>
        <?php 
            if(is_page()){
                global $post;
                $post_slug=$post->post_name;
                $siteContentClass = 'site-content-'.$post_slug;
            }else{
                $siteContentClass = '';
            }
        ?>
        <div id="site-content" class="<?php echo $siteContentClass;?>">
            <div class="<?php if(!is_page('home') && !is_product_category()){ echo "container";} ?>">