<?php
/**
 * WooCommerce Admin Functions
 *
 * @author      WooThemes
 * @category    Core
 * @package     WooCommerce/Admin/Functions
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get all WooCommerce screen ids
 *
 * @return array
 */
function wc_get_screen_ids() {
	$wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );
	$screen_ids   = array(
		'toplevel_page_' . $wc_screen_id,
		$wc_screen_id . '_page_wc-reports',
		$wc_screen_id . '_page_wc-settings',
		$wc_screen_id . '_page_wc-status',
		$wc_screen_id . '_page_wc-addons',
		'product_page_product_attributes',
		'edit-product',
		'product',
		'edit-shop_coupon',
		'shop_coupon',
		'edit-product_cat',
		'edit-product_tag',
		'edit-product_shipping_class'
	);

	foreach ( wc_get_order_types() as $type ) {
		$screen_ids[] = $type;
		$screen_ids[] = 'edit-' . $type;
	}

	return apply_filters( 'woocommerce_screen_ids', $screen_ids );
}

/**
 * Create a page and store the ID in an option.
 *
 * @access public
 * @param mixed $slug Slug for the new page
 * @param string $option Option name to store the page's ID
 * @param string $page_title (default: '') Title for the new page
 * @param string $page_content (default: '') Content for the new page
 * @param int $post_parent (default: 0) Parent for the new page
 * @return int page ID
 */
function wc_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
	global $wpdb;

	$option_value = get_option( $option );

	if ( $option_value > 0 && get_post( $option_value ) )
		return -1;

	$page_found = null;

	if ( strlen( $page_content ) > 0 ) {
		// Search for an existing page with the specified page content (typically a shortcode)
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
	} else {
		// Search for an existing page with the specified page slug
		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_name = %s LIMIT 1;", $slug ) );
	}

	$page_found = apply_filters( 'woocommerce_create_page_id', $page_found, $slug, $page_content );

	if ( $page_found ) {
		if ( ! $option_value ) {
			update_option( $option, $page_found );
		}

		return $page_found;
	}

	$page_data = array(
		'post_status'       => 'publish',
		'post_type'         => 'page',
		'post_author'       => 1,
		'post_name'         => $slug,
		'post_title'        => $page_title,
		'post_content'      => $page_content,
		'post_parent'       => $post_parent,
		'comment_status'    => 'closed'
	);
	$page_id = wp_insert_post( $page_data );

	if ( $option ) {
		update_option( $option, $page_id );
	}

	return $page_id;
}

/**
 * Output admin fields.
 *
 * Loops though the woocommerce options array and outputs each field.
 *
 * @param array $options Opens array to output
 */
function woocommerce_admin_fields( $options ) {
	if ( ! class_exists( 'WC_Admin_Settings' ) )
		include 'class-wc-admin-settings.php';

	WC_Admin_Settings::output_fields( $options );
}

/**
 * Update all settings which are passed.
 *
 * @access public
 * @param array $options
 * @return void
 */
function woocommerce_update_options( $options ) {
	if ( ! class_exists( 'WC_Admin_Settings' ) )
		include 'class-wc-admin-settings.php';

	WC_Admin_Settings::save_fields( $options );
}

/**
 * Get a setting from the settings API.
 *
 * @param mixed $option_name
 * @return string
 */
function woocommerce_settings_get_option( $option_name, $default = '' ) {
	if ( ! class_exists( 'WC_Admin_Settings' ) )
		include 'class-wc-admin-settings.php';

	return WC_Admin_Settings::get_option( $option_name, $default );
}

/**
 * Generate CSS from the less file when changing colours.
 *
 * @access public
 * @return void
 */
function woocommerce_compile_less_styles() {
	$colors         = array_map( 'esc_attr', (array) get_option( 'woocommerce_frontend_css_colors' ) );
	$base_file      = WC()->plugin_path() . '/assets/css/woocommerce-base.less';
	$less_file      = WC()->plugin_path() . '/assets/css/woocommerce.less';
	$css_file       = WC()->plugin_path() . '/assets/css/woocommerce.css';

	// Write less file
	if ( is_writable( $base_file ) && is_writable( $css_file ) ) {

		// Colours changed - recompile less
		if ( ! class_exists( 'lessc' ) )
			include_once( WC()->plugin_path() . '/includes/libraries/class-lessc.php' );
		if ( ! class_exists( 'cssmin' ) )
			include_once( WC()->plugin_path() . '/includes/libraries/class-cssmin.php' );

		try {
			// Set default if colours not set
			if ( ! $colors['primary'] ) $colors['primary'] = '#ad74a2';
			if ( ! $colors['secondary'] ) $colors['secondary'] = '#f7f6f7';
			if ( ! $colors['highlight'] ) $colors['highlight'] = '#85ad74';
			if ( ! $colors['content_bg'] ) $colors['content_bg'] = '#ffffff';
			if ( ! $colors['subtext'] ) $colors['subtext'] = '#777777';

			// Write new color to base file
			$color_rules = "
@primary:       " . $colors['primary'] . ";
@primarytext:   " . wc_light_or_dark( $colors['primary'], 'desaturate(darken(@primary,50%),18%)', 'desaturate(lighten(@primary,50%),18%)' ) . ";

@secondary:     " . $colors['secondary'] . ";
@secondarytext: " . wc_light_or_dark( $colors['secondary'], 'desaturate(darken(@secondary,60%),18%)', 'desaturate(lighten(@secondary,60%),18%)' ) . ";

@highlight:     " . $colors['highlight'] . ";
@highlightext:  " . wc_light_or_dark( $colors['highlight'], 'desaturate(darken(@highlight,60%),18%)', 'desaturate(lighten(@highlight,60%),18%)' ) . ";

@contentbg:     " . $colors['content_bg'] . ";

@subtext:       " . $colors['subtext'] . ";
			";

			file_put_contents( $base_file, $color_rules );

			$less         = new lessc;
			$compiled_css = $less->compileFile( $less_file );
			$compiled_css = CssMin::minify( $compiled_css );

			if ( $compiled_css )
				file_put_contents( $css_file, $compiled_css );

		} catch ( exception $ex ) {
			wp_die( __( 'Could not compile woocommerce.less:', 'woocommerce' ) . ' ' . $ex->getMessage() );
		}
	}
}

/**
 * Save order items
 *
 * @since 2.2
 * @param int $order_id Order ID
 * @param array $items Order items to save
 * @return void
 */
function wc_save_order_items( $order_id, $items ) {
	global $wpdb;

	// Order items + fees
	$subtotal = 0;
	$total    = 0;
	$taxes    = array( 'items' => array(), 'shipping' => array() );

	if ( isset( $items['order_item_id'] ) ) {

		$get_values = array( 'order_item_id', 'order_item_name', 'order_item_qty', 'line_subtotal', 'line_subtotal_tax', 'line_total', 'line_tax', 'order_item_tax_class' );

		foreach ( $get_values as $value ) {
			$$value = isset( $items[ $value ] ) ? $items[ $value ] : array();
		}

		foreach ( $order_item_id as $item_id ) {

			$item_id = absint( $item_id );

			if ( isset( $order_item_name[ $item_id ] ) ) {
				$wpdb->update(
					$wpdb->prefix . 'woocommerce_order_items',
					array( 'order_item_name' => wc_clean( $order_item_name[ $item_id ] ) ),
					array( 'order_item_id' => $item_id ),
					array( '%s' ),
					array( '%d' )
				);
			}

			if ( isset( $order_item_qty[ $item_id ] ) ) {
				wc_update_order_item_meta( $item_id, '_qty', wc_stock_amount( $order_item_qty[ $item_id ] ) );
			}

			if ( isset( $order_item_tax_class[ $item_id ] ) ) {
				wc_update_order_item_meta( $item_id, '_tax_class', wc_clean( $order_item_tax_class[ $item_id ] ) );
			}

			// Get values. Subtotals might not exist, in which case copy value from total field
			$line_total[ $item_id ]        = isset( $line_total[ $item_id ] ) ? $line_total[ $item_id ] : 0;
			$line_subtotal[ $item_id ]     = isset( $line_subtotal[ $item_id ] ) ? $line_subtotal[ $item_id ] : $line_total[ $item_id ];
			$line_tax[ $item_id ]          = isset( $line_tax[ $item_id ] ) ? $line_tax[ $item_id ] : array();
			$line_subtotal_tax[ $item_id ] = isset( $line_subtotal_tax[ $item_id ] ) ? $line_subtotal_tax[ $item_id ] : $line_tax[ $item_id ];

			// Update values
			wc_update_order_item_meta( $item_id, '_line_subtotal', wc_format_decimal( $line_subtotal[ $item_id ] ) );
			wc_update_order_item_meta( $item_id, '_line_total', wc_format_decimal( $line_total[ $item_id ] ) );
			wc_update_order_item_meta( $item_id, '_line_subtotal_tax', array_sum( array_map( 'wc_format_decimal', $line_subtotal_tax[ $item_id ] ) ) );
			wc_update_order_item_meta( $item_id, '_line_tax', array_sum( array_map( 'wc_format_decimal', $line_tax[ $item_id ] ) ) );

			// Save line tax data - Since 2.2
			$tax_data_total    = array_map( 'wc_format_decimal', $line_tax[ $item_id ] );
			$tax_data_subtotal = array_map( 'wc_format_decimal', $line_subtotal_tax[ $item_id ] );
			wc_update_order_item_meta( $item_id, '_line_tax_data', array( 'total' => $tax_data_total, 'subtotal' => $tax_data_subtotal ) );
			$taxes['items'][] = $tax_data_total;

			// Total up
			$subtotal += wc_format_decimal( $line_subtotal[ $item_id ] );
			$total    += wc_format_decimal( $line_total[ $item_id ] );

			// Clear meta cache
			wp_cache_delete( $item_id, 'order_item_meta' );
		}
	}

	// Save meta
	$meta_keys   = isset( $items['meta_key'] ) ? $items['meta_key'] : array();
	$meta_values = isset( $items['meta_value'] ) ? $items['meta_value'] : array();

	foreach ( $meta_keys as $id => $meta_key ) {
		$meta_value = ( empty( $meta_values[ $id ] ) && ! is_numeric( $meta_values[ $id ] ) ) ? '' : $meta_values[ $id ];
		$wpdb->update(
			$wpdb->prefix . 'woocommerce_order_itemmeta',
			array(
				'meta_key'   => wp_unslash( $meta_key ),
				'meta_value' => wp_unslash( $meta_value )
			),
			array( 'meta_id' => $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	// Shipping Rows
	$order_shipping = 0;

	if ( isset( $items['shipping_method_id'] ) ) {

		$get_values = array( 'shipping_method_id', 'shipping_method_title', 'shipping_method', 'shipping_cost', 'shipping_taxes' );

		foreach ( $get_values as $value ) {
			$$value = isset( $items[ $value ] ) ? $items[ $value ] : array();
		}

		foreach ( $shipping_method_id as $item_id ) {
			$item_id      = absint( $item_id );
			$method_id    = wc_clean( $shipping_method[ $item_id ] );
			$method_title = wc_clean( $shipping_method_title[ $item_id ] );
			$cost         = wc_format_decimal( $shipping_cost[ $item_id ] );
			$ship_taxes   = isset( $shipping_taxes[ $item_id ] ) ? array_map( 'wc_format_decimal', $shipping_taxes[ $item_id ] ) : array();

			$wpdb->update(
				$wpdb->prefix . 'woocommerce_order_items',
				array( 'order_item_name' => $method_title ),
				array( 'order_item_id' => $item_id ),
				array( '%s' ),
				array( '%d' )
			);

			wc_update_order_item_meta( $item_id, 'method_id', $method_id );
			wc_update_order_item_meta( $item_id, 'cost', $cost );
			wc_update_order_item_meta( $item_id, 'taxes', $ship_taxes );

			$taxes['shipping'][] = $ship_taxes;

			$order_shipping += $cost;
		}
	}

	// Taxes
	$order_taxes        = isset( $items['order_taxes'] ) ? $items['order_taxes'] : array();
	$taxes_items        = array();
	$taxes_shipping     = array();
	$total_tax          = 0;
	$total_shipping_tax = 0;

	// Sum items taxes
	foreach ( $taxes['items'] as $rates ) {
		foreach ( $rates as $id => $value ) {
			if ( isset( $taxes_items[ $id ] ) ) {
				$taxes_items[ $id ] += $value;
			} else {
				$taxes_items[ $id ] = $value;
			}
		}
	}

	// Sum shipping taxes
	foreach ( $taxes['shipping'] as $rates ) {
		foreach ( $rates as $id => $value ) {
			if ( isset( $taxes_shipping[ $id ] ) ) {
				$taxes_shipping[ $id ] += $value;
			} else {
				$taxes_shipping[ $id ] = $value;
			}
		}
	}

	// Update order taxes
	foreach ( $order_taxes as $item_id => $rate_id ) {
		if ( isset( $taxes_items[ $rate_id ] ) ) {
			$_total = wc_format_decimal( $taxes_items[ $rate_id ] );
			wc_update_order_item_meta( $item_id, 'tax_amount', $_total );

			$total_tax += $_total;
		}

		if ( isset( $taxes_shipping[ $rate_id ] ) ) {
			$_total = wc_format_decimal( $taxes_shipping[ $rate_id ] );
			wc_update_order_item_meta( $item_id, 'shipping_tax_amount', $_total );

			$total_shipping_tax += $_total;
		}
	}

	// Update order shipping total
	update_post_meta( $order_id, '_order_shipping', $order_shipping );

	// Update cart discount from item totals
	update_post_meta( $order_id, '_cart_discount', $subtotal - $total );

	// Update totals
	update_post_meta( $order_id, '_order_discount', wc_format_decimal( $items['_order_discount'] ) );
	update_post_meta( $order_id, '_order_total', wc_format_decimal( $items['_order_total'] ) );

	// Update tax
	update_post_meta( $order_id, '_order_tax', wc_format_decimal( $total_tax ) );
	update_post_meta( $order_id, '_order_shipping_tax', wc_format_decimal( $total_shipping_tax ) );

	// Remove old values
	delete_post_meta( $order_id, '_shipping_method' );
	delete_post_meta( $order_id, '_shipping_method_title' );

	// Set the currency
	add_post_meta( $order_id, '_order_currency', get_woocommerce_currency(), true );

	// inform other plugins that the items have been saved
	do_action( 'woocommerce_saved_order_items', $order_id, $items );
}

add_action( 'woocommerce_process_shop_order_meta', 'woocommerce_process_shop_order', 10, 2 );
function woocommerce_process_shop_order ( $post_id, $post ) {
    $gif_wrap = sanitize_text_field($_POST['gif_wrap']);
    $post_ID = sanitize_text_field($_POST['post_ID']);
    update_post_meta($post_ID,'_gif_wrap',$gif_wrap);
}
