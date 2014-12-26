<?php 
add_filter( 'woocommerce_process_registration_errors', 'checkout_registration_errors', 10, 4 );
function checkout_registration_errors( $validation_error, $username, $password, $email )
{
    if(isset($_POST['checkout_register'])){
        if($_POST['email']!==$_POST['confirm_email']){
            $validation_error->add('error', 'The Confirmation Email must match your Email Address');
        }
        if($_POST['password']!==$_POST['confirm_password']){
            $validation_error->add('error', 'The Confirmation Password must match your Password');
        }
        if(!isset($_POST['term'])){
            $validation_error->add('error', 'You must agree to our Term & Conditions ');
        }
    }
    return $validation_error;
}

function choose_gift_product(){
    if(isset($_GET['gift'])){
        global $woocommerce;
        $product_id = $_GET['gift'];
        $gift_exist = false;
        $gift_slug = MyProduct::GIFT_CAT;
        $items = $woocommerce->cart->get_cart();
        // Check is gift in each cart item
        foreach ($items as $item) {
            $terms = get_the_terms($item['product_id'], 'product_cat' );
            if( isset($terms[0]->slug) && $terms[0]->slug===$gift_slug){
                $gift_exist = true;
                break;
            }
        }
        if(!$gift_exist){
            $woocommerce->cart->add_to_cart( $product_id ); 
        }
        wp_redirect(get_permalink( get_page_by_path( 'checkout-account' ) ) );
        exit();
    }
}
function update_cart_quality(){
    if(isset($_POST['checkout_goto_gift'])){
        //print_r($_POST);die();
        $carts = $_POST['cart'];
        $currentItems = WC()->cart->cart_contents;
        foreach ($carts as $key => $q) {
            if(isset($carts[$key])){
                WC()->cart->set_quantity($key,  intval($q['qty']));
            }
        }
        wp_redirect(get_permalink( get_page_by_path( MyProduct::GIFT_CAT) ) );
        exit();
    }
}
function update_cart_quality_ajax(){
    if(isset($_POST['update_cart_quality'])){
        //print_r($_POST);die();
        $cartName = str_replace(array('cart[','][qty]'),'', $_POST['name']);
        $type = filter_input(INPUT_POST,'type');
        $quality = intval($_POST['quality']);
        if(preg_match('/minus/',$type)){
            $quality -= 1;
        }else{
            $quality += 1;
        }
        WC()->cart->set_quantity($cartName,$quality);
        if($quality<=0){
            $arr = array(
                'success'=>true,
                'type'=>'remove',
                'pricing'=> WC()->cart->get_cart_total(),
                'total'=>WC()->cart->cart_contents_count
            );
        }else{
            $arr = array(
                'success'=>true,
                'type'=>'update',
                'pricing'=> WC()->cart->get_cart_total(),
                'total'=>WC()->cart->cart_contents_count
            );
        }
        echo json_encode($arr);
        exit();
    }
}
function remove_cart_ajax(){
    $result = array('error'=>true);
    if ( ! empty( $_GET['remove_item_ajax'] ) && isset( $_GET['_wpnonce'] )
            && wp_verify_nonce( $_GET['_wpnonce'], 'woocommerce-cart' ) ) {
        WC()->cart->set_quantity( $_GET['remove_item_ajax'], 0 );
        $result['error'] = true; 
        $result['html'] = cart_header_html();
        echo json_encode($result);
        exit;
    }
}
function check_coupon_applied(){
    if(!empty($_GET['action']) && trim($_GET['action']==='check_coupon')){
        $res = array('applied'=>false);
        if(MyProduct::appliedCoupon()){
            $res['applied'] = true;
        }
        echo json_encode($res);
        exit;
    }
}
//add_filter( 'the_content', 'hide_h1_checkout_page');
//
//function hide_h1_checkout_page( $title_content ) {
//    if (is_page('checkout') ) {
//        $title_content = 'Your Shopping Bag';
//    }
//    return $title_content;
//}
function test_cart(){
    if(!empty($_GET['test_gift'])){
       WC()->cart->cart_contents_total = 1000;
       WC()->cart->subtotal = 1000;
       WC()->cart->subtotal_ex_tax = 1000;
       WC()->cart->persistent_cart_update();
       WC()->cart->set_session();
       die();
     }
     if(!empty($_GET['test'])){
//       $cart = WC()->cart;
//       print_r($cart);
      print_r(check_free_sample_product());
       die();
     }
}
function check_remove_gift(){
    if(!MyProduct::isAllowGift()){
        $items = WC()->cart->get_cart();
        foreach ($items as $key => $item) {
            if(MyProduct::is_gift($item['product_id'])){
               WC()->cart->set_quantity($key, 0 );
            }
        }
    }
}
function check_free_sample_product(){
    $items = WC()->cart->get_cart();
    try {
        $samples = array();
        foreach ($items as $key => $item) {
            if(MyProduct::isSampleProduct($item)){
                $quantity = $item['quantity'];
                for($i=1;$i<=$quantity;$i++){
                    $k = $key.'-'.$i;
                    if( (!MyProduct::isAtSaveFree($k) && !MyProduct::isAtSavedMadeFree($k))){
                        $samples[] = $k;
                    }
                }
            }
            //$item['data']->set_price(400);
        }
        return $samples;
        //print_r($samples);die();
        $coupon_amount = 0;
        for($i=0;$i<count($$samples);$i+=3){
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
        }
        
    } catch (Exception $exc) {
        print_r($exc);die();
    }

}
function update_real_cart(){
    WC()->cart->calculate_totals();
}
function show_highest_html_price($html_price){
    try {
        $price = strip_tags($html_price);
        $price = str_replace(array('&ndash;'), '', $price);
        $arr = explode(get_woocommerce_currency_symbol(), $price);
        natsort($arr);
        return get_woocommerce_currency_symbol().$arr[count($arr)-1];
    } catch (Exception $exc) {
        return $html_price;
    }
}
add_action('woocommerce_before_cart_header','update_real_cart');
add_action('woocommerce_checkout_process','update_real_cart');
add_action('woocommerce_before_cart_table','update_real_cart');
add_action('woocommerce_before_checkout_form','update_real_cart');
add_action('woocommerce_cart_updated','check_free_sample_product');


add_filter('show_detail_product_html_price','show_highest_html_price');
add_action('woocommerce_cart_updated','check_remove_gift');
add_action('init', 'choose_gift_product');
add_action('init', 'update_cart_quality');
add_action('init', 'update_cart_quality_ajax');
add_action('init', 'remove_cart_ajax');
add_action('init', 'check_coupon_applied');
add_action('init', 'test_cart');
