<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * Override this template by copying it to yourtheme/woocommerce/content-single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php
	/**
	 * woocommerce_before_single_product hook
	 *
	 * @hooked wc_print_notices - 10
	 */
	 do_action( 'woocommerce_before_single_product' );

	 if ( post_password_required() ) {
	 	echo get_the_password_form();
	 	return;
	 }
?>
<?php global $product; ?>
<aside class="col-xs-12 col-sm-6 col-md-6 arrowbox" id="customer-reviews" style="display: none;">
    <a id="customer-reviews-close" href="javascript::void(0)"><i class="fa fa-times"></i></a>
    <div id="customer-reviews-title" class="row">
       <ul class="list-inline">
           <li><?php echo $product->get_title();  ?></li>
           <li>customer reviews</li>
       </ul>
    </div>
    <div class="row">
        <?php //comments_template() ?>
    </div>
    <div class="row" >
        <div id="new-reviews" data-toggle="collapse" data-target="#data-new-reviews">
          share your signature experience
        </div>
        <div id="data-new-reviews" class="collapse" style="height: 0">
            <form class="jqueryvalidate" novalidate method="POST" action="<?php echo get_home_url() ?>/wp-comments-post.php"  role="form">
                <input id="comment_post_ID" type="hidden" value="<?php echo $product->id;  ?>" name="comment_post_ID">
                <input id="comment_parent" type="hidden" value="0" name="comment_parent">
                <?php if(!is_user_logged_in()): ?>
                <div class="form-group row">
                    <span class="col-xs-12 no-padding-left">Name(*):</span>
                    <input class="form-control" type="text" required="required" size="30" value="" name="author" id="comment-author">               
                </div>
                <div class="form-group row">
                    <span class="col-xs-12 no-padding-left">Email(*):</span>
                    <input class="form-control" type="email" required="required" size="30" value="" name="email" id="comment-email">
                </div>
                <?php endif;?>
                <div class="form-group row" style="margin-bottom: 0;padding-bottom: 0">
                  <div class="col-xs-2 no-padding-left">Rating:</div>  <div class=" col-xs-6 reviews-item-raty"></div>
                </div>
                <div class="form-group row">
                    <span class="col-xs-12 no-padding-left">Comments(*):</span>
                    <textarea required="required" style="border-radius: 0" name="comment" id="" class="form-control" cols="30" rows="5"></textarea>
                </div>
                <button name="submit" type="submit" style="border-radius: 0" class="btn">Submit review</button>
            </form>
        </div>
    </div>
</aside>
<div id="shipping-data" style="display: none"  class="arrowbox col-xs-12 col-sm-6 col-md-6">
    <a id="shipping-data-close" href="javascript::void(0)"><i class="fa fa-times"></i></a>
    UK – Next working Day £7.50 – For orders placed before 1:00pm.<br/>
    UK Standard £4.95 – 2-5 Working Days<br/>
    Europe £20 – 5 -6 Working Days<br/>
    USA £20 – 6 -10 Working Days<br/>
    Rest of the world £30 – 10 -12 Working Days<br/>
    (FREE on UK standard orders over £50)<br/>
    Orders are processed for delivery on Working days only (Monday - Friday)<br/>
</div>
<div id="customer-service" style="display: none" class="arrowbox col-xs-12 col-sm-6 col-md-6">
    <a id="customer-service-data-close" href="javascript::void(0)"><i class="fa fa-times"></i></a>
    <p>Need some help with your order ?</p>
    <p>Contact us today</p>
    <p>Email: sales@signaturefragrances.co.uk</p>
    <p>Contact : 0207 127 9592</p>
</div>
  
<div itemscope  itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="row">
	<?php
		/**
		 * woocommerce_before_single_product_summary hook
		 *
		 * @hooked woocommerce_show_product_sale_flash - 10
		 * @hooked woocommerce_show_product_images - 20
		 */
		//do_action( 'woocommerce_before_single_product_summary' );
	?>
        <div class='col-sm-6'>
            <?php
                 wc_get_template( 'single-product/sale-flash.php' );
                 wc_get_template( 'single-product/product-image.php' );
            ?>
            <div class='clearfix'>
                <?php 
                    
                 $args = array(
			'posts_per_page' => 2,
			'columns' => 2,
			'orderby' => 'rand'
		);

	 	echo woocommerce_related_products( apply_filters( 'woocommerce_output_related_products_args', $args ) );
            
                ?>
            </div>
        </div>
	<div class="summary entry-summary col-sm-6">

		<?php
			/**
			 * woocommerce_single_product_summary hook
			 *
			 * @hooked woocommerce_template_single_title - 5
			 * @hooked woocommerce_template_single_rating - 10
			 * @hooked woocommerce_template_single_price - 10
			 * @hooked woocommerce_template_single_excerpt - 20
			 * @hooked woocommerce_template_single_add_to_cart - 30
			 * @hooked woocommerce_template_single_meta - 40
			 * @hooked woocommerce_template_single_sharing - 50
			 */
			do_action( 'woocommerce_single_product_summary' );
		?>
            <div id='shipping-data-tab' class='single-meta'>
                <p><label>Shipping info</label> <a href="javascript::void(0)"><i class="fa fa-minus"></i></a></p>
                <div class='content'>
                    UK – Next working Day £7.50 – For orders placed before 1:00pm.<br>
                    UK Standard £4.95 – 2-5 Working Days<br>
                    Europe £20 – 5 -6 Working Days<br>
                    USA £20 – 6 -10 Working Days<br>
                    Rest of the world £30 – 10 -12 Working Days<br>
                    (FREE on UK standard orders over £50)<br>
                    Orders are processed for delivery on Working days only (Monday - Friday)<br>
                </div>
            </div>
            <div id="customer-service-tab" class='single-meta'>
                <p><label>Customer service</label> <a id="" href="javascript::void(0)"><i class="fa fa-minus"></i></a></p>
                <div class='content'>
                    <p>Need some help with your order ?<br>
                    Contact us today<br>
                    Email: sales@signaturefragrances.co.uk<br>
                    Contact : 0207 127 9592</p>
                </div>
            </div>
          </div><!-- .summary -->

	<?php
		/**
		 * woocommerce_after_single_product_summary hook
		 *
		 * @hooked woocommerce_output_product_data_tabs - 10
		 * @hooked woocommerce_upsell_display - 15
		 * @hooked woocommerce_output_related_products - 20
		 */
		//do_action( 'woocommerce_after_single_product_summary' );
	?>

	<meta itemprop="url" content="<?php the_permalink(); ?>" />
    </div>
    <div class='clearfix'>
        <div class='single-meta' id="customer-reviews-tab" >
            <p><label>Reviews (<?php echo  wp_count_comments( $product->id)->approved; ?>)</label>  <a id="" href="javascript::void(0)"><i class="fa fa-minus"></i></a></p>

            <div class="row content">
                <div class='col-sm-4'>
                    <div class="clearfix" >
                        <div id="data-new-reviews">
                            <form class="jqueryvalidate" novalidate method="POST" action="<?php echo get_home_url() ?>/wp-comments-post.php"  role="form">
                                <input id="comment_post_ID" type="hidden" value="<?php echo $product->id;  ?>" name="comment_post_ID">
                                <input id="comment_parent" type="hidden" value="0" name="comment_parent">
                                <?php if(!is_user_logged_in()): ?>
                                <div class="form-group row">
                                    <span class="col-xs-12 no-padding-left">Name(*):</span>
                                    <input class="form-control" type="text" required="required" size="30" value="" name="author" id="comment-author">               
                                </div>
                                <div class="form-group row">
                                    <span class="col-xs-12 no-padding-left">Email(*):</span>
                                    <input class="form-control" type="email" required="required" size="30" value="" name="email" id="comment-email">
                                </div>
                                <?php endif;?>
                                <div class="form-group row" style="margin-bottom: 0;padding-bottom: 0">
                                  <div class="col-xs-2 no-padding-left">Rating:</div>  <div class=" col-xs-6 reviews-item-raty"></div>
                                </div>
                                <div class="form-group row">
                                    <span class="col-xs-12 no-padding-left">Comments(*):</span>
                                    <textarea required="required" style="border-radius: 0" name="comment" id="" class="form-control" cols="30" rows="5"></textarea>
                                </div>
                                <button name="submit" type="submit" style="border-radius: 0" class="btn">Submit review</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class='col-sm-8'>
                    <?php comments_template() ?>
                </div>
            </div>
           
        </div>
    </div>
</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>