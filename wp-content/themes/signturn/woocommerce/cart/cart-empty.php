<?php
/**
 * Empty cart page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wc_print_notices();

?>
<div id="checkout-account" style="width:100%;height: 300px;display: table;" class="aligncenter">
    <div style="display: table-cell;vertical-align: middle">
        <p class="cart-empty"><?php _e( 'Your cart is currently empty.', 'woocommerce' ) ?></p>
        <p class="return-to-shop"><a class="button wc-backward site-btn small" href="<?php echo get_permalink( get_page_by_path( 'home' ) ); ?>"><?php _e( 'Return To Shop', 'woocommerce' ) ?></a></p>
    </div>
</div>
<?php do_action( 'woocommerce_cart_is_empty' ); ?>

