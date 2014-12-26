<?php
    get_header();
    // action hook for placing content above #container
    thematic_abovecontainer();
?>

<div id="main-content" class="page-sales">
    <h1 class="page-title">Sales Products</h1>
    <?php 
        $args = array(
            'post_type'      => 'product',
            'meta_query'     => array(
                'relation' => 'OR',
                array( // Simple products type
                    'key'           => '_sale_price',
                    'value'         => 0,
                    'compare'       => '>',
                    'type'          => 'numeric'
                ),
                array( // Variable products type
                    'key'           => '_min_variation_sale_price',
                    'value'         => 0,
                    'compare'       => '>',
                    'type'          => 'numeric'
                )
            )
        );

        $loop = new WP_Query( $args );
    ?>
    <div class="row sale-items">
        <?php 
            while ( $loop->have_posts() ) : $loop->the_post(); global $product; 
            $image_link  = wp_get_attachment_url( get_post_thumbnail_id() );
        ?>
        <div class="col-sm-3 sale-item">
            <?php do_action( 'woocommerce_before_shop_loop_item' ); ?>
            <a href="<?php the_permalink(); ?>">
                
		<h3><?php the_title(); ?></h3>
		<?php
			/**
			 * woocommerce_before_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_show_product_loop_sale_flash - 10
			 * @hooked woocommerce_template_loop_product_thumbnail - 10
			 */
			do_action( 'woocommerce_before_shop_loop_item_title' );
		?>

		<?php
			/**
			 * woocommerce_after_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_template_loop_rating - 5
			 * @hooked woocommerce_template_loop_price - 10
			 */
			do_action( 'woocommerce_after_shop_loop_item_title' );
		?>

	</a>
	<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
            <div class="actions">
                <a  data-quantity="1" data-product_sku="" 
                    data-product_id="<?php echo $loop->post->ID ?>" rel="nofollow" 
                    href="<?php the_permalink(); ?>" 
                    class="button add_to_cart_button product_type_simple site-btn btn-sm" >View Product
                </a>
            </div>
        </div>
        <?php endwhile;?>
        <?php wp_reset_query(); ?>
    </div>
</div>

<?php 
    // action hook for placing content below #container
    thematic_belowcontainer();
    // calling footer.php
    get_footer();
?>