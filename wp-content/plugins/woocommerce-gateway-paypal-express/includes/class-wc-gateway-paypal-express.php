<?php
/**
 * WooCommerce Gateway PayPal Express
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Gateway PayPal Express to newer
 * versions in the future. If you wish to customize WooCommerce Gateway PayPal Express for your
 * needs please refer to http://docs.woothemes.com/document/paypal-express-checkout/ for more information.
 *
 * @package     WC-Gateway-PayPal-Express/Classes
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2014, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Handles all single purchases and payments-related actions
 *
 * @since 2.0
 * @extends \WC_Payment_Gateway
 */
class WC_Gateway_PayPal_Express extends SV_WC_Payment_Gateway_Direct {


	/** the production URL */
	const PRODUCTION_URL_ENDPOINT = 'https://www.paypal.com/cgi-bin/webscr';

	/** the sandbox URL */
	const TEST_URL_ENDPOINT = 'https://www.sandbox.paypal.com/webscr';

	/** The test environment identifier */
	const ENVIRONMENT_TEST = 'sandbox';

	/** @var string paypal api username */
	protected $api_username;

	/** @var string paypal api password */
	protected $api_password;

	/** @var string paypal api signature */
	protected $api_signature;

	/** @var string paypal api username */
	protected $sandbox_api_username;

	/** @var string paypal api password */
	protected $sandbox_api_password;

	/** @var string paypal api signature */
	protected $sandbox_api_signature;

	/** @var string paypal invoice prefix */
	protected $invoice_prefix;

	/** @var string paypal checkout brand name */
	protected $brand_name;

	/** @var string paypal payment page template */
	protected $template;

	/** @var string checkout with paypal express button style */
	protected $checkout_button_style = 'image';

	/** @var string hide standard checkout button on cart page */
	protected $hide_standard_checkout_button;

	/** @var string enable paypal bill me later */
	protected $enable_bill_me_later;

	/** @var string show paypal expresss checkout on checkout page */
	protected $show_on_checkout;

	/** @var string is paypal account optional */
	protected $paypal_account_optional;

	/** @var string type of paypal page to display as default */
	protected $landing_page;

	/** @var string paypal payment page url */
	protected $payment_url;

	/** @var WC_PayPal_Express_API instance */
	protected $api;

	/** @var WC_PayPal_Express_API instance */
	protected $transaction_handler_url;


	/**
	 * Load payment gateway and related settings
	 *
	 * @since 2.0
	 * @return \WC_Gateway_PayPal_Express
	 */
	public function __construct() {

		global $wc_paypal_express;

		parent::__construct(
			WC_PayPal_Express::GATEWAY_ID,
			$wc_paypal_express,
			WC_PayPal_Express::TEXT_DOMAIN,
			array(
				'method_title'       => __( 'PayPal Express', WC_PayPal_Express::TEXT_DOMAIN ),
				'method_description' => __( 'PayPal Express is <strong>purposely designed to skip WooCommerce\'s checkout process</strong> - customers will instead be taken directly to PayPal to authorize funds, and then return to your store to choose shipping and pay.', WC_PayPal_Express::TEXT_DOMAIN ),
				'supports'           => array(
					self::FEATURE_PRODUCTS,
					self::FEATURE_CREDIT_CARD_CHARGE,
					self::FEATURE_CREDIT_CARD_AUTHORIZATION,
					self::FEATURE_CREDIT_CARD_CAPTURE,
					self::FEATURE_DETAILED_CUSTOMER_DECLINE_MESSAGES,
				),
				'environments'       => array(
					'production' => __( 'Production', WC_PayPal_Express::TEXT_DOMAIN ),
					'test'       => __( 'Sandbox', WC_PayPal_Express::TEXT_DOMAIN ),
				),
				'payment_type' => 'paypal',
			)
		);

		// wc-api handler
		if ( ! has_action( 'woocommerce_api_' . strtolower( get_class( $this ) ) ) ) {
			add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( $this, 'handle_wc_api' ) );
		}

		// inject PayPal-provided checkout details to $_POST at checkout
		add_action( 'woocommerce_checkout_billing', array( $this, 'set_checkout_post_data' ) );

		// disable all other gateways at checkout when confirming payment
		add_action( 'woocommerce_available_payment_gateways', array( $this, 'disable_other_gateways' ) );

		// add checkout body class to aid JS selectors
		add_filter( 'body_class', array( $this, 'add_body_class' ) );

		// augment standard WC checkout fields with express checkout data
		add_action( 'woocommerce_checkout_fields', array( $this, 'augment_checkout_fields' ) );

		// auto-generate username and password when using express checkout
		add_filter( 'pre_option_woocommerce_registration_generate_username', array( $this, 'conditional_yes' ) );
		add_filter( 'pre_option_woocommerce_registration_generate_password', array( $this, 'conditional_yes' ) );

		// show formatted address in checkout
		add_action( 'woocommerce_before_checkout_billing_form', array( $this, 'render_formatted_billing_address' ), 9 );

		// add cancel link after order submit
		add_action( 'woocommerce_review_order_after_submit', array( $this, 'render_cancel_link' ) );

		// clear session after checkout and cart emptied
		add_action( 'woocommerce_cart_emptied', array( $this, 'clear_session_data' ) );
	}


	/**
	 * Initialize payment gateway settings fields
	 *
	 * @since 3.0.0
	 * @see \SV_WC_Payment_Gateway::get_method_form_fields()
	 * @return array method-specific form fields
	 */
	public function get_method_form_fields() {

		return array(

			'api_credentials' => array(
				'title'       => __( 'API Credentials', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'        => 'title',
			),

			'api_username' => array(
				'title'       => __( 'API User Name', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'        => 'text',
				'class'       => 'environment-field production-field',
				'desc_tip'    => __( 'This is the API User Name supplied by PayPal.', WC_PayPal_Express::TEXT_DOMAIN ),
				'default'     => '',
			),

			'api_password' => array(
				'title'       => __( 'API Password', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'        => 'password',
				'class'       => 'environment-field production-field',
				'desc_tip'    => __( 'This is the API Password supplied by PayPal.', WC_PayPal_Express::TEXT_DOMAIN ),
				'default'     => '',
			),

			'api_signature' => array(
				'title'       => __( 'API Signature', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'        => 'password',
				'class'       => 'environment-field production-field',
				'desc_tip'    => __( 'This is the API Signature supplied by PayPal.', WC_PayPal_Express::TEXT_DOMAIN ),
				'default'     => '',
			),

			'sandbox_api_username' => array(
				'title'       => __( 'Sandbox API User Name', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'        => 'text',
				'class'       => 'environment-field test-field',
				'desc_tip'    => __( 'This is the Sandbox API User Name supplied by PayPal.', WC_PayPal_Express::TEXT_DOMAIN ),
				'default'     => '',
			),

			'sandbox_api_password' => array(
				'title'       => __( 'Sandbox API Password', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'        => 'password',
				'class'       => 'environment-field test-field',
				'desc_tip'    => __( 'This is the Sandbox API Password supplied by PayPal.', WC_PayPal_Express::TEXT_DOMAIN ),
				'default'     => '',
			),

			'sandbox_api_signature' => array(
				'title'       => __( 'Sandbox API Signature', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'        => 'password',
				'class'       => 'environment-field test-field',
				'desc_tip'    => __( 'This is the Sandbox API Signature supplied by PayPal.', WC_PayPal_Express::TEXT_DOMAIN ),
				'default'     => '',
			),

			'display_options' => array(
				'title'       => __( 'Display Options', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'        => 'title',
			),

			'checkout_button_style' => array(
				'title'   => __( 'Checkout button style', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'    => 'select',
				'options' => array(
					'image'  => __( 'PayPal "Check out with PayPal" image', WC_PayPal_Express::TEXT_DOMAIN ),
					'button' => __( 'WooCommerce-style button', WC_PayPal_Express::TEXT_DOMAIN ),
				),
				'default' => 'image',
			),

			'hide_standard_checkout_button' => array(
				'title'   => __( 'Standard checkout button', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'    => 'checkbox',
				'label'   => __( 'Hide standard checkout button on cart page', WC_PayPal_Express::TEXT_DOMAIN ),
				'default' => 'no',
			),

			'show_on_checkout' => array(
				'title'   => __( 'Standard checkout', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'    => 'checkbox',
				'label'   => __( 'Show express checkout button on checkout page', WC_PayPal_Express::TEXT_DOMAIN ),
				'default' => 'no',
			),

			'advanced_options' => array(
				'title'       => __( 'Advanced Options', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'        => 'title',
			),

			'invoice_prefix' => array(
				'title'       => __( 'Invoice Prefix', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'        => 'text',
				'desc_tip'    => __( 'Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.', WC_PayPal_Express::TEXT_DOMAIN ),
				'default'     => 'WC-'
			),

			'paypal_account_optional' => array(
				'title'   => __( 'PayPal Account Optional', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'    => 'checkbox',
				'label'   => __( 'Allow customers to checkout without a PayPal account using their credit card. "PayPal Account Optional" must be turned on in your PayPal account. ', WC_PayPal_Express::TEXT_DOMAIN ),
				'default' => 'no',
			),

			'enable_bill_me_later' => array(
				'title'   => __( 'Enable Bill Me Later', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'    => 'checkbox',
				'label'   => __( 'Show the "Bill Me Later" button next to the PayPal Express Checkout button.', WC_PayPal_Express::TEXT_DOMAIN ),
				'default' => 'yes',
			),

			'landing_page' => array(
				'title'       => __( 'Landing Page', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'        => 'select',
				'desc_tip'    => __( 'Type of PayPal page to display as default. "PayPal Account Optional" must be checked for this option to be used.', WC_PayPal_Express::TEXT_DOMAIN ),
				'options'     => array(
					'login'   => 'Login',
					'billing' => 'Billing',
				),
				'default'     => 'login',
			),

			'brand_name' => array(
				'title'       => __( 'Brand name', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'        => 'text',
				'desc_tip'    => __( 'This overrides the business name in the PayPal account displayed on the PayPal hosted checkout pages.', WC_PayPal_Express::TEXT_DOMAIN ),
				'default'     => '',
			),

			'template' => array(
				'title'       => __( 'Custom Payment Page', WC_PayPal_Express::TEXT_DOMAIN ),
				'type'        => 'text',
				'desc_tip'    => __( 'This is the name of the custom payment page to use, if you have created one.', WC_PayPal_Express::TEXT_DOMAIN ),
				'default'     => '',
			),
		);
	}


	/**
	 * Get the express checkout button HTML
	 *
	 * @since 3.0.0
	 */
	public function render_express_checkout_button() {

		if ( ! $this->is_available() ) {
			return;
		}

		// cart total is available when on the cart page, cart subtotal is available with mini-cart
		if ( WC()->cart->total > 0 || WC()->cart->subtotal > 0 ) {

			$button_markup = '';
			$button_link   = $this->get_checkout_url( 'set_express_checkout' );

			if ( 'image' === $this->get_checkout_button_style() ) {

				$button_markup .= '<a class="paypal-express-checkout-button" href="' . $button_link .'">';
				$button_markup .= '<img src="https://www.paypal.com/' . $this->get_safe_locale() . '/i/btn/btn_xpressCheckout.gif" width="145" height="42" style="width: 145px; height: 42px; float: right; clear: both; margin-top: 3px;" border="0" align="top" alt="' . __( 'Check out with PayPal', WC_PayPal_Express::TEXT_DOMAIN ) . '" />';
				$button_markup .= "</a>";

			} else {

				$button_markup .= '<a class="paypal-express-checkout-button button alt" href="'. $button_link .'">' . __( 'Check out with PayPal &rarr;', WC_PayPal_Express::TEXT_DOMAIN ) .'</a>';

			}

			if ( $this->bill_me_later_enabled() ) {

				// Bill Me Later button
				$button_markup .= '<a class="paypal_checkout_button" href="' . add_query_arg( 'use_bml', 'true', $button_link ) . '">';
				$button_markup .= '<img src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_bml_SM.png" width="145" height="32" style="width: 145px; height: 32px; float: right; clear: both;" border="0" align="top" alt="' . __( 'Check out with PayPal', WC_PayPal_Express::TEXT_DOMAIN ) . '" />';
				$button_markup .= '</a>';

				// Marketing Message
				$button_markup .= '<a href="https://www.securecheckout.billmelater.com/paycapture-content/fetch?hash=AU826TU8&content=/bmlweb/ppwpsiw.html" >';
				$button_markup .= '<img src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_bml_text.png" width="130" height="22" style="width: 130px; height: 22px; float: right; clear: both;" border="0" align="top" />';
				$button_markup .= '</a>';
			}

			/**
			 * Filter the express checkout button markup.
			 *
			 * @since 3.0.0
			 * @param string $button_markup The button markup.
			 * @param string $button_link The checkout url (button link)
			 * @param object $paypal_express This instance of WC_Gateway_PayPal_Express class.
			 */
			echo apply_filters( 'wc_gateway_paypal_express_checkout_button_html', $button_markup, $button_link, $this );
		}
	}


	/**
	 * Maybe hide standard WC checkout button on the cart, if enabled
	 *
	 * @since 3.0.0
	 */
	public function maybe_hide_standard_checkout_button() {

		if ( $this->is_available() && $this->hide_standard_checkout_button() ) {
			?>
				<style type="text/css">
					.cart input.checkout-button,
					.cart a.checkout-button,
					.widget_shopping_cart a.checkout {
						display: none !important;
					}
				</style>
			<?php
		}
	}


	/**
	 * Maybe redirect to PayPal after checkout when the admin has opted to
	 * display PayPal Express as a payment method on checkout. This must happen
	 * very early in the checkout process because we don't want to create an order
	 * until the custome has confirmed their payment on PayPal and is redirected
	 * back to the site.
	 *
	 * This approach is preferred over a pure javascript approach as it's guaranteed
	 * to work even if javascript is disabled or not working properly.
	 *
	 * @since 3.1.2
	 */
	public function maybe_redirect_after_checkout() {

		if ( ! $this->is_express_checkout() ) {

			$args = array(
				'result'   => 'success',
				'redirect' => $this->get_checkout_url( 'set_express_checkout' ),
			);

			if ( is_ajax() ) {

				echo '<!--WC_START-->' . json_encode( $args ) . '<!--WC_END-->';

			} else {

				wp_redirect( $args['redirect'] );
			}

			exit;
		}
	}


	/**
	 * Maybe disable the gateway in 2 situations:
	 *
	 * 1) when the admin has opted not to show it at checkout
	 * 2) when on the checkout > pay page as PPE does not yet support this
	 *
	 * @since 3.0.0
	 * @return bool|true
	 */
	public function is_available() {

		$is_available = parent::is_available();

		// don't show on checkout page
		if ( ! $this->is_express_checkout() && is_checkout() && ! $this->show_on_checkout() ) {
			$is_available = false;
		}

		// don't display when order review table is rendered via AJAX
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['action'] ) && $_POST['action'] == 'woocommerce_update_order_review' && ! $this->show_on_checkout() ) {
			$is_available = false;
		}

		// don't show on checkout > pay page
		if ( is_checkout_pay_page() ) {
			$is_available = false;
		}

		return $is_available;
	}


	/**
	 * Handle WC-API endpoint
	 *
	 * @since 3.0.0
	 */
	public function handle_wc_api() {

		if ( ! isset( $_GET['action'] ) ) {
			return;
		}

		$cancel_url = WC()->cart->get_cart_url();

		switch ( $_GET['action'] ) {

			// called when the customer clicks the "Checkout with PayPal" button
			case 'set_express_checkout':

				$return_url = $this->get_checkout_url( 'get_express_checkout_details' );

				try {

					$response = $this->get_api()->set_express_checkout( array(
						'return_url'              => $return_url,
						'cancel_url'              => $cancel_url,
						'use_bml'                 => $this->bill_me_later_enabled() && isset( $_GET['use_bml'] ) && $_GET['use_bml'],
						'landing_page'            => $this->get_landing_page(),
						'brand_name'              => $this->get_brand_name(),
						'page_style'              => $this->get_template(),
						'paypal_account_optional' => $this->paypal_account_optional(),
						'payment_action'          => $this->perform_credit_card_charge() ? WC_Paypal_Express_API_Request::AUTH_CAPTURE : WC_Paypal_Express_API_Request::AUTH_ONLY,
					) );

					// redirect to PayPal so buyer can confirm payment details
					wp_redirect( $this->get_payment_url( $response->get_token() ) );

				} catch ( Exception $e ) {

					$this->add_debug_message( $e->getMessage(), 'error' );

					wc_add_notice( __( 'An error occurred, please try again or try an alternate form of payment.', WC_PayPal_Express::TEXT_DOMAIN ), 'error' );

					wp_redirect( $cancel_url );
				}

				exit;

			// called when the customer is returned from PayPal after authorizing their payment, used for retrieving the customer's checkout details
			case 'get_express_checkout_details':

				// bail if no token
				if ( ! isset( $_GET['token'] ) ) {
					return;
				}

				// get token to retrieve checkout details with
				$token = esc_attr( $_GET['token'] );

				try {

					$response = $this->get_api()->get_express_checkout_details( $token );

					// save customer information to session
					WC()->session->paypal_express_checkout = array(
						'token'            => $token,
						'shipping_details' => $response->get_shipping_details(),
						'order_note'       => $response->get_note_text(),
						'payer_id'         => $response->get_payer_id(),
					);

					// ensures PPE is selected at checkout
					WC()->session->chosen_payment_method = get_class( $this );

					// redirect customer to checkout
					wp_redirect( WC()->cart->get_checkout_url() );

				} catch ( Exception $e ) {

					$this->add_debug_message( $e->getMessage(), 'error' );

					wc_add_notice( __( 'An error occurred, please try again or try an alternate form of payment.', WC_PayPal_Express::TEXT_DOMAIN ), 'error' );

					wp_redirect( $cancel_url );
				}

				exit;
		}
	}


	/**
	 * Sets $_POST data in checkout
	 *
	 * After successfully getting checkout details from PayPal, copy those details
	 * to the global $_POST, which WC then uses for the checkout fields
	 *
	 * @since 3.0.0
	 */
	public function set_checkout_post_data() {

		// skip if this is a real POST
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		// skip if PayPal data not available
		if ( ! $this->is_express_checkout() || ! $this->get_session_data( 'shipping_details' ) ) {
			return;
		}

		// copy PayPal Express Checkout shipping details to $_POST so that the checkout fields are pre-filled
		foreach ( $this->get_session_data( 'shipping_details' ) as $field => $value ) {

			if ( $value ) {

				$_POST[ 'billing_' . $field ] = $value;
			}
		}

		// copy order note
		$order_note = $this->get_session_data( 'order_note' );
		if ( ! empty(  $order_note ) ) {
			$_POST['order_comments'] = $this->get_session_data( 'order_note' );
		}
	}


	/**
	 * Disable other gateways on checkout if Express Checkout has been
	 * setup with PayPal
	 *
	 * @since 3.0.0
	 * @param array $gateways
	 * @return array
	 */
	public function disable_other_gateways( $gateways ) {

		if ( $this->is_express_checkout() ) {
			foreach ( $gateways as $id => $gateway ) {
				if ( $id !== $this->id ) {
					unset( $gateways[ $id ] );
				}
			}
		}

		return $gateways;
	}


	/**
	 * Adds `paypal-express-checkout` class to the body element
	 * on checkout page, if using express checkout
	 *
	 * @since 3.0.0
	 * @param array $classes
	 * @return array
	 */
	public function add_body_class( $classes ) {

		if ( is_checkout() && $this->is_express_checkout() ) {

			$classes[] = 'paypal-express-checkout';
		}

		return $classes;
	}


	/**
	 * Enqueues the checkout CSS
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway::enqueue_scripts()
	 * @return boolean true if the scripts were enqueued, false otherwise
	 */
	public function enqueue_scripts() {

		// call to parent and determine whether we need to load
		if ( ! parent::enqueue_scripts() ) {
			return false;
		}

		// enqueue the frontend styles
		wp_enqueue_style( 'wc-paypal-express', $this->get_plugin()->get_plugin_url() . '/assets/css/frontend/wc-paypal-express.min.css', false, WC_PayPal_Express::VERSION );

		return true;
	}


	/**
	 * Augment checkout fields with PayPal classes
	 *
	 * @since 3.0.0
	 * @param array $checkout_fields
	 * @return array Modified checkout fields
	 */
	public function augment_checkout_fields( $checkout_fields ) {

		if ( $this->is_express_checkout() && $this->get_session_data( 'shipping_details' ) ) {

			foreach ( $this->get_session_data( 'shipping_details' ) as $field => $value ) {

				if ( isset( $checkout_fields['billing'] ) && isset( $checkout_fields['billing'][ 'billing_' . $field ] ) ) {

					$required = isset( $checkout_fields['billing']['billing_' . $field]['required'] ) && $checkout_fields['billing'][ 'billing_' . $field ]['required'];

					// Add class to each field that is provided by PayPal -
					// but only if PayPal provided the value or the field is not required
					if ( ! $required || $required && $value ) {
						$checkout_fields['billing'][ 'billing_' . $field ]['class'][] = 'paypal-express-provided';
						$checkout_fields['billing'][ 'billing_' . $field ]['class'][] = 'hidden';
					}
				}
			}
		}

		return $checkout_fields;
	}


	/**
	 * Render a formatted address, used in place of the displaying the billing
	 * fields on the checkout page
	 *
	 * @param string $type address type, `billing` or `shipping`
	 * @since 3.0.0
	 */
	public function render_formatted_address( $type ) {

		if ( ! $this->is_express_checkout() ) {
			return;
		}

		?>
		<div class="paypal-express-provided-address">
			<a href="#" class="js-show-address-fields" data-type="<?php echo esc_attr( $type ); ?>"><?php _e( 'Edit', WC_PayPal_Express::TEXT_DOMAIN ); ?></a>
			<address>
				<?php
					$address = array(
						'first_name'  => WC()->checkout->get_value( $type . '_first_name' ),
						'last_name'   => WC()->checkout->get_value( $type . '_last_name' ),
						'company'     => WC()->checkout->get_value( $type . '_company' ),
						'address_1'   => WC()->checkout->get_value( $type . '_address_1' ),
						'address_2'   => WC()->checkout->get_value( $type . '_address_2' ),
						'city'        => WC()->checkout->get_value( $type . '_city' ),
						'state'       => WC()->checkout->get_value( $type . '_state' ),
						'postcode'    => WC()->checkout->get_value( $type . '_postcode' ),
						'country'     => WC()->checkout->get_value( $type . '_country' ),
					);

					echo WC()->countries->get_formatted_address( $address );
				?>
			</address>
		</div>
		<?php
	}


	/**
	 * Render formatted billing address
	 *
	 * @since 3.0.0
	 */
	public function render_formatted_billing_address() {
		$this->render_formatted_address( 'billing' );
	}


	/**
	 * Render formatted shipping address
	 *
	 * @since 3.0.0
	 */
	public function render_formatted_shipping_address() {
		$this->render_formatted_address( 'shipping' );
	}


	/**
	 * Render Cancel link
	 *
	 * @since 3.0.0
	 */
	public function render_cancel_link() {

		if ( ! $this->is_available() || ! $this->is_express_checkout() ) {
			return;
		}

		echo sprintf(
			'<a href="%s" class="wc-paypal-express-cancel">%s</a>',
			 add_query_arg( array( 'wc_paypal_express_clear_session' => true ), WC()->cart->get_cart_url() ),
			__( 'Cancel', WC_PayPal_Express::TEXT_DOMAIN )
		);
	}


	/**
	 * Conditionally return 'yes' if currently doing express checkout.
	 *
	 * This method is used to alter the generate_username &
	 * generate_password option values
	 *
	 * @since 3.0.0
	 * @param string $value
	 * @return string
	 */
	public function conditional_yes( $value ) {

		return $this->is_express_checkout() ? 'yes' : $value;
	}


	/**
	 * Add any PayPal Express specific transaction information as
	 * class members of WC_Order instance. Added members can include:
	 *
	 * token - PayPal Express Checkout token
	 * paypal_express_payer_id - PayPal Express payer ID (whatever that means)
	 *
	 * @since 3.0.0
	 * @see WC_Payment_Gateway::get_order()
	 * @param int $order_id order ID being processed
	 * @return WC_Order object with PPE information added
	 */
	protected function get_order( $order_id ) {

		// add common order members
		$order = parent::get_order( $order_id );

		// checkout token
		$order->paypal_express_token = $this->get_session_data( 'token' );

		// payer ID
		if ( $this->get_session_data( 'payer_id' ) ) {
			$order->paypal_express_payer_id = $this->get_session_data( 'payer_id' );
		}

		// invoice prefix
		$order->paypal_express_invoice_prefix = $this->get_invoice_prefix();

		return $order;
	}


	/**
	 * Performs a payment transaction for the given order and returns the
	 * result
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order the order object
	 * @return \SV_WC_Payment_Gateway_API_Response the response
	 * @throws Exception network timeouts, etc
	 */
	protected function do_paypal_transaction( WC_Order $order ) {

		if ( $this->perform_credit_card_charge() ) {
			$response = $this->get_api()->credit_card_charge( $order );
		} else {
			$response = $this->get_api()->credit_card_authorization( $order );
		}

		// success! update order record
		if ( $response->transaction_approved() ) {

			// order note, e.g.
			// PayPal Express Test Instant Payment Approved (Transaction ID ABC)
			$message = sprintf(
				__( '%s %s %s %s Approved', WC_PayPal_Express::TEXT_DOMAIN ),
				$this->get_method_title(),
				$this->is_test_environment() ? __( 'Test', WC_PayPal_Express::TEXT_DOMAIN ) : '',
				$response->has_payment_type() ? ucwords( $response->get_payment_type() ) : '',
				$this->perform_credit_card_authorization() ? __( 'Authorization', WC_PayPal_Express::TEXT_DOMAIN ) : __( 'Payment', WC_PayPal_Express::TEXT_DOMAIN )
			);

			// adds the transaction id (if any) to the order note
			if ( $response->get_transaction_id() ) {
				$message .= ' ' . sprintf( __( '(Transaction ID %s)', WC_PayPal_Express::TEXT_DOMAIN ), $response->get_transaction_id() );
			}

			$order->add_order_note( $message );
		}

		return $response;
	}


	/**
	 * Adds any gateway-specific transaction data to the order
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_Direct::add_transaction_data()
	 * @param WC_Order $order the order object
	 * @param SV_WC_Payment_Gateway_API_Response $response the transaction response
	 */
	protected function add_payment_gateway_transaction_data( $order, $response ) {

		if ( $this->get_session_data( 'payer_id' ) ) {
			$this->add_order_meta( $order->id, 'payer_id', $this->get_session_data( 'payer_id' ) );
		}
	}


	/**
	 * Add original transaction ID for capturing a prior authorization
	 *
	 * @since 3.0.0
	 * @param WC_Order $order order object
	 * @return WC_Order object with payment and transaction information attached
	 */
	protected function get_order_for_capture( $order ) {

		$order = parent::get_order_for_capture( $order );

		$order->paypal_express_transaction_id = $this->get_order_meta( $order->id, 'trans_id' );

		return $order;
	}


	/**
	 * PayPal allows a 29 day authorization window.
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_Direct::get_authorization_time_window()
	 * @return int
	 */
	protected function get_authorization_time_window() {

		// 29 days in hours
		return 696;
	}


	/**
	 * Clear PayPal Express Checkout data from WC session after the cart is
	 * emptied
	 *
	 * @since 3.0.0
	 */
	public function clear_session_data() {

		unset( WC()->session->paypal_express_checkout );
	}


	/** Helpers ******************************************************/


	/**
	 * Check if the express checkout data is set
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	protected function is_express_checkout() {

		return isset( WC()->session->paypal_express_checkout );
	}


	/**
	 * Get PapPal Express Checkout data from WC session
	 *
	 * @since 3.0.0
	 * @param string $key Optional. session key to get
	 * @return mixed|array session value at specified key or array of all session data
	 */
	protected function get_session_data( $key = '' ) {

		$session_data = null;

		if ( empty( $key ) ) {

			$session_data = WC()->session->paypal_express_checkout;

		} elseif ( isset( WC()->session->paypal_express_checkout[ $key ] ) ) {

			$session_data = WC()->session->paypal_express_checkout[ $key ];

		}

		return $session_data;
	}


	/** Getters ******************************************************/


	/**
	 * Get the API object
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway::get_api()
	 * @return \WC_PayPal_Express_API API instance
	 */
	public function get_api() {

		if ( is_object( $this->api ) ) {
			return $this->api;
		}

		$plugin_path = $this->get_plugin()->get_plugin_path();

		$classes = array(
			'api',
			'api-request',
			'api-response',
			'api-checkout-response',
			'api-payment-response',
			'api-capture-response',
		);

		foreach ( $classes as $class ) {
			require_once( $plugin_path . "/includes/api/class-wc-paypal-express-{$class}.php" );
		}

		return $this->api = new WC_PayPal_Express_API( $this->get_id(), $this->get_environment(), $this->get_api_username(), $this->get_api_password(), $this->get_api_signature() );
	}


	/**
	 * Get the API username
	 *
	 * @since 3.0.0
	 * @return string API username
	 */
	protected function get_api_username() {
		return $this->is_production_environment() ? $this->api_username : $this->sandbox_api_username;
	}


	/**
	 * Get the API password
	 *
	 * @since 3.0.0
	 * @return string API password
	 */
	protected function get_api_password() {
		return $this->is_production_environment() ? $this->api_password : $this->sandbox_api_password;
	}


	/**
	 * Get the API signature
	 *
	 * @since 3.0.0
	 * @return string API signature
	 */
	protected function get_api_signature() {
		return $this->is_production_environment() ? $this->api_signature : $this->sandbox_api_signature;
	}


	/**
	 * Get Express Checkout Button language (locale) based on current
	 * WordPress locale.
	 *
	 * @link http://wpcentral.io/internationalization/
	 * @link https://developer.paypal.com/docs/classic/express-checkout/integration-guide/ECButtonIntegration/#id089QD0O0TX4__id08AH8N000HS
	 *
	 * @since 2.0
	 * @return string locale
	 */
	protected function get_safe_locale() {

		$locale = get_locale();

		$safe_locales = array(
			'en_US',
			'de_DE',
			'en_AU',
			'nl_NL',
			'fr_FR',
			'zh_XC',
			'es_XC',
			'zh_CN',
			'fr_XC',
			'en_GB',
			'it_IT',
			'pl_PL',
			'ja_JP',
		);

		if ( ! in_array( $locale, $safe_locales ) ) {
			$locale = 'en_US';
		}

		/**
		 * Filter the Express Checkout button locale.
		 *
		 * @since 3.0.0
		 * @param string $lang The button locale.
		 */
		return apply_filters( 'wc_paypal_express_checkout_button_language', $locale );
	}


	/**
	 * Get checkout button style
	 *
	 * @since 2.0
	 * @return string button style
	 */
	protected function get_checkout_button_style() {

		return $this->checkout_button_style;
	}


	/**
	 * Get landing page
	 *
	 * @since 2.0
	 * @return string landing page
	 */
	protected function get_landing_page() {

		return $this->landing_page;
	}


	/**
	 * Get PayPal invoice prefix
	 *
	 * @since 3.0.0
	 * @return string invoice prefix
	 */
	protected function get_invoice_prefix() {

		return $this->invoice_prefix;
	}


	/**
	 * Get brand name
	 *
	 * @since 2.0
	 * @return string brand name
	 */
	protected function get_brand_name() {

		return $this->brand_name;
	}


	/**
	 * Get PayPal template (Custom Payment Page)
	 *
	 * @since 3.1.0
	 * @return string template
	 */
	protected function get_template() {

		return $this->template;
	}


	/**
	 * Whether bill me later is enabled or not
	 *
	 * @since 2.0
	 * @return bool, true if bill me later is enabled, false otherwise
	 */
	protected function bill_me_later_enabled() {

		return ( 'yes' === $this->enable_bill_me_later );
	}


	/**
	 * Whether PayPal account is optional or not
	 *
	 * @since 2.0
	 * @return bool, true if account is optional, false otherwise
	 */
	protected function paypal_account_optional() {

		return ( 'yes' === $this->paypal_account_optional );
	}


	/**
	 * Returns whether the PayPal Express gateway should be included as a payment
	 * method on the checkout page
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	protected function show_on_checkout() {

		return 'yes' === $this->show_on_checkout;
	}


	/**
	 * Returns true if the standard checkout button should be hidden
	 *
	 * @since 3.0.0
	 * @return boolean true if the standard checkout button should be hidden, false otherwise
	 */
	protected function hide_standard_checkout_button() {
		return 'yes' == $this->hide_standard_checkout_button;
	}


	/**
	 * Get the wc-api URL to redirect to
	 *
	 * @since 3.0.0
	 * @param string $action checkout action, either `set_express_checkout or `get_express_checkout_details`
	 * @return string URL
	 */
	public function get_checkout_url( $action ) {

		return add_query_arg( 'action', $action, WC()->api_request_url( get_class( $this ) ) );
	}


	/**
	 * Get the PayPal URL to redirect customer to in order to confirm their
	 * payment
	 *
	 * @link https://developer.paypal.com/docs/classic/express-checkout/integration-guide/ECGettingStarted/#id0832BA0605Z
	 *
	 * @since 3.0.0
	 * @param string $token PayPal Express checkout token
	 * @return string the payment URL
	 */
	public function get_payment_url( $token ) {

		$params = array(
			'cmd'   => '_express-checkout',
			'token' => $token,
		);

		$paypal_url = $this->is_production_environment() ? self::PRODUCTION_URL_ENDPOINT : self::TEST_URL_ENDPOINT;

		return add_query_arg( $params, $paypal_url );
	}


	/**
	 * Overrides parent class to exclude the is_credit_gateway_gateway() check
	 * as PPE is a quasi-direct gateway
	 *
	 * @since 3.0.0
	 * @return boolean true if the gateway supports authorization
	 */
	public function supports_credit_card_authorization() {
		return $this->supports( self::FEATURE_CREDIT_CARD_AUTHORIZATION );
	}


	/**
	 * Overrides parent class to exclude the is_credit_gateway_gateway() check
	 * as PPE is a quasi-direct gateway
	 *
	 * @since 3.0.0
	 * @return boolean true if the gateway supports charges
	 */
	public function supports_credit_card_charge() {
		return $this->supports( self::FEATURE_CREDIT_CARD_CHARGE );
	}


	/**
	 * Return the default payment method title
	 *
	 * @since 3.0.0
	 * @return string payment method title to show on checkout
	 */
	protected function get_default_title() {

		return __( 'PayPal Express', WC_PayPal_Express::TEXT_DOMAIN );
	}


	/**
	 * Get the default payment method description
	 *
	 * @since 3.0.0
	 * @return string payment method description to show on checkout
	 */
	protected function get_default_description() {

		return __( "Pay via PayPal; you can pay with your credit card if you don't have a PayPal account", WC_PayPal_Express::TEXT_DOMAIN );
	}


	/**
	 * Returns the direct link to view the transaction in PayPal, e.g.
	 *
	 * https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=ABC123
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway::get_transaction_url()
	 * @param WC_Order $order the order object
	 * @return string transaction url
	 */
	public function get_transaction_url( $order ) {

		$params = array(
			'cmd' => '_view-a-trans',
			'id'  => $this->get_order_meta( $order->id, 'trans_id' ),
		);

		$endpoint = ( 'test' == $this->get_order_meta( $order->id, 'environment' ) ) ? self::TEST_URL_ENDPOINT : self::PRODUCTION_URL_ENDPOINT;

		return add_query_arg( $params, $endpoint );
	}

}
