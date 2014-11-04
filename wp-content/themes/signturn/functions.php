<?php
include_once 'lib/const.php';
include_once 'lib/vender/wp_bootstrap_navwalker.php';

/**
 * Custom Child Theme Functions
 *
 * This file's parent directory can be moved to the wp-content/themes directory 
 * to allow this Child theme to be activated in the Appearance - Themes section of the WP-Admin.
 *
 * Included is a basic theme setup that will add support for custom header images and custom 
 * backgrounds. There are also a set of commented theme supports that can be uncommented if you need
 * them for backwards compatibility. If you are starting a new theme, these legacy functionality can be deleted.  
 *
 * More ideas can be found in the community documentation for Thematic
 * @link http://docs.thematictheme.com
 *
 * @package ThematicSampleChildTheme
 * @subpackage ThemeInit
 */


/* The Following add_theme_support functions 
 * will enable legacy Thematic Features
 * if uncommented.
 */
 
// add_theme_support( 'thematic_legacy_feedlinks' );
// add_theme_support( 'thematic_legacy_body_class' );
// add_theme_support( 'thematic_legacy_post_class' );
// add_theme_support( 'thematic_legacy_comment_form' );
// add_theme_support( 'thematic_legacy_comment_handling' );

/**
 * Define theme setup
 */
function childtheme_setup() {
	
	/*
	 * Add support for custom background
	 * 
	 * Allow users to specify a custom background image or color.
	 * Requires at least WordPress 3.4
	 * 
	 * @link http://codex.wordpress.org/Custom_Backgrounds Custom Backgrounds
	 */
	add_theme_support( 'custom-background' );
	
	
	/**
	 * Add support for custom headers
	 * 
	 * Customize to match your child theme layout and style.
	 * Requires at least WordPress 3.4
	 * 
	 * @link http://codex.wordpress.org/Custom_Headers Custom Headers
	 */
	add_theme_support( 'custom-header', array(
		// Header image default
		'default-image' => '',
		// Header text display default
		'header-text' => true,
		// Header text color default
		'default-text-color' => '000',
		// Header image width (in pixels)
		'width'	=> '940',
		// Header image height (in pixels)
		'height' => '235',
		// Header image random rotation default
		'random-default' => false,
		// Template header style callback
		'wp-head-callback' => 'childtheme_header_style',
		// Admin header style callback
		'admin-head-callback' => 'childtheme_admin_header_style'
		) 
	);
	
}
add_action('thematic_child_init', 'childtheme_setup');


/**
 * Custom Image Header Front-End Callback
 *
 * Defines the front-end style definitions for 
 * the custom image header.
 * This style declaration will be output in the <head> of the
 * document just before the closing </head> tag.
 * Inline Syles and !important declarations 
 * can be used to override these styles.
 *
 * @link http://codex.wordpress.org/Function_Reference/get_header_image get_header_image()
 * @link http://codex.wordpress.org/Function_Reference/get_header_textcolor get_header_textcolor()
 */
function childtheme_header_style() {
	?>	
	<style type="text/css">
	<?php
	/* Declares the header image from the settings
	 * saved in WP-Admin > Appearance > Header
	 * as the background-image for div#branding.
	 */
	if ( get_header_image() && HEADER_IMAGE != get_header_image() ) {
		?>
		#branding {
			background:url('<?php header_image(); ?>') no-repeat 0 100%;
			margin-bottom:28px;
    		padding:44px 0 <?php echo HEADER_IMAGE_HEIGHT; ?>px 0; /* Bottom padding is the same height as the image */
    		overflow: visible;
}
		}
		<?php if ( 'blank' != get_header_textcolor() ) { ?>
		#blog-title, #blog-title a {
			color:#000;
		}
		#blog-description {	
			padding-bottom: 22px;
		}
		<?php
		}
		
	}
	?>
	<?php
	/* This delcares text color for the Blog title and Description
	 * from the settings saved in WP-Admin > Appearance > Header\
	 * If not set the deafault color is set to #000 
	 */
	if ( get_header_textcolor() ) {
		?>
		#blog-title, #blog-title a, #blog-description {
			color:#<?php header_textcolor(); ?>;
		}
		<?php
	}
	/* Removes header text if the
	 * "Do not diplay header text…" setting is saved
	 * in WP-Admin > Appearance > Header
	 */
	if ( ! display_header_text() ) {
		?>
		#branding {
			background-position: center bottom;
			background-repeat: no-repeat;
			margin-top: 32px;
		}
		#blog-title, #blog-title a, #blog-description {
			display:none;
		}
		#branding { 
			height:<?php echo HEADER_IMAGE_HEIGHT; ?>px; 
			width:940px;
			padding:0; 
		}
		<?php
	}
	?>
	</style>
	<?php
}


/**
 * Custom Image Header Admin Callback
 *
 * Callback to defines the admin (back-end) style
 * definitions for the custom image header.
 * Customize the css to match your theme defaults.
 * The !important declarations override inline admin styles
 * to better represent a WYSIWYG of the front-end styling
 * that this child theme is currently designed to display.
 */
function childtheme_admin_header_style() {
	?>
	<style type="text/css">
	#headimg {
		background-position: left bottom; 
		background-repeat:no-repeat;
		border:0 !important;   
		height:auto !important;
		padding:0 0 <?php echo HEADER_IMAGE_HEIGHT + 22; /* change the added integer (22) to match your desired top padding */?>px 0;
		margin:0 0 28px 0;
	}
	
	#headimg h1 {
	    font-family:Arial,sans-serif;
	    font-size:34px;
	    font-weight:bold;
	    line-height:40px;
		margin:0;
	}
	#headimg a {
		color: #000;
		text-decoration: none;
	}
	#desc{
		font-family: Georgia;
    	font-size: 13px;
    	font-style: italic;
    }
	</style>
	<?php
}

add_filter('body_class','add_body_loading_class');
function add_body_loading_class($classes) {
    // add ‘class-name’ to the $classes array
    $classes[] = 'loading';
    // return the $classes array
    return $classes;
}

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
function custom_override_checkout_fields( $fields ) {
     $fields['billing']['billing_title'] = array(
            'label'     => __('Title', 'woocommerce'),
            'placeholder'   => _x('Mr', 'placeholder', 'woocommerce'),
            'required'  => true,
            'class'     => array('form-row-wide'),
            'clear'     => false
     );
     $fields['billing']['delivery_instructions'] = array(
            'type'      => 'textarea',
            'label'     => __('Delivery Instructions', 'woocommerce'),
            'required'  => false,
            'class'     => array('form-row-wide'),
            'clear'     => false
     );
     $fields['billing']['billing_city']['lavel'] = __('City', 'woocommerce');
     unset($fields['billing']['billing_company']);
     unset($fields['billing']['billing_state']);
     unset($fields['billing']['billing_email']);
     
     $fields['shipping']['shipping_title'] = array(
            'label'     => __('Title', 'woocommerce'),
            'placeholder'   => _x('Mr', 'placeholder', 'woocommerce'),
            'required'  => true,
            'class'     => array('form-row-wide'),
            'clear'     => false
     );
     //Delivery Instructions
     //$fields['shipping']['shipping_city']['lavel'] = __('City', 'woocommerce');
     unset($fields['shipping']['shipping_company']);
     unset($fields['shipping']['shipping_state']);
     unset($fields['shipping']['shipping_email']);
     return $fields;
}

function woocommerce_template_single_description(){
    wc_get_template( 'single-product/description.php' );
}

/** SHARE FRODUCT **/
remove_action('the_content', 'ppss_twitter_facebook_contents');

/** DETAIL PRODUCT **/
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
remove_action( 'woocommerce_product_tabs', 'woocommerce_default_product_tabs' );
remove_action( 'woocommerce_product_tabs', 'woocommerce_sort_product_tabs', 99 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 7 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_description', 20 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 22 );
//add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 11 );

function woocommerce_template_loop_product_thumbnail(){
    echo woocommerce_get_product_thumbnail('post-thumbnail');
}
function woo_related_products_limit() {
    global $product;
    $args['posts_per_page'] = 6;
    return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'jk_related_products_args' );
    function jk_related_products_args( $args ) {

    $args['posts_per_page'] = 15; // 4 related products
    $args['columns'] = 1; // arranged in 2 columns
    return $args;
}
function woocommerce_product_loop_start_for_related($echo = true){
    ob_start();
?>
        <ul class="products  overview">
<?php
    if ( $echo )
            echo ob_get_clean();
    else
            return ob_get_clean();
}

add_filter( 'woocommerce_breadcrumb_defaults', 'jk_change_breadcrumb_delimiter' );
function jk_change_breadcrumb_delimiter( $defaults ) {
// Change the breadcrumb delimeter from '/' to '>'
$defaults['delimiter'] = ' &gt; ';
    return $defaults;
}
add_filter( 'add_to_cart_text', 'woo_custom_cart_button_text' ); // < 2.1
add_filter( 'woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text' ); // 2.1 +
function woo_custom_cart_button_text() {
    return __( 'Add To Bag', 'woocommerce' );
}
function get_step_winzar($stepNum=1){ ?>
    <div id="booking-winzar" class="row">
        <div class="col-xs-2 col-sm-offset-2 actived">
            <?php if($stepNum>1): ?>
            <a href="<?php echo get_permalink( get_page_by_path( 'cart' ) ) ?>">
            <hr>
            <span>1</span>
            <label class="bag">Bag</label>
            </a>
            <?php else:?>
            <hr>
            <span <?php if($stepNum==1) echo 'class="active"' ?>>1</span>
            <label class="bag">Bag</label>
            <?php endif;?>
        </div>
        <div class="col-xs-2 actived">
            <?php if($stepNum>2): ?>
            <a href="<?php echo get_permalink( get_page_by_path( 'gift' ) ) ?>">
            <hr>
            <span>2</span>
            <label class="gift">Gift</label>
            </a>
             <?php else:?>
            <hr>
            <span <?php if($stepNum==2) echo 'class="active"' ?> >2</span>
            <label class="gift">Gift</label>
             <?php endif;?>
        </div>
        <div class="col-xs-2 actived">
            <?php if($stepNum>3): ?>
            <a href="<?php echo get_permalink( get_page_by_path( 'checkout-account' ) ) ?>">
            <hr>
            <span>3</span>
            <label class="account">Account</label>
            </a>
            <?php else:?>
             <hr>
            <span <?php if($stepNum==3) echo 'class="active"' ?>>3</span>
            <label class="account">Account</label>
            <?php endif;?>
        </div>
        <div class="col-xs-2">
            <?php if($stepNum>4): ?>
            <a href="<?php echo get_permalink( get_page_by_path( 'checkout-delivery' ) ) ?>">
            <hr>
            <span>4</span>
            <label class="delivery">Delivery</label>
            </a>
            <?php else:?>
             <hr>
            <span  <?php if($stepNum==4) echo 'class="active"' ?>>4</span>
            <label class="delivery">Delivery</label>
            <?php endif;?>
        </div>
        <div class="col-xs-2">
            <?php if($stepNum>5): ?>
            <a href="<?php echo get_permalink( get_page_by_path( 'payment' ) ) ?>">
            <hr>
            <span>5</span>
            <label class="payment">Payment</label>
            </a>
            <?php else:?>
            <hr>
            <span <?php if($stepNum==5) echo 'class="active"' ?>>5</span>
            <label class="payment">Payment</label>
            <?php endif;?>
        </div>
    </div>
<?php } ?>
<?php

include_once 'lib/woocommerce/class-my-product.php';
include_once 'lib/woocommerce/my-wc-form-handler.php';
