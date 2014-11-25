<?php
/**
 * Checkout Form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<?php get_step_winzar(5);?>
<?php
wc_print_notices();

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout
if ( ! $checkout->enable_signup && ! $checkout->enable_guest_checkout && ! is_user_logged_in() ) {
	echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) );
	return;
}

// filter hook for include new pages inside the payment method
$get_checkout_url = apply_filters( 'woocommerce_get_checkout_url', WC()->cart->get_checkout_url() ); ?>

<form name="checkout" method="post" class="checkout" action="<?php echo esc_url( $get_checkout_url ); ?>" enctype="multipart/form-data">

	<?php if ( sizeof( $checkout->checkout_fields ) > 0 ) : ?>
		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
                
            <div class="col2-set" id="customer_details" style="display: none">

			<div class="col-sm-6 no-padding">
                <div id="checkout-delivery-builling">
                    <h2>Billing Address</h2>
                        <?php 
                         if(is_user_logged_in()){
                            $customer_id = get_current_user_id();
                            $billing_title = get_user_meta( $customer_id, 'billing_title', true );
                            $billing_first_name = get_user_meta( $customer_id, 'billing_first_name', true );
                            $billing_last_name = get_user_meta( $customer_id, 'billing_last_name', true );
                            $billing_phone = get_user_meta( $customer_id, 'billing_phone', true );
                            $billing_address_1 = get_user_meta( $customer_id, 'billing_address_1', true );
                            $billing_address_2 = get_user_meta( $customer_id, 'billing_address_2', true );
                            $billing_city = get_user_meta( $customer_id, 'billing_city', true );
                            $billing_country = get_user_meta( $customer_id, 'billing_country', true );
                            $billing_postcode = get_user_meta( $customer_id, 'billing_postcode', true );
                            $billing_email = get_user_meta( $customer_id, 'billing_email', true );
                            
                            $shipping_title = get_user_meta( $customer_id, 'shipping_title', true );
                            $shipping_first_name = get_user_meta( $customer_id, 'shipping_first_name', true );
                            $shipping_last_name = get_user_meta( $customer_id, 'shipping_last_name', true );
                            $shipping_phone = get_user_meta( $customer_id, 'shipping_phone', true );
                            $shipping_address_1 = get_user_meta( $customer_id, 'shipping_address_1', true );
                            $shipping_address_2 = get_user_meta( $customer_id, 'shipping_address_2', true );
                            $shipping_city = get_user_meta( $customer_id, 'shipping_city', true );
                            $shipping_country = get_user_meta( $customer_id, 'shipping_country', true );
                            $shipping_postcode = get_user_meta( $customer_id, 'shipping_postcode', true );
                         }elseif(isset($_SESSION['guest_delivery'])) {
                            $guest_delivery =   $_SESSION['guest_delivery'];
                            $billing_title =  $guest_delivery['billing_title'];
                            $billing_first_name =  $guest_delivery['billing_first_name'];
                            $billing_last_name =  $guest_delivery['billing_last_name'];
                            $billing_phone =  $guest_delivery['billing_phone'];
                            $billing_address_1 =  $guest_delivery['billing_address_1'];
                            $billing_address_2 =  $guest_delivery['billing_address_2'];
                            $billing_city =  $guest_delivery['billing_city'];
                            $billing_country =  $guest_delivery['billing_country'];
                            $billing_postcode =  $guest_delivery['billing_postcode'];
                            $billing_email = $guest_delivery['billing_email'];
                            if(isset($guest_delivery['ship_to_different_address']) && $guest_delivery['ship_to_different_address']){
                                $shipping_title =  $guest_delivery['shipping_title'];
                                $shipping_first_name =  $guest_delivery['shipping_first_name'];
                                $shipping_last_name =  $guest_delivery['shipping_last_name'];
                                $shipping_phone =  $guest_delivery['shipping_phone'];
                                $shipping_address_1 =  $guest_delivery['shipping_address_1'];
                                $shipping_address_2 =  $guest_delivery['shipping_address_2'];
                                $shipping_city =  $guest_delivery['shipping_city'];
                                $shipping_country =  $guest_delivery['shipping_country'];
                                $shipping_postcode =  $guest_delivery['shipping_postcode'];
                            }else{
                                $shipping_title =  $billing_title;
                                $shipping_first_name =  $billing_first_name;
                                $shipping_last_name =  $billing_last_name;
                                $shipping_phone =  $billing_phone;
                                $shipping_address_1 =  $billing_address_1;
                                $shipping_address_2 =   $billing_address_2;
                                $shipping_city =  $billing_city;
                                $shipping_country =  $billing_country;
                                $shipping_postcode =  $billing_postcode;
                            }
                         }else{
                             wp_redirect(get_permalink( get_page_by_path( 'checkout-delivery' ) ) );
                             exit();
                         }
                        ?>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Title: <span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_title" value="<?php echo $billing_title   ?>"  placeholder="Mr/Mrs"  class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>First Name:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_first_name" value="<?php echo $billing_first_name ?>" class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Last Name:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_last_name" value="<?php echo $billing_last_name ?>" class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Contact Number:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_phone" value="<?php echo $billing_phone ?>"  class="form-control" />
                            </div>
                        </div>
                       
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Address<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_address_1"  value="<?php echo  $billing_address_1 ?>"  class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Email<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_email"  value="<?php echo  $billing_email ?>"  class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-8 col-sm-offset-4">
                                <input type="text" name="billing_address_2"  value="<?php echo  $billing_address_2 ?>"  class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>City:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_city" value="<?php echo  $billing_city ?>"  class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Postcode:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_postcode" value="<?php echo $billing_postcode ?>"  class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <?php 
                                $countries = WC()->countries->get_shipping_countries();
                                $args = ['class'=>' ','id'=>' ','label_class'=>'','label'=>' Country'];
                                $field =  '<select class="country_to_state country_select form-control" name="billing_country" >'
						. '<option value="">'.__( 'Select a country&hellip;', 'woocommerce' ) .'</option>';

				foreach ( $countries as $ckey => $cvalue ){
                                    //echo "current $ckey vs ".get_user_meta( $customer_id, 'billing_country', true );
                                    $selected = esc_attr( $ckey ) == $billing_country ?  'selected="selected"' : '';
                                    $field .= '<option '.$selected.'  value="' . esc_attr( $ckey ) . '" '.selected( $value, $ckey, false ) .'>'.__( $cvalue, 'woocommerce' ) .'</option>';
                                }
				$field .= '</select>';
                            ?>
                            <div class="col-sm-4">
                                <label>Country:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <?php echo $field;?>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label class="delivery-instructions">Delivery Instructions:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <textarea class="form-control"  name="order_comments"><?php echo  get_user_meta( $customer_id, 'order_comments', true ) ?></textarea>
                            </div>
                        </div>
                        <p class="radio-wrapper">
                            <label class="inline">
                                <input name="ship_to_different_address" checked="checked" value="0" type="radio" /> 
                                Use my billing address as my delivery address
                            </label>
                        </p>
                        <p class="radio-wrapper">
                            <label class="inline">
                                <input name="ship_to_different_address"  type="radio" value="1" /> 
                                Enter a different delivery address
                            </label>
                        </p>
                </div>
            </div>
            <div class="col-sm-6 no-padding">
                <div id="checkout-delivery-delivery">
                    <h2>Shipping Address</h2>
                    <div id="custom-delivery-address" style="display: none">
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Title: <span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="shipping_title" value="<?php echo $shipping_title ?>"  placeholder="Mr/Mrs"  class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>First Name:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="shipping_first_name" value="<?php echo $shipping_first_name ?>" class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Last Name:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="shipping_last_name" value="<?php echo $shipping_last_name ?>" class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Address<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="shipping_address_1"  value="<?php echo $shipping_address_1 ?>"  class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-8 col-sm-offset-4">
                                <input type="text" name="shipping_address_2"  value="<?php echo $shipping_address_2 ?>"  class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>City:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="shipping_city" value="<?php echo $shipping_city ?>"  class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Postcode:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="shipping_postcode" value="<?php echo $shipping_postcode ?>"  class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <?php 
                                $countries = WC()->countries->get_shipping_countries();
                                $args = ['class'=>' ','id'=>' ','label_class'=>'','label'=>' Country'];
                                $field =  '<select class="country_to_state country_select form-control" name="shipping_country" >'
						. '<option value="">'.__( 'Select a country&hellip;', 'woocommerce' ) .'</option>';

				foreach ( $countries as $ckey => $cvalue ){
                                        $selected = esc_attr( $ckey ) == $shipping_country ?  'selected="selected"' : '';
                                       //echo "current $ckey vs ".get_user_meta( $customer_id, 'shipping_country', true );
					$field .= '<option '.$selected.'  value="' . esc_attr( $ckey ) . '" '.selected( $value, $ckey, false ) .'>'.__( $cvalue, 'woocommerce' ) .'</option>';
                                 }
				$field .= '</select>';
                            ?>
                            <div class="col-sm-4">
                                <label>Country:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <?php echo $field;?>
                            </div>
                        </div>
                    </div>
                    <p id="same-billing-adress">
                        This is where we will deliver your purchase. The billing address and shipping are the same
                    </p>
                </div>
            </div>

		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>


	<?php endif; ?>

	<?php 
            $_SESSION['billing_address_1'] = $billing_address_1;
            $_SESSION['billing_address_2'] = $billing_address_2;
            $_SESSION['billing_city'] = $billing_city;
            $_SESSION['billing_country'] = $billing_country;
            $_SESSION['shipping_address_1'] = $shipping_address_1;
            $_SESSION['shipping_address_2'] = $shipping_address_2;
            $_SESSION['shipping_city'] = $shipping_city;
            $_SESSION['shipping_country'] = $shipping_country;
            do_action( 'woocommerce_checkout_order_review');
            
         ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>