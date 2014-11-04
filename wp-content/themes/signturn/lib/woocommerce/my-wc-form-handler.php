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
//add_filter( 'the_content', 'hide_h1_checkout_page');
//
//function hide_h1_checkout_page( $title_content ) {
//    if (is_page('checkout') ) {
//        $title_content = 'Your Shopping Bag';
//    }
//    return $title_content;
//}

add_action('init', 'choose_gift_product');
add_action('init', 'update_cart_quality');
add_action('init', 'update_cart_quality_ajax');
