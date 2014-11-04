<?php
/**
 * Single Product Thumbnails
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $product, $woocommerce;

$attachment_ids = $product->get_gallery_attachment_ids();

if ( $attachment_ids ) {
	?>
	<div class="thumbnails">
            <div class="als-container" id="productItems">
            <span class="als-prev"><i class="fa fa-arrow-circle-left"></i></span>
              <div class="als-viewport">
                  <ul class="als-wrapper">
            <?php

		$loop = 0;
		$columns = apply_filters( 'woocommerce_product_thumbnails_columns', 3 );

		foreach ( $attachment_ids as $attachment_id ) {

			$classes = array( 'zoom' );

			if ( $loop == 0 || $loop % $columns == 0 )
				$classes[] = 'first';

			if ( ( $loop + 1 ) % $columns == 0 )
				$classes[] = 'last';

			$image_link = wp_get_attachment_url( $attachment_id );

			if ( ! $image_link )
				continue;

			$image       = wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_small_thumbnail_size','shop_thumbnail') );
			$image_class = esc_attr( implode( ' ', $classes ) );
			$image_title = esc_attr( get_the_title( $attachment_id ) );

			echo apply_filters( 'woocommerce_single_product_image_thumbnail_html',
                                sprintf( ' <li class="als-item"><a data-rel="prettyPhoto[product-gallery]"  href="%s" class="%s" title="%s" data-rel="prettyPhoto[product-gallery]">%s</a></li>', $image_link, $image_class, $image_title, $image ), $attachment_id, $post->ID, $image_class );

			$loop++;
		}

	?>
                </ul> <!-- als-wrapper end -->
              </div> <!-- als-viewport end -->
             <span class="als-prev"><i class="fa fa-arrow-circle-right"></i></span>
            </div> <!-- als-container end -->
        </div>
            <?php function add_als_script(){
                echo '<script src="'.TEMPLATE_URL.'/js/jquery.als-1.7.min.js"></script>';
            }
            add_action('thematic_after','add_als_script');
            ?>
            <?php function ext_product_slider() { ?>
                <script>
                    $("#productItems").als({
                            visible_items: 10,
                            scrolling_items: 2,
                            orientation: "horizontal",
                            circular: "yes",
                            autoscroll: "no",
                            interval: 1000
                    });
                </script>
            <?php }
            add_action('thematic_after','ext_product_slider');
            ?>
	<?php
}
?>