<?php
/**
 * Checkout coupon form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! WC()->cart->coupons_enabled() ) {
	return;
}

$info_message = apply_filters( 'woocommerce_checkout_coupon_message', __( 'Have a coupon?', 'woocommerce' ) . ' <a href="#" class="showcoupon">' . __( 'Click here to enter your code', 'woocommerce' ) . '</a>' );
wc_print_notice( $info_message, 'notice' );
?>

<form id="checkout-coupon" class="checkout_coupon" method="post" style="display:none">
    <div class="row">
        <div class="col-sm-9">
        	<input type="text" name="coupon_code" class="input-text form-control"
        	 placeholder="<?php _e( 'Coupon code', 'woocommerce' ); ?>" id="coupon_code" value="" />
        </div>
        <div class="col-sm-3">
        	<input type="submit" class="button site-btn" name="apply_coupon" value="<?php _e( 'Apply Coupon', 'woocommerce' ); ?>" />
        </div>
    </div>
    <a id="close-coupon" href="javascript:void(0)">
        <i class="fa fa-times"></i>
    </a>
	<div class="clear"></div>
</form>