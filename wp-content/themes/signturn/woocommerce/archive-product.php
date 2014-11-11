<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * Override this template by copying it to yourtheme/woocommerce/archive-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header( 'shop' ); ?>

	<?php
		/**
		 * woocommerce_before_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
		 * @hooked woocommerce_breadcrumb - 20
		 */
		do_action( 'woocommerce_before_main_content' );
	?>
        <?php if( is_product_category()):
                  global $wp_query;
                $cat = $wp_query->get_queried_object();
                $description = $cat->description;
                $tmpArr = explode(PHP_EOL,$description);
                $title = '';
                if($tmpArr[0]!==$description){
                    $title = $tmpArr[0];
                    $description = str_replace($tile,'', $description);
                }
            ?>  
            <div id="product-cat-description">
                <h1><?php echo $title;?></h1>
                <p><?php echo $description ?></p>
            </div>
        <div id="list-product-of-cat" class="wow slideInRight"  data-wow-iteration="1" >
            <a id="trigger-prev" href="javascript:void(0)"><i class="fa fa-angle-down fa-2x"></i></a>
            <div id="slider1">
		<a class="buttons prev" href="#"><i class="fa fa-angle-down fa-2x"></i></a>
		<div class="viewport">
			<ul class="overview">
                            <?php
                            $catLink = get_term_link($cat->slug,'product_cat');
                            $args = array( 'post_type' => 'product', 'product_cat' => $cat->slug, 'orderby' => 'rand' );
                            $loop = new WP_Query( $args );
                            while ( $loop->have_posts() ) : $loop->the_post(); global $product; 
                            $image_link  = wp_get_attachment_url( get_post_thumbnail_id() );
                            ?>
                            <li>
                                <a href="<?php the_permalink()?>">
                                        <?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="Placeholder" width="300px" height="300px" />'; ?>
                                    <h3><?php the_title();
                                    ?>
                                   
                                    </h3>
                                </a>
                                <div class="archive-mark">
                                    <a title="Add to Bag (<?php echo strip_tags($product->get_price_html()) ?>)" data-quantity="1" data-product_sku="" data-product_id="<?php echo $loop->post->ID ?>" rel="nofollow" href="<?php echo $catLink?>/?add-to-cart=<?php echo $loop->post->ID ?>"  class="button add_to_cart_button product_type_simple" ><i class="fa fa-plus"></i></a>
                                    <a title="View" href="<?php the_permalink()?>"><i class="fa fa-search"></i></a>
                                </div>
                            </li>
                            <?php endwhile;?>
                            <?php wp_reset_query(); ?>
			</ul>
                    </div>
                    <a class="buttons next" href="#"><i class="fa fa-angle-up fa-2x"></i></a>
            </div>
        </div>
<div id="list-product-of-cat2" >
            <div id="slider2">
		<a class="buttons prev" href="#"><i class="fa fa-angle-left fa-2x"></i></a>
		<div class="viewport">
			<ul class="overview list-unstyled">
                            <?php 
                            $args = array( 'post_type' => 'product', 'product_cat' => $cat->slug, 'orderby' => 'rand' );
                            $loop = new WP_Query( $args );
                            while ( $loop->have_posts() ) : $loop->the_post(); global $product; 
                            $image_link  = wp_get_attachment_url( get_post_thumbnail_id() );
                            ?>
                            <li>
                                <a  href="<?php the_permalink()?>">
                                        <?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="Placeholder" width="300px" height="300px" />'; ?>
                                    <h3><?php the_title(); ?></h3>
                                </a>
                                
                                <div class="archive-mark">
                                    <a title="Add to Bag (<?php echo strip_tags($product->get_price_html()) ?>)" data-quantity="1" data-product_sku="" data-product_id="<?php echo $loop->post->ID ?>" rel="nofollow" href="<?php echo $catLink?>/?add-to-cart=<?php echo $loop->post->ID ?>"  class="button add_to_cart_button product_type_simple" ><i class="fa fa-plus"></i></a>
                                    <a title="View" href="<?php the_permalink()?>"><i class="fa fa-search"></i></a>
                                </div>
                            </li>
                            <?php endwhile;?>
                            <?php wp_reset_query(); ?>
			</ul>
                    </div>
                    <a class="buttons next" href="#"><i class="fa fa-angle-right fa-2x"></i></a>
            </div>
        </div>
        <?php else: ?>
		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>

			<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>

		<?php endif; ?>

		<?php do_action( 'woocommerce_archive_description' ); ?>

		<?php if ( have_posts() ) : ?>

			<?php
				/**
				 * woocommerce_before_shop_loop hook
				 *
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action( 'woocommerce_before_shop_loop' );
			?>

			<?php woocommerce_product_loop_start(); ?>

				<?php woocommerce_product_subcategories(); ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php  wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

			<?php
				/**
				 * woocommerce_after_shop_loop hook
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
			?>

		<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

			<?php wc_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif; ?>
        <?php endif;?>
	<?php
		/**
		 * woocommerce_after_main_content hook
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'woocommerce_after_main_content' );
	?>

	<?php
		/**
		 * woocommerce_sidebar hook
		 *
		 * @hooked woocommerce_get_sidebar - 10
		 */
		do_action( 'woocommerce_sidebar' );
	?>
                        
<div class="modal fade" id="zoom-image-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <img src="" style="width: 100%" class="img-responsive" />
    </div>
  </div>
</div
<?php get_footer( 'shop' ); ?>