<?php
/**
 * Cart Page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wc_print_notices();

do_action( 'woocommerce_before_cart' ); ?>
<form action="<?php echo esc_url( WC()->cart->get_cart_url() ); ?>" method="post">

<?php do_action( 'woocommerce_before_cart_table' ); ?>

<table class="shop_table cart" cellspacing="0">
	<thead>
            <tr>
                <th class="product-thumbnail"><?php _e( 'Product', 'woocommerce' ); ?></th>
                <th class="product-name" style="width: 55%"><?php _e( 'Description', 'woocommerce' ); ?></th>
                <th class="product-quantity" style="text-align: center"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
                <th class="product-subtotal"><?php _e( 'Price', 'woocommerce' ); ?></th>
            </tr>
	</thead>
	<tbody>
		<?php do_action( 'woocommerce_before_cart_contents' ); ?>
		<?php
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				?>
				<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
					<td class="product-thumbnail">
						<?php
							$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                                                ?>
                                                <?php        
							if ( ! $_product->is_visible() ){
                                                            echo $thumbnail;
                                                        }else
								printf( '<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail );
						?>
					</td>

					<td class="product-name">
						<?php
							if ( ! $_product->is_visible() ){
								echo apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key );
                                                        }else{
								echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s | %s</a>', $_product->get_permalink(), $_product->get_title(),$_product->get_price_html() ), $cart_item, $cart_item_key );
                                                                echo apply_filters( 'woocommerce_short_description', $_product->post->post_excerpt);
                                                        }        
							// Meta data
							echo WC()->cart->get_item_data( $cart_item );

               				// Backorder notification
               				if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) )
               					echo '<p class="backorder_notification">' . __( 'Available on backorder', 'woocommerce' ) . '</p>';
						?>
					</td>

					<td class="product-quantity">
						<?php if(!MyProduct::is_gift($_product->id)){
                                                    if ( $_product->is_sold_individually() ) {
                                                            $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
                                                    } else {
                                                            $product_quantity = woocommerce_quantity_input( array(
                                                                    'input_name'  => "cart[{$cart_item_key}][qty]",
                                                                    'input_value' => $cart_item['quantity'],
                                                                    'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
                                                                    'min_value'   => '0'
                                                            ), $_product, false );
                                                    }
                                                    echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key );
                                                }else{
                                                    echo "<label>1</label>";
                                                }        
                                                ?>
					</td>

					<td class="product-subtotal">
						<?php
							echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
						?>
                                                 <?php
							echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="remove" title="%s">Remove</a>', esc_url( WC()->cart->get_remove_url( $cart_item_key ) ), __( 'Remove this item', 'woocommerce' ) ), $cart_item_key );
						?>
					</td>
				</tr>
				<?php
			}
		}

		do_action( 'woocommerce_cart_contents' );
		?>
                <tr>
                    <td colspan="4" id="td-cart-sub-total">
                        <span id="complementary-gift">Choose Complementary Gift</span>
                        <span id="lb-cart-sub-total">Sub Total:</span>
                        <span id="cart-sub-total"><?php wc_cart_totals_subtotal_html(); ?></span>
                    </td>
                </tr>
		<tr>
                    <td colspan="4" class="actions " style="padding-right: 0">
                        <a style="margin-right:10px;" class="site-btn btn small" href="<?php echo get_permalink( woocommerce_get_page_id( 'shop' ) ) ?>">Continue Shopping</a>
                        <input class="btn site-btn small" type="submit" id="cart-checkout-btn"  name="checkout_goto_gift" value="<?php _e( 'Proceed to Checkout', 'woocommerce' ); ?>" />
                        
                        <?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
                        <?php wp_nonce_field( 'woocommerce-cart' ); ?>
                    </td>
		</tr>

		<?php do_action( 'woocommerce_after_cart_contents' ); ?>
	</tbody>
</table>

<?php do_action( 'woocommerce_after_cart_table' ); ?>

</form>

<div class="cart-collaterals">

	<?php do_action( 'woocommerce_cart_collaterals' ); ?>

	<?php // woocommerce_cart_totals(); ?>

	<?php // woocommerce_shipping_calculator(); ?>

</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
<?php function update_cart_number_item_script(){ ?>
    <script>
        $('body').on('click','.plus',function(){
//          /  alert('haha');
        });
        $(document).ready(function(){
            $('.plus,.minus').on('click',function(){
                var $_this = $(this);
                var quality = $(this).parent().find('.qty').val();
                var name = $(this).parent().find('.qty').attr('name');
                var type = $(this).attr('class');
                $.ajax({
                    type:"POST",
                    dataType: 'json',
                    data:{update_cart_quality:'ajax',quality:quality,name:name,type:type},
                    success: function (data, textStatus, jqXHR) {
                        
                        $('#bag-count').html('bag('+data.total+') - '); 
                        if(data.type==='update'){
                           $('#cart-sub-total').html(data.pricing); 
                        }else{
                            $('#cart-sub-total').html(data.pricing); 
                            //console.log( $(this).parent().parent().parent().attr('class'));
                            $($_this).parents('tr').remove();
                        }
                    }
                });
            });
        });
    </script>
<?php } 
 add_action('thematic_after','update_cart_number_item_script');
?>