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
                <a href="<?php the_permalink() ?>">
                    <h3><?php echo $loop->post->post_title ?></h3>
                    <span class="onsale"><?php echo $loop->post->ID  ?></span>
                    <?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="'.$loop->post->post_title.'"  class="attachment-post-thumbnail wp-post-image" width="300px" height="300px" />'; ?>
                   <?php if(MyProduct::multipleAttr( $loop->post->ID)): ?>
                    <span class="price"> <?php echo apply_filters('show_detail_product_html_price',$product->get_price_html())  ?></span>
                    <?php else :?>
                    <span class="price"> <?php echo $product->get_price_html();?></span>
                    <?php endif;?>
                </a>
                <div class="actions">
                    <a href="<?php the_permalink() ?>" class="button site-btn btn-sm">View Product
                    </a>
                </div>
	<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
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