<?php

class MyProduct extends WC_Product {
    const GIFT_CAT = 'gift';
    const SAMPLE_PRODUCT = 'sample';
    const FREE_SAMPLE_COUPON = 'free_sample';
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
    public static function realTotalBeforeFreeSampe(){
//        $amount = floatval( preg_replace( '#[^\d.]#', '', WC()->cart->get_cart_total() ) );
//        $totalCoupon = 0;
//        $coupons =  WC()->cart->applied_coupons;
//        foreach ($coupons as $coupon) {
//            if(preg_match('/free samples products/', strtolower($coupon))){
//                $totalCoupon += floatval(MyProduct::getCouponAmount($coupon));
//            }
//        }
//        return number_format($amount + $totalCoupon,2);
        $amount = strip_tags( WC()->cart->get_cart_subtotal());
        $amount = str_replace(get_woocommerce_currency_symbol(),'', $amount);
        //var_dump('trong');die();
        return number_format(floatval($amount));
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
                    if(strpos(strtoupper($variation), strtoupper(MyProduct::SAMPLE_PRODUCT))
                            ||  strtoupper($variation)===  strtoupper(MyProduct::SAMPLE_PRODUCT)){
                        return true;
                    }
                }
            }
            return false;
        }
        return false;
    }

    public static function isAllowGift(){
        $nonAttr = 0;
        $perfume = 0;
        $cartProducts = WC()->cart->get_cart();
        if(is_array($cartProducts)){
            foreach ($cartProducts as $product) {
                if(!MyProduct::isSampleProduct($product) && !MyProduct::is_gift($product['product_id'])){
                    $perfume ++;
                }
//                elseif(!MyProduct::is_gift($product['product_id'])){
//                    $nonAttr ++;
//                }
            }
            if($perfume){
                return true;
            }else{
                return false;
            }
        }
        return false;
    }
    public static function isAlwayFreeShip(){
        if(!WC()->cart){
            return false;
        }
        $perfume = 0;
        $cartProducts = WC()->cart->get_cart();
        if(is_array($cartProducts)){
            foreach ($cartProducts as $product) {
                if(!MyProduct::isSampleProduct($product) && !MyProduct::is_gift($product['product_id'])){
                    $perfume ++;
                }
            }
            if($perfume){
                return false;
            }else{
                return true;
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
    public static function setSavedSampleFree($key_free,$key_made_free){
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
    public static function createCoupon($coupon_code,$amount){
        $discount_type = 'fixed_cart'; // Type: fixed_cart, percent, fixed_product, percent_product
        $coupon = array(
            'post_title' => $coupon_code,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'shop_coupon'
        );
        $new_coupon_id = wp_insert_post( $coupon );
        // Add meta
        update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
        update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
        update_post_meta( $new_coupon_id, 'individual_use', 'no' );
        update_post_meta( $new_coupon_id, 'product_ids', '' );
        update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
        update_post_meta( $new_coupon_id, 'usage_limit', '' );
        update_post_meta( $new_coupon_id, 'expiry_date', '' );
        update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
        update_post_meta( $new_coupon_id, 'free_shipping', 'no' ); 
    }
    public static function priceWithSymbol($price){
        return get_woocommerce_currency_symbol().$price;
    }
    public static function isSaleExpired($varitionId){
        $sale_price_dates_from 	=  get_post_meta( $varitionId, '_sale_price_dates_from', true );//( $date = get_post_meta( $varitionId, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d',$date ) : '';
        $sale_price_dates_to 	= get_post_meta( $varitionId, '_sale_price_dates_to', true ) ;//( $date = get_post_meta( $varitionId, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
        
        $current = time();
        if(!$sale_price_dates_from && !$sale_price_dates_to){
            return false;
        }elseif($sale_price_dates_from && !$sale_price_dates_to && $current >=$sale_price_dates_from){
            return false;
        }elseif(!$sale_price_dates_from && $sale_price_dates_to && $current <=$sale_price_dates_to){
            return false;
        }elseif($current >= $sale_price_dates_from && $current <=$sale_price_dates_to ){
            return false;
        }else{
            return true;
        }
    }
    
    public static function removeSampleCoupon(){
        $coupon = isset($_SESSION[self::FREE_SAMPLE_COUPON]) ? $_SESSION[self::FREE_SAMPLE_COUPON] : NULL;
        if($coupon){
            WC()->cart->remove_coupon($coupon);
            global $wpdb;
            $query = $wpdb->prepare("SELECT id FROM {$wpdb->posts} WHERE post_type like 'shop_coupon' AND post_title like %s",$coupon);
            $results = $wpdb->get_results($query, OBJECT );
            if(count($results)){
                $id = $results[0]->id;
                wp_delete_post($id,true);
                $wpdb->query( 
                    $wpdb->prepare( 
                            "
                             DELETE FROM $wpdb->postmeta
                             WHERE post_id = %d
                            ",$id
                    )
                );
            }
            
        }
    }
    public static function addSampleCoupon(){
        $items = WC()->cart->get_cart();
        $samples = array();
        foreach ($items as $key => $item) {
            if(MyProduct::isSampleProduct($item)){
                $quantity = $item['quantity'];
                for($i=1;$i<=$quantity;$i++){
                    $k = $key.'-'.$i;
                    $samples[] = $k;
                }
            }
        }
        $coupon_amount = 0;
        for($i=0;$i<count($samples);$i+=3){
            if(isset($samples[$i]) && isset($samples[$i+1]) && isset($samples[$i+2])){
                $arr = explode('-', $samples[$i+2]);
                $cartKey = $arr[0];
                $coupon_amount += $items[$cartKey]['data']->get_price();
            }
        }
        if($coupon_amount){
           $coupon_code = "Free samples products - ID:".md5(microtime().uniqid());
           MyProduct::createCoupon($coupon_code, $coupon_amount);
           WC()->cart->add_discount($coupon_code);
           $_SESSION[self::FREE_SAMPLE_COUPON] = $coupon_code;
        }
    }
    public static function getTotalFreeSample(){
        $items = WC()->cart->get_cart();
        $samples = array();
        foreach ($items as $key => $item) {
            if(MyProduct::isSampleProduct($item)){
                $quantity = $item['quantity'];
                for($i=1;$i<=$quantity;$i++){
                    $k = $key.'-'.$i;
                    $samples[] = $k;
                }
            }
        }
        $coupon_amount = 0;
        for($i=0;$i<count($samples);$i+=3){
            if(isset($samples[$i]) && isset($samples[$i+1]) && isset($samples[$i+2])){
                $arr = explode('-', $samples[$i+2]);
                $cartKey = $arr[0];
                $coupon_amount += $items[$cartKey]['data']->get_price();
            }
        }
        return $coupon_amount;
    }
    public  static function getCouponAmount($code) {
        $output = OBJECT;
        global $wpdb;
            $post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='shop_coupon'", $code ));
            if ( $post ){
                $postCoupon = get_post($post, $output);
                $amount = get_post_meta($postCoupon->ID,'coupon_amount');
                return $amount;
            }
        return 0;
    }
}