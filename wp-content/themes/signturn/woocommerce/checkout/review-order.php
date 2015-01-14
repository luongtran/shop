<?php
/**
 * Review order form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.8
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php if ( ! $is_ajax ) : ?><div id="order_review"><?php endif; ?>
        
         <div id="checkout-account" class="clearfix">
            <div class="col-sm-6 no-padding">
                <div id="checkout-shipping">
                    <?php
                        $customer_id = get_current_user_id();
                        try {
                            $billing_address_1 = $_SESSION['billing_address_1'] ;
                            $billing_address_2 = $_SESSION['billing_address_2'] ;
                            $billing_city = $_SESSION['billing_city'] ;
                            $billing_country = WC()->countries->countries[$_SESSION['billing_country']] ;
                            $billing_state = isset($_SESSION['billing_state']) ? $_SESSION['billing_state'] : null;
                            $shipping_address_1 = $_SESSION['shipping_address_1'] ;
                            $shipping_address_2 = $_SESSION['shipping_address_2'] ;
                            $shipping_city = $_SESSION['shipping_city'] ;
                            $shipping_country = WC()->countries->countries[$_SESSION['shipping_country']] ;
                            $shipping_state = isset($_SESSION['shipping_state']) ? $_SESSION['shipping_state'] : null;
                            if($billing_state || $shipping_state){
                                 $us_states = WC()->countries->get_states('US');
                                 if($billing_state){
                                     $billing_state = isset($us_states[$billing_state]) ?  $us_states[$billing_state] : null;
                                 }
                                 if($shipping_state){
                                     $shipping_state = isset($us_states[$shipping_state]) ?  $us_states[$shipping_state] : null;
                                 }
                            }
                        }
                        catch (Exception $ex){
                             wp_redirect(get_permalink( get_page_by_path( 'checkout-delivery' ) ) );
                             exit();
                        }
                    ?>
                    <h2>Shipping</h2>
                    <div class="shipping-group">
                        <h3>Billing Address</h3>
                        <p><?php  echo  $billing_address_1?></p>
                        <?php if (isset($billing_address_2)): ?>
                        <p><?php echo $billing_address_2 ?></p>
                        <?php endif;?>
                        <?php if($billing_state):?>
                        <p><?php echo $billing_city . ', '.$billing_state.', '.$billing_country ?></p>
                        <?php else:?>
                         <p><?php echo $billing_city .', '.$billing_country ?></p>
                        <?php endif;?>
                    </div>
                    <div class="shipping-group">
                        <h3>Shipping Address</h3>
                        <p><?php echo $shipping_address_1 ?></p>
                        <?php if (isset($shipping_address_2)): ?>
                        <p><?php echo $shipping_address_2 ?></p>
                        <?php endif;?>
                        <?php if($shipping_state):?>
                        <p><?php echo $shipping_city . ', '.$shipping_state.', '.$billing_country ?></p>
                        <?php else:?>
                         <p><?php echo $shipping_city .', '.$billing_country ?></p>
                        <?php endif;?>
                    </div>
                    <div class="shipping-group">
                        <h3>Choose Shipping method</h3>
                        <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

				<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

				<?php wc_cart_totals_shipping_html(); ?>

				<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

			<?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 no-padding">
                <div id="checkout-payment">
                    <h2>Payment</h2>
                    <div id="payment">
                            <?php if ( WC()->cart->needs_payment() ) : ?>
                            <ul class="payment_methods methods">
                                <?php
                                    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                                    if ( ! empty( $available_gateways ) ) {

                                                // Chosen Method
                                                if ( isset( WC()->session->chosen_payment_method ) && isset( $available_gateways[ WC()->session->chosen_payment_method ] ) ) {
                                                        $available_gateways[ WC()->session->chosen_payment_method ]->set_current();
                                                } elseif ( isset( $available_gateways[ get_option( 'woocommerce_default_gateway' ) ] ) ) {
                                                        $available_gateways[ get_option( 'woocommerce_default_gateway' ) ]->set_current();
                                                } else {
                                                        current( $available_gateways )->set_current();
                                                }

                                                foreach ( $available_gateways as $gateway ) {
                                                        ?>
                                                        <li class="payment_method_<?php echo $gateway->id; ?>">
                                                                <input id="payment_method_<?php echo $gateway->id; ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>" />
                                                                <label for="payment_method_<?php echo $gateway->id; ?>"><?php echo $gateway->get_title(); ?> <?php echo $gateway->get_icon(); ?></label>
                                                                <?php
                                                                        if ( $gateway->has_fields() || $gateway->get_description() ) :
                                                                                echo '<div class="payment_box payment_method_' . $gateway->id . '" ' . ( $gateway->chosen ? '' : 'style="display:none;"' ) . '>';
                                                                                $gateway->payment_fields();
                                                                                echo '</div>';
                                                                        endif;
                                                                ?>
                                                        </li>
                                                        <?php
                                                }
                                        } else {

                                                if ( ! WC()->customer->get_country() )
                                                        $no_gateways_message = __( 'Please fill in your details above to see available payment methods.', 'woocommerce' );
                                                else
                                                        $no_gateways_message = __( 'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' );

                                                echo '<p>' . apply_filters( 'woocommerce_no_available_payment_methods_message', $no_gateways_message ) . '</p>';

                                        }
                                ?>
                            </ul>
                            <?php endif; ?>


                            <div class="clear"></div>

                    </div>
                </div>
            </div>
             <div class="col-sm-6 col-sm-offset-6" id="personalised" >
                 <h3>Send with a personalised message ?</h3>
                 <textarea name="order_comments" class="form-control" style="border: 1px solid #a89340; border-radius: 0;"></textarea>
                 <p style="padding-top: 10px;text-align: right">
                     <input type="checkbox" class="css-checkbox" id="gif-wrap-cb" value="Yes" name="_gif_wrap" /> 
                     <label for="gif-wrap-cb" class="css-label lite-orange-check"> 
                       Gift wrap
                    </label>
                 </p>
             </div>
             <div class="col-sm-12 aligncenter">
                 <div class="form-row place-order" style="text-align: center">
                        <noscript><?php _e( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the <em>Update Totals</em> button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce' ); ?><br/><input type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php _e( 'Update totals', 'woocommerce' ); ?>" /></noscript>

                        <?php wp_nonce_field( 'woocommerce-process_checkout' ); ?>

                        <?php do_action( 'woocommerce_review_order_before_submit' ); ?>

                        <?php
                        $order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Confirm Checkout', 'woocommerce' ) );

                        echo apply_filters( 'woocommerce_order_button_html', '<input type="submit" class="button alt site-btn" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '" />' );
                        ?>

                        <?php if ( wc_get_page_id( 'terms' ) > 0 && apply_filters( 'woocommerce_checkout_show_terms', true ) ) { 
                                $terms_is_checked = apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['terms'] ) );
                                ?>
                                <p class="form-row terms">
                                        <label for="terms" class="checkbox"><?php printf( __( 'I&rsquo;ve read and accept the <a href="%s" target="_blank">terms &amp; conditions</a>', 'woocommerce' ), esc_url( get_permalink( wc_get_page_id( 'terms' ) ) ) ); ?></label>
                                        <input type="checkbox" checked="checked" class="input-checkbox" name="terms" id="terms" />
                                </p>
                        <?php } ?>

                        <?php do_action( 'woocommerce_review_order_after_submit' ); ?>

                </div>
             </div>
        </div>

        <table class="shop_table" style="display: none">
		<thead>
                    <tr>
                        <th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
                        <th class="product-total"><?php _e( 'Total', 'woocommerce' ); ?></th>
                    </tr>
		</thead>
		<tbody>
			<?php
				do_action( 'woocommerce_review_order_before_cart_contents' );

				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

					if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
						?>
						<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
							<td class="product-name">
								<?php echo apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ); ?>
								<?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times; %s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); ?>
								<?php echo WC()->cart->get_item_data( $cart_item ); ?>
							</td>
							<td class="product-total">
								<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
							</td>
						</tr>
						<?php
					}
				}

				do_action( 'woocommerce_review_order_after_cart_contents' );
			?>
		</tbody>
		<tfoot>

			<tr class="cart-subtotal">
				<th><?php _e( 'Cart Subtotal', 'woocommerce' ); ?></th>
				<td><?php wc_cart_totals_subtotal_html(); ?></td>
			</tr>

			<?php foreach ( WC()->cart->get_coupons( 'cart' ) as $code => $coupon ) : ?>
				<tr class="cart-discount coupon-<?php echo esc_attr( $code ); ?>">
					<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
					<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
				</tr>
			<?php endforeach; ?>

			

			<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
				<tr class="fee">
					<th><?php echo esc_html( $fee->name ); ?></th>
					<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
				</tr>
			<?php endforeach; ?>

			<?php if ( WC()->cart->tax_display_cart === 'excl' ) : ?>
				<?php if ( get_option( 'woocommerce_tax_total_display' ) === 'itemized' ) : ?>
					<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
						<tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
							<th><?php echo esc_html( $tax->label ); ?></th>
							<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr class="tax-total">
						<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
						<td><?php echo wc_price( WC()->cart->get_taxes_total() ); ?></td>
					</tr>
				<?php endif; ?>
			<?php endif; ?>

			<?php foreach ( WC()->cart->get_coupons( 'order' ) as $code => $coupon ) : ?>
				<tr class="order-discount coupon-<?php echo esc_attr( $code ); ?>">
					<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
					<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
				</tr>
			<?php endforeach; ?>

			<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

			<tr class="order-total">
				<th><?php _e( 'Order Total', 'woocommerce' ); ?></th>
				<td><?php wc_cart_totals_order_total_html(); ?></td>
			</tr>

			<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

		</tfoot>
	</table>

	<?php do_action( 'woocommerce_review_order_before_payment' ); ?>

	

	<?php do_action( 'woocommerce_review_order_after_payment' ); ?>

<?php if ( ! $is_ajax ) : ?></div><?php endif; ?>