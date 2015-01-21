<?php
/**
 * WooCommerce PayPal Express Payment Gateway
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce PayPal Express to newer
 * versions in the future. If you wish to customize WooCommerce PayPal Express for your
 * needs please refer to http://docs.woothemes.com/document/woocommerce-PayPal Express/
 *
 * @package   WC-PayPal Express/Gateway
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2014, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * PayPal Express API Request Class
 *
 * Generates query string required by API specs to perform an API request
 *
 * @link https://developer.paypal.com/docs/classic/api/NVPAPIOverview/
 *
 * @since 3.0.0
 */
class WC_Paypal_Express_API_Request implements SV_WC_Payment_Gateway_API_Request {


	/** auth/capture transaction type */
	const AUTH_CAPTURE = 'Sale';

	/** authorize only transaction type */
	const AUTH_ONLY = 'Authorization';

	/** @var array the request parameters */
	private $parameters = array();

	/** @var WC_Order optional order object if this request was associated with an order */
	protected $order;


	/**
	 * Construct an PayPal Express request object
	 *
	 * @since 3.0.0
	 * @param string $api_username the API username
	 * @param string $api_password the API password
	 * @param string $api_signature the API signature
	 * @param string $api_version the API version
	 * @return \WC_Paypal_Express_API_Request
	 */
	public function __construct( $api_username, $api_password, $api_signature, $api_version ) {

		$this->add_parameters( array(
			'USER'      => $api_username,
			'PWD'       => $api_password,
			'SIGNATURE' => $api_signature,
			'VERSION'   => $api_version,
		) );
	}


	/**
	 * Sets up the express checkout transaction
	 *
	 * @link https://developer.paypal.com/docs/classic/express-checkout/integration-guide/ECGettingStarted/#id084RN060BPF
	 * @link https://developer.paypal.com/webapps/developer/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
	 *
	 * @since 3.0.0
	 * @param array $args {
	 *     @type string $return_url                URL to which the buyer's browser is returned after choosing to pay with PayPal.
	 *     @type string $cancel_url                URL to which the buyer is returned if the buyer does not approve the use of PayPal to pay.
	 *     @type string $page_style                Name of the Custom Payment Page Style for payment pages associated with this button or link.
	 *     @type bool   $use_bml                   Whether to use Bill Me Later or not, defaults to false.
	 *     @type bool   $paypal_account_optional   Whether using/having a PayPal account is optional or not.
	 *     @type string $landing_page              PayPal landing page to use, defaults to `billing`.
	 * }
	 */
	public function set_express_checkout( $args ) {

		$this->set_method( 'SetExpressCheckout' );

		$defaults = array(
			'use_bml'                 => false,
			'paypal_account_optional' => false,
			'landing_page'            => 'billing',
			'page_style'              => null,
			'brand_name'              => null,
			'payment_action'          => self::AUTH_CAPTURE,
		);

		$args = wp_parse_args( $args, $defaults );

		$this->add_parameters( array(
			'RETURNURL'    => $args['return_url'],
			'CANCELURL'    => $args['cancel_url'],
			'PAGESTYLE'    => $args['page_style'],
			'BRANDNAME'    => $args['brand_name'],
			'SOLUTIONTYPE' => $args['paypal_account_optional'] ? 'Sole' : 'Mark',
			'LANDINGPAGE'  => ( 'login' == $args['landing_page'] ) ? 'Login' : 'Billing',
		) );

		// override params specific to BML
		if ( $args['use_bml'] ) {
			$this->add_parameters( array(
				'USERSELECTEDFUNDINGSOURCE' => 'BML',
				'SOLUTIONTYPE'              => 'Sole',
				'LANDINGPAGE'               => 'Billing',
			) );
		}

		// item count
		$i = 0;

		// force total calculation
		if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
			define( 'WOOCOMMERCE_CART', true );
		}

		WC()->cart->calculate_totals();

		if ( $this->skip_line_items() ) {

			$item_names = array();

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				$product  = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$quantity = absint( $cart_item['quantity'] );

				$item_names[] = sprintf( '%s x %s', $product->get_title(), $quantity );
			}

			$this->add_line_item_parameters( array(
				'NAME' => sprintf( __( '%s - Order', WC_PayPal_Express::TEXT_DOMAIN ), get_option( 'blogname' ) ),
				'DESC' => SV_WC_Helper::str_truncate( html_entity_decode( implode( ', ', $item_names ), ENT_QUOTES, 'UTF-8' ), 127 ),
				'AMT'  => WC()->cart->cart_contents_total - WC()->cart->get_order_discount_total(),
			), 0 );

		} else {

			// add line items
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				$product  = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$quantity = absint( $cart_item['quantity'] );

				$this->add_line_item_parameters( array(
					'NAME'    => SV_WC_Helper::str_truncate( html_entity_decode( $product->get_title(), ENT_QUOTES, 'UTF-8' ), 127 ),
					'DESC'    => $this->get_item_description( $cart_item, $product ),
					'AMT'     => round( $cart_item['line_subtotal'] / $quantity, 2 ),
					'QTY'     => $quantity,
					'ITEMURL' => $product->get_permalink(),
				), $i++ );

				// note: TAXAMT is skipped as PayPal doesn't display the per-item
				// tax amount to the customer, and it breaks totals calculations with
				// pre-tax discounts. The total order tax is included instead.
			}

			// add cart discounts as line item
			if ( WC()->cart->get_cart_discount_total() > 0 ) {

				$this->add_line_item_parameters( array(
					'NAME' => __( 'Cart Discount', WC_Paypal_Express::TEXT_DOMAIN ),
					'QTY'  => 1,
					'AMT'  => - WC()->cart->get_cart_discount_total(),
				), $i++ );
			}

			// add order discounts as line item
			if ( WC()->cart->get_order_discount_total() > 0 ) {

				$this->add_line_item_parameters( array(
					'NAME' => __( 'Order Discount', WC_Paypal_Express::TEXT_DOMAIN ),
					'QTY'  => 1,
					'AMT'  => - WC()->cart->get_order_discount_total(),
				), $i );
			}
		}

		// set order-level totals
		$this->add_payment_parameters( array(
			'AMT'           => WC()->cart->total,
			'CURRENCYCODE'  => get_woocommerce_currency(),
			'ITEMAMT'       => WC()->cart->cart_contents_total - WC()->cart->get_order_discount_total(),
			'SHIPPINGAMT'   => WC()->cart->shipping_total,
			'TAXAMT'        => wc_round_tax_total( WC()->cart->tax_total + WC()->cart->shipping_tax_total ),
			'PAYMENTACTION' => ( $args['payment_action'] == self::AUTH_ONLY ) ? self::AUTH_ONLY : self::AUTH_CAPTURE,
		) );

		// set max amount to 150% of the total to allow for increases in shipping, etc. before final checkout
		$this->add_parameter( 'MAXAMT', WC()->cart->total + ( WC()->cart->total * .5 ) );

		// set customer shipping address
		$this->add_payment_parameters( array(
			'SHIPTOSTREET'      => WC()->customer->get_shipping_address(),
			'SHIPTOSTREET2'     => WC()->customer->get_shipping_address_2(),
			'SHIPTOCITY'        => WC()->customer->get_shipping_city(),
			'SHIPTOSTATE'       => WC()->customer->get_shipping_state(),
			'SHIPTOZIP'         => WC()->customer->get_shipping_postcode(),
			'SHIPTOCOUNTRYCODE' => WC()->customer->get_shipping_country(),
		) );
	}


	/**
	 * Get info about the buyer & transaction from PayPal
	 *
	 * @link https://developer.paypal.com/docs/classic/express-checkout/integration-guide/ECGettingStarted/#id084RN060BPF
	 * @link https://developer.paypal.com/webapps/developer/docs/classic/api/merchant/GetExpressCheckoutDetails_API_Operation_NVP/
	 *
	 * @since 3.0.0
	 * @param string $token token from SetExpressCheckout response
	 */
	public function get_express_checkout_details( $token ) {

		$this->set_method( 'GetExpressCheckoutDetails' );
		$this->add_parameter( 'TOKEN', $token );
	}


	/**
	 * Sets up express checkout payment
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order
	 */
	public function do_payment_charge( WC_Order $order ) {

		$this->do_payment( $order, self::AUTH_CAPTURE );
	}


	/**
	 * Sets up an auth-only express checkout payment
	 *
	 * @sine 3.0.0
	 * @param \WC_Order $order
	 */
	public function do_payment_auth( WC_Order $order ) {

		$this->do_payment( $order, self::AUTH_ONLY );
	}

	/**
	 * Set up the DoExpressCheckoutPayment request
	 *
	 * @link https://developer.paypal.com/docs/classic/express-checkout/integration-guide/ECGettingStarted/#id084RN060BPF
	 * @link https://developer.paypal.com/webapps/developer/docs/classic/api/merchant/DoExpressCheckoutPayment_API_Operation_NVP/
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order order object
	 * @param string $type
	 */
	private function do_payment( WC_Order $order, $type ) {

		$this->set_method( 'DoExpressCheckoutPayment' );

		// set base params
		$this->add_parameters( array(
			'TOKEN'            => $order->paypal_express_token,
			'PAYERID'          => ( ! empty( $order->paypal_express_payer_id ) ) ? $order->paypal_express_payer_id : null,
			'BUTTONSOURCE'     => 'WooThemes_Cart',
			'RETURNFMFDETAILS' => 1,
		) );

		$order_subtotal = $i = 0;

		$order_items = array();

		// add line items
		foreach ( $order->get_items() as $item ) {

			$product = new WC_Product( $item['product_id'] );

			$order_items[] = array(
				'NAME'    => SV_WC_Helper::str_truncate( html_entity_decode( $product->get_title(), ENT_QUOTES, 'UTF-8' ), 127 ),
				'DESC'    => $this->get_item_description( $item, $product ),
				'AMT'     => $order->get_item_subtotal( $item ),
				'QTY'     => ( ! empty( $item['qty'] ) ) ? absint( $item['qty'] ) : 1,
				'ITEMURL' => $product->get_permalink(),
			);

			$order_subtotal += $item['line_total'];
		}

		// add fees
		foreach ( $order->get_fees() as $fee ) {

			$order_items[] = array(
				'NAME' => SV_WC_Helper::str_truncate( $fee['name'], 127 ),
				'AMT'  => $fee['line_total'],
				'QTY'  => 1,
			);

			$order_subtotal += $fee['line_total'];
		}

		// add cart discounts as line item
		if ( $order->get_cart_discount() > 0 ) {

			$order_items[] = array(
				'NAME' => __( 'Cart Discount', WC_Paypal_Express::TEXT_DOMAIN ),
				'QTY'  => 1,
				'AMT'  => - $order->get_cart_discount(),
			);
		}

		// add order discounts as line item
		if ( $order->get_order_discount() > 0 ) {

			$order_items[] = array(
				'NAME' => __( 'Order Discount', WC_Paypal_Express::TEXT_DOMAIN ),
				'QTY'  => 1,
				'AMT'  => - $order->get_order_discount(),
			);
		}

		// add a single item for the entire order
		if ( $this->skip_line_items() ) {

			$item_names = array();

			foreach ( $order_items as $item ) {

				$item_names[] = sprintf( '%s x %s', $item['NAME'], $item['QTY'] );
			}

			$this->add_line_item_parameters( array(
				'NAME' => sprintf( __( '%s - Order', WC_PayPal_Express::TEXT_DOMAIN ), get_option( 'blogname' ) ),
				'DESC' => SV_WC_Helper::str_truncate( html_entity_decode( implode( ', ', $item_names ), ENT_QUOTES, 'UTF-8' ), 127 ),
				'AMT'  => $order_subtotal - $order->get_order_discount(),
				'QTY'  => 1,
			), 0 );

		} else {

			// add individual order items
			foreach ( $order_items as $item ) {
				$this->add_line_item_parameters( $item, $i++ );
			}
		}

		// add order-level parameters
		$this->add_payment_parameters( array(
			'AMT'              => $order->get_total(),
			'CURRENCYCODE'     => $order->get_order_currency(),
			'ITEMAMT'          => $order_subtotal - $order->get_order_discount(),
			'SHIPPINGAMT'      => $order->get_total_shipping(),
			'TAXAMT'           => $order->get_total_tax(),
			'INVNUM'           => $order->paypal_express_invoice_prefix . ltrim( $order->get_order_number(), _x( '#', 'hash before the order number', 'woocommerce-gateway-paytrail' ) ),
			'PAYMENTACTION'    => $type,
			'PAYMENTREQUESTID' => $order->id,
		) );
	}


	/**
	 * Setup the DoCapture request
	 *
	 * @link https://developer.paypal.com/webapps/developer/docs/classic/api/merchant/DoCapture_API_Operation_NVP/
	 * @link https://developer.paypal.com/webapps/developer/docs/classic/admin/auth-capture/
	 *
	 * @since 3.0.0
	 * @param WC_Order $order order object
	 */
	public function do_capture( WC_Order $order ) {

		$this->set_method( 'DoCapture' );

		$this->add_parameters( array(
			'AUTHORIZATIONID' => $order->paypal_express_transaction_id,
			'AMT'             => $order->capture_total,
			'CURRENCYCODE'    => $order->get_order_currency(),
			'COMPLETETYPE'    => 'Complete',
			'INVNUM'          => $order->get_order_number(),
			'NOTE'            => $order->description,
		) );
	}


	/** Helper Methods ******************************************************/


	/**
	 * Add a parameter
	 *
	 * since 3.0.0
	 * @param string $key
	 * @param string|int $value
	 */
	private function add_parameter( $key, $value ) {

		$this->parameters[ $key ] = $value;
	}


	/**
	 * Add multiple parameters
	 *
	 * @since 3.0.0
	 * @param array $params
	 */
	private function add_parameters( array $params ) {

		foreach ( $params as $key => $value ) {
			$this->add_parameter( $key, $value );
		}
	}


	/**
	 * Set the method for the request, currently using:
	 *
	 * + `SetExpressCheckout` - setup transaction
	 * + `GetExpressCheckout` - gets buyers info from PayPal
	 * + `DoExpressCheckoutPayment` - completes the transaction
	 * + `DoCapture` - captures a previously authorized transaction
	 *
	 * @since 3.0.0
	 * @param string $method
	 */
	private function set_method( $method ) {
		$this->add_parameter( 'METHOD', $method );
	}


	/**
	 * Add payment parameters, auto-prefixes the parameter key with `PAYMENTREQUEST_0_`
	 * for convenience and readability
	 *
	 * @since 3.0.0
	 * @param array $params
	 */
	private function add_payment_parameters( array $params ) {

		foreach ( $params as $key => $value ) {
			$this->add_parameter( "PAYMENTREQUEST_0_{$key}", $value );
		}
	}


	/**
	 * Adds a line item parameters to the request, auto-prefixes the parameter key
	 * with `L_PAYMENTREQUEST_0_` for convenience and readability
	 *
	 * @since 3.0.0
	 * @param array $params
	 * @param int $item_count current item count
	 */
	private function add_line_item_parameters( array $params, $item_count ) {

		foreach ( $params as $key => $value ) {
			$this->add_parameter( "L_PAYMENTREQUEST_0_{$key}{$item_count}", $value );
		}
	}


	/**
	 * PayPal cannot properly calculate order totals when prices include tax (due
	 * to rounding issues), so line items are skipped and the order is sent as
	 * a single item
	 *
	 * @since 3.0.0
	 * @return bool true if line items should be skipped, false otherwise
	 */
	private function skip_line_items() {

		return ( 'yes' === get_option( 'woocommerce_calc_taxes' ) && 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
	}


	/**
	 * Helper method to return the item description, which is composed of item
	 * meta flattened into a comma-separated string, if available. Otherwise the
	 * product SKU is included.
	 *
	 * The description is automatically truncated to the 127 char limit.
	 *
	 * @since 3.0.0
	 * @param array $item cart or order item
	 * @param \WC_Product $product product data
	 * @return string
	 */
	private function get_item_description( $item, $product ) {

		if ( empty( $item['item_meta'] ) ) {

			// cart item
			$item_desc = WC()->cart->get_item_data( $item, true );

			$item_desc = str_replace( "\n", ', ', rtrim( $item_desc ) );

		} else {

			// order item
			$item_meta = new WC_Order_Item_Meta( $item['item_meta'] );

			$item_meta = SV_WC_Plugin_Compatibility::get_formatted_item_meta( $item_meta );

			if ( ! empty( $item_meta ) ) {

				$item_desc = array();

				foreach ( $item_meta as $meta ) {
					$item_desc[] = sprintf( '%s: %s', $meta['label'], $meta['value'] );
				}

				$item_desc = implode( ', ', $item_desc );

			} else {

				$item_desc = is_callable( array( $product, 'get_sku') ) && $product->get_sku() ? sprintf( __( 'SKU: %s', WC_PayPal_Express::TEXT_DOMAIN ), $product->get_sku() ) : null;
			}
		}

		return SV_WC_Helper::str_truncate( $item_desc, 127 );
	}


	/**
	 * Returns the string representation of this request
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API_Request::to_string()
	 * @return string the request query string
	 */
	public function to_string() {

		return http_build_query( $this->get_parameters() );
	}


	/**
	 * Returns the string representation of this request with any and all
	 * sensitive elements masked or removed
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API_Request::to_string_safe()
	 * @return string the pretty-printed request array string representation, safe for logging
	 */
	public function to_string_safe() {

		$request = $this->get_parameters();

		$sensitive_fields = array( 'USER', 'PWD', 'SIGNATURE' );

		foreach ( $sensitive_fields as $field ) {

			if ( isset( $request[ $field ] ) ) {

				$request[ $field ] = str_repeat( '*', strlen( $request[ $field ] ) );
			}
		}

		return print_r( $request, true );
	}


	/**
	 * Returns the request parameters after validation & filtering
	 *
	 * @since 3.0.0
	 * @throws \SV_WC_Payment_Gateway_Exception invalid amount
	 * @return array request parameters
	 */
	public function get_parameters() {

		/**
		 * Filter PPE request parameters.
		 *
		 * Use this to modify the PayPal request parameters prior to validation
		 *
		 * @since 3.0.0
		 * @param array $parameters
		 * @param \WC_PayPal_Express_API_Request $this instance
		 */
		$this->parameters = apply_filters( 'wc_gateway_paypal_express_request_params', $this->parameters, $this );

		// validate parameters
		foreach ( $this->parameters as $key => $value ) {

			// remove unused params
			if ( '' === $value || is_null( $value ) ) {
				unset( $this->parameters[ $key ] );
			}

			// format and check amounts
			if ( false !== strpos( $key, 'AMT' ) ) {

				// amounts must be 10,000.00 or less for USD
				if ( isset( $this->parameters['PAYMENTREQUEST_0_CURRENCYCODE'] ) && 'USD' == $this->parameters['PAYMENTREQUEST_0_CURRENCYCODE'] && $value > 10000 ) {

					throw new SV_WC_Payment_Gateway_Exception( sprintf( '%s amount of %s must be less than $10,000.00', $key, $value ) );
				}

				// PayPal requires locale-specific number formats (e.g. USD is 123.45)
				// PayPal requires the decimal separator to be a period (.)
				$this->parameters[ $key ] = number_format( $value, 2, '.', '' );
			}
		}

		return $this->parameters;
	}


	/**
	 * Returns the order associated with this request, if there was one
	 *
	 * @since 3.0.0
	 * @return WC_Order order object
	 */
	public function get_order() {

		return $this->order;
	}


} // end WC_Paypal_Express_API_Request class
