<?php
/**
 * Content wrappers
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$template = get_option( 'template' );

switch( $template ) {
	case 'twentyeleven' :
		echo '<div id="primary"><div id="content" role="main">';
		break;
	case 'twentytwelve' :
		echo '<div id="primary" class="site-content"><div id="content" role="main">';
		break;
	case 'twentythirteen' :
		echo '<div id="primary" class="site-content"><div id="content" role="main" class="entry-content twentythirteen">';
		break;
	case 'twentyfourteen' :
		echo '<div id="primary" class="content-area"><div id="content" role="main" class="site-content twentyfourteen"><div class="tfwc">';
		break;
	default :
		if(is_product_category()){
                   show_product_catetory_template();
                   break;
                }
		echo '<div id="main-content"><div id="content" role="main">';
		break;
}
function show_product_catetory_template(){
     global $wp_query;
     $cat = $wp_query->get_queried_object();
     $thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
     $image = wp_get_attachment_url( $thumbnail_id );
     $style = 'style="background:url('.$image.')  no-repeat center center #000;position: relative;min-height:650px"';
    echo '<div  id="product-category-container"><div id="content" role="main"><img src="'.$image.'" class="img-responsive cat-image" />';
}