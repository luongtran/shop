<?php

class MyProduct extends WC_Product {
    const GIFT_CAT = 'gift';
    const SAMPLE_PRODUCT = 'sample';
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
    public function removeGift(){
        
    }
    
    public static function isSampleProduct(Array $product){
        if(is_array($product)){
            if( isset($product['variation']) && is_array($product['variation']) ){
                foreach ($product['variation'] as $variation) {
                    if($variation!==$sampleString){
                        return true;
                    }
                }
            }
            return false;
        }
        return false;
    }

    public static function isAllowGift(){
        $sampleString = MyProduct::SAMPLE_PRODUCT;
        $nonAttr = 0;
        $perfume = 0;
        $cartProducts = WC()->cart->get_cart();
        if(is_array($cartProducts)){
            foreach ($cartProducts as $product) {
                if( isset($product['variation']) && is_array($product['variation']) ){
                    foreach ($product['variation'] as $variation) {
                        if($variation!==$sampleString && !MyProduct::is_gift($product['product_id'])){
                            $perfume ++;
                        }
                    }
                }elseif(!MyProduct::is_gift($product['product_id'])){
                    $nonAttr ++;
                }
            }
            if($nonAttr || $perfume){
                return true;
            }else{
                return false;
            }
        }
        return false;
    }
    

    public static function multipleAttr($productId){
        $product = new WC_Product_Variable($productId);
        $variations = $product->get_available_variations();
        if(is_array($variations) && count($variations)>1){
            return true;
        }
        return false;
    }
    public static function getSavedMadeFree(){
        $savedFree = $_SESSION['saved_sample_free'];
        if(!$savedFree || (is_array($savedFree)&&empty($savedFree))){
            return  array();
        }
        return $savedFree;
    }
    public function setSavedSampleFree($key_free,$key_made_free){
        $savedFree = $_SESSION['saved_sample_free'];
        $savedFree[$key_free][] = $key_made_free;
        $_SESSION['saved_sample_free'] = $savedFree;
    }
    public function detroySaveSampleFree(){
        unset($_SESSION['saved_sample_free']);
    }
    public static function isAtSaveFree($key){
        $savedFree = MyProduct::getSavedMadeFree();
        return isset($savedFree[$key]) ? true : false;
    }
    public static function isAtSavedMadeFree($key_made,$key_free=null){
        $savedMadeFree = MyProduct::getSavedMadeFree();
        if($key_free){
            return isset($savedMadeFree[$key_free][$key_made]) ? true : false;
        }else {
            foreach ($savedMadeFree as $_key_free => $_key_mades) {
                return is_array($_key_mades) && in_array($key_made, $_key_mades) ? true : false;
            }
        }
    }
}