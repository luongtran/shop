<?php
/**
 * Related Products
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product, $woocommerce_loop;

if ( empty( $product ) || ! $product->exists() ) {
	return;
}

$related = $product->get_related( $posts_per_page );

if ( sizeof( $related ) == 0 ) return;

$args = apply_filters( 'woocommerce_related_products_args', array(
	'post_type'            => 'product',
	'ignore_sticky_posts'  => 1,
	'no_found_rows'        => 1,
	'posts_per_page'       => $posts_per_page,
	'orderby'              => $orderby,
	'post__in'             => $related,
	'post__not_in'         => array( $product->id )
) );

$products = new WP_Query( $args );
$totalRelateProduct = $products->post_count;
$woocommerce_loop['columns'] = $columns;

if ( $products->have_posts() ) : ?>
<div style="clear: both"></div>
	<div class="related products" >
            <h2><?php _e( 'Why Not Try...', 'woocommerce' ); ?></h2>
            <div id="related-products">
                <?php if($totalRelateProduct>=4):?>
                <a href="#" class="buttons prev"><i class="fa fa-angle-left fa-2x"></i></a>
                <?php endif;?>
                 <div class="viewport">

                    <?php woocommerce_product_loop_start_for_related(); ?>
                            <?php 
                            $productIds = array();
                            while ( $products->have_posts() ) : $products->the_post(); ?>
                                    <?php wc_get_template( 'related-product.php' ); ?>

                            <?php
                            endwhile; // end of the loop.
                           // print_r($productIds);
                            ?>
                    <?php woocommerce_product_loop_end(); ?>
                 </div>
                <?php if($totalRelateProduct>=4):?>
                <a href="#" class="buttons next"><i class="fa fa-angle-right fa-2x"></i></a>
                <?php endif;?>
            </div>
	</div>

<?php endif;

wp_reset_postdata();
?>
 <?php function add_als_script(){
        echo '<script src="'.TEMPLATE_URL.'/js/jquery.als-1.7.min.js"></script>';
    }
    add_action('thematic_after','add_als_script');
    ?>
    <?php function ext_product_slider() { ?>
        <script>
            $('#related-products').tinycarousel({axis   : "x"});
        </script>
    <?php }
    if($totalRelateProduct>=4)
    add_action('thematic_after','ext_product_slider');
    ?>
