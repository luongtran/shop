<?php

class MyProduct extends WC_Product {
    const GIFT_CAT = 'gift';

    public static  function getFromCategory($category_slug,$posts_per_page = 12) {
    // Default Woocommerce ordering args
        $ordering_args = WC()->query->get_catalog_ordering_args();

        $args = array(
                'post_type'             => 'product',
                'post_status'           => 'publish',
                'ignore_sticky_posts'   => 1,
                'orderby'               => $ordering_args['orderby'],
                'order'                 => $ordering_args['order'],
                'posts_per_page'        => $posts_per_page,
                'meta_query'            => array(
                    array(
                        'key'           => '_visibility',
                        'value'         => array('catalog', 'visible'),
                        'compare'       => 'IN'
                    )
                ),
                'tax_query'             => array(
                    array(
                        'taxonomy'      => 'product_cat',
                        'terms'         => array( esc_attr( $category_slug ) ),
                        'field'         => 'slug',
                        'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
                    )
                )
            );

        if ( isset( $ordering_args['meta_key'] ) ) {
                $args['meta_key'] = $ordering_args['meta_key'];
        }
        $products = new WP_Query($args);

        woocommerce_reset_loop();
        wp_reset_postdata();

        return $products;
    }
    public static function is_gift($productId){
        $terms = get_the_terms($productId, 'product_cat' );
        if( isset($terms[0]->slug) && $terms[0]->slug===self::GIFT_CAT){
            return true;
        }
        return false;
    }

    public static function addToCart($productId){
       
    }
    public static function appliedCoupon(){
        if ( ! empty( WC()->cart->applied_coupons ) ) {
            return true;
        }else{
            return false;
        }
    }
    
}