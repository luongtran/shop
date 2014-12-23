<?php 
global $woocommerce;
if(!MyProduct::isAllowGift()){
    wp_redirect(get_permalink( get_page_by_path( 'checkout-account' ) ) );
    exit();
}
//Check have a gift ?
$gift_exist = false;
$items = $woocommerce->cart->get_cart();
// Check is gift in each cart item
foreach ($items as $item) {
    if(MyProduct::is_gift($item['product_id'])){
        $gift_exist = true;
        break;
    }
}
if($gift_exist){
    wp_redirect(get_permalink( get_page_by_path( 'checkout-account' ) ) );
    exit();
}
?>
<?php
    get_header();

    // action hook for placing content above #container
    thematic_abovecontainer();
?>
    <div id="main-content" class="page-gift page-cart">
        <h1>Your Shopping Bag</h1>
        <?php get_step_winzar(2);?>
        <div id="completed-fragrance">
            <h2>Select Your Complement Fragrance</h2>
            <p>As a thank you for your shopping with us, we'd like to offer you one of the following fragrances</p>
        </div>
        <ul class="products list-inline list-unstyled gift-products">
            <?php
                $loop = MyProduct::getFromCategory('gift');
                while ( $loop->have_posts() ) : $loop->the_post(); global $product; ?>
                    <li class="product">  
                        <a href="<?php echo home_url() ?>?gift=<?php echo $product->id ?>" 
                           title="<?php echo esc_attr($loop->post->post_title ? $loop->post->post_title : $loop->post->ID); ?>">
                            <?php woocommerce_show_product_sale_flash( $post, $product ); ?>
                            <?php if (has_post_thumbnail( $loop->post->ID )) echo get_the_post_thumbnail($loop->post->ID, 'shop_catalog'); else echo '<img src="'.woocommerce_placeholder_img_src().'" alt="Placeholder" width="300px" height="300px" />'; ?>
                            <h3><?php the_title(); ?></h3> 
                        </a>
                        <?php // woocommerce_template_loop_add_to_cart( $loop->post, $product ); ?>
                    </li>
            <?php endwhile; ?>
            <?php wp_reset_query(); ?>
        </ul><!--/.products-->
        <div id="with-complement">
            With Complements
        </div>
    </div><!-- #content -->
    <?php 
        // action hook for placing content below #content
        thematic_belowcontent(); 
    ?> 
<?php 
    // action hook for placing content below #container
    thematic_belowcontainer();

    // calling footer.php
    get_footer();
?>