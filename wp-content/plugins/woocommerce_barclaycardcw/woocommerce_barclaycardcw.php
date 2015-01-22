<?php

/**
 * Plugin Name: WooCommerce BarclaycardCw
 * Plugin URI: http://www.customweb.ch
 * Description: This plugin adds the BarclaycardCw payment gateway to your WooCommerce.
 * Version: 2.1.164
 * Author: customweb GmbH
 * Author URI: http://www.customweb.ch
 */

/**
 *  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2013 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.customweb.ch/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.customweb.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit();
}

// Load Language Files
load_plugin_textdomain('woocommerce_barclaycardcw', false, basename(dirname(__FILE__)) . '/translations');

require_once dirname(__FILE__) . '/lib/loader.php';
require_once 'BarclaycardCwUtil.php';

// Add translation Adapter
BarclaycardCwUtil::includeClass('BarclaycardCw_TranslationResolver');

// Get all admin functionality
require_once BarclaycardCwUtil::getBasePath() . '/admin.php';

register_activation_hook(__FILE__, array(
	'BarclaycardCwUtil',
	'installPlugin' 
));
add_filter('woocommerce_payment_gateways', array(
	'BarclaycardCwUtil',
	'addPaymentMethods' 
));

if (!is_admin()) {

	function woocommerce_barclaycardcw_add_frontend_css(){
		wp_register_style('woocommerce_barclaycardcw_frontend_styles', plugins_url('resources/css/frontend.css', __FILE__));
		wp_enqueue_style('woocommerce_barclaycardcw_frontend_styles');
		
		wp_register_script('barclaycardcw_frontend_script', plugins_url('resources/js/frontend.js', __FILE__), array(
			'jquery' 
		));
		wp_enqueue_script('barclaycardcw_frontend_script');
	}
	add_action('wp_enqueue_scripts', 'woocommerce_barclaycardcw_add_frontend_css');
}

// Log Errors
function woocommerce_barclaycardcw_add_errors(){
	if (isset($_GET['barclaycardcw_failed_transaction_id'])) {
		BarclaycardCwUtil::includeClass('BarclaycardCw_Transaction');
		$dbTransaction = BarclaycardCwUtil::getTransactionById($_GET['barclaycardcw_failed_transaction_id']);
		if(get_current_user_id()!= null && get_current_user_id() != '0' && $dbTransaction->getCustomerId() == get_current_user_id()) { 
			

	if (!function_exists('wc_add_notice')) {
		global $woocommerce;
		$woocommerce->add_error( current($dbTransaction->getTransactionObject()->getErrorMessages()) );
	}
	else {
		wc_add_notice(current($dbTransaction->getTransactionObject()->getErrorMessages()), 'error');
	}
	

		}
	}
}
add_action('init', 'woocommerce_barclaycardcw_add_errors');

add_action('woocommerce_before_checkout_billing_form', array(
	'BarclaycardCwUtil',
	'actionBeforeCheckoutBillingForm' 
));
add_action('woocommerce_before_checkout_shipping_form', array(
	'BarclaycardCwUtil',
	'actionBeforeCheckoutShippingForm' 
));

function woocommerce_barclaycardcw_create_order_status(){
	register_post_status('wc-barclaycardcw-pending', 
			array(
				'label' => 'Barclaycard pending',
				'public' => true,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop('Barclaycard pending <span class="count">(%s)</span>', 
						'Barclaycard pending <span class="count">(%s)</span>') 
			));
}

// Add to list of WC Order statuses
function woocommerce_barclaycardcw_add_order_status( $order_statuses ) {

	$order_statuses['wc-barclaycardcw-pending'] = 'Barclaycard pending';
	return $order_statuses; 
}

if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '2.2.0') >= 0) {
	add_action('init', 'woocommerce_barclaycardcw_create_order_status');
	add_filter( 'wc_order_statuses', 'woocommerce_barclaycardcw_add_order_status' );
}



function createBarclaycardCwCronInterval($schedules){
	$schedules['BarclaycardCwCronInterval'] = array(
		'interval' => 120,
		'display' => __('BarclaycardCw Interval', 'woocommerce_barclaycardcw') 
	);
	return $schedules;
}

function createBarclaycardCwCron(){
	$timestamp = wp_next_scheduled('BarclaycardCwCron');
	if ($timestamp == false) {
		wp_schedule_event(time() + 120, 'BarclaycardCwCronInterval', 'BarclaycardCwCron');
	}
}

function deleteBarclaycardCwCron(){
	wp_clear_scheduled_hook('BarclaycardCwCron');
}

function runBarclaycardCwCron(){
	BarclaycardCwUtil::includeClass('BarclaycardCw_UpdateObserver');
	BarclaycardCw_UpdateObserver::run();
}

//Cron Functions to pull update
register_activation_hook(__FILE__, 'createBarclaycardCwCron');
register_deactivation_hook(__FILE__, 'deleteBarclaycardCwCron');

add_filter('cron_schedules', 'createBarclaycardCwCronInterval');
add_action('BarclaycardCwCron', 'runBarclaycardCwCron');




//Action to add payment information to order confirmation page, and email
add_action('woocommerce_thankyou', array(
	'BarclaycardCwUtil',
	'thankYouPageHtml' 
));
add_action('woocommerce_email_before_order_table', array(
	'BarclaycardCwUtil',
	'orderEmailHtml' 
), 10, 3);


//Ajax Handling
add_action('wp_ajax_woocommerce_barclaycardcw_update_payment_form', 'woocommerce_barclaycardcw_ajax_update_payment_form');
add_action('wp_ajax_nopriv_woocommerce_barclaycardcw_update_payment_form', 'woocommerce_barclaycardcw_ajax_update_payment_form');

function woocommerce_barclaycardcw_ajax_update_payment_form(){
	if (!isset($_POST['payment_method'])) {
		echo 'no payment method provided.';
		die();
	}
	$paymentMethod = BarclaycardCwUtil::getPaymentMehtodInstance($_POST['payment_method']);
	$paymentMethod->payment_fields();
	die();
}

//Fix to avoid multiple cart calculations
function woocommerce_barclaycardcw_before_calculate_totals($cart){
	$cart->totalCalculatedCw = true;
	return;
}
add_action('woocommerce_before_calculate_totals', 'woocommerce_barclaycardcw_before_calculate_totals');
