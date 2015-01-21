<?php

/**
 * You are allowed to use this API in your web application.
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

require_once dirname(dirname(__FILE__)) . '/classes/BarclaycardCw/PaymentMethod.php'; 

class BarclaycardCw_Maestro extends BarclaycardCw_PaymentMethod
{
	public $machineName = 'maestro';
	public $admin_title = 'Maestro';
	public $title = 'Maestro';
	
	protected function getMethodSettings(){
		return array(
			'capturing' => array(
				'title' => __("Capturing", 'woocommerce_barclaycardcw'),
 				'default' => 'direct',
 				'description' => __("Should the amount be captured automatically after the order (direct) or should the amount only be reserved (deferred)?", 'woocommerce_barclaycardcw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'direct' => __("Directly after order", 'woocommerce_barclaycardcw'),
 					'deferred' => __("Deferred", 'woocommerce_barclaycardcw'),
 				),
 			),
 			'status_authorized' => array(
				'title' => __("Authorized Status", 'woocommerce_barclaycardcw'),
 				'default' => (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '2.2.0') >= 0) ? 'wc-processing' : 'processing',
 				'description' => __("This status is set, when the payment was successfull and it is authorized.", 'woocommerce_barclaycardcw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
					'use-default' => __("Use WooCommerce rules", 'woocommerce_barclaycardcw'),
 				),
 				'is_order_status' => true,
 			),
 			'status_uncertain' => array(
				'title' => __("Uncertain Status", 'woocommerce_barclaycardcw'),
 				'default' => (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '2.2.0') >= 0) ? 'wc-on-hold' : 'on-hold',
 				'description' => __("You can specify the order status for new orders that have an uncertain authorisation status.", 'woocommerce_barclaycardcw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
				),
 				'is_order_status' => true,
 			),
 			'status_cancelled' => array(
				'title' => __("Cancelled Status", 'woocommerce_barclaycardcw'),
 				'default' => (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '2.2.0') >= 0) ? 'wc-cancelled' : 'cancelled',
 				'description' => __("You can specify the order status when an order is cancelled.", 'woocommerce_barclaycardcw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
					'no_status_change' => __("Don't change order status", 'woocommerce_barclaycardcw'),
 				),
 				'is_order_status' => true,
 			),
 			'status_captured' => array(
				'title' => __("Captured Status", 'woocommerce_barclaycardcw'),
 				'default' => 'no_status_change',
 				'description' => __("You can specify the order status for orders that are captured either directly after the order or manually in the backend.", 'woocommerce_barclaycardcw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
					'no_status_change' => __("Don't change order status", 'woocommerce_barclaycardcw'),
 				),
 				'is_order_status' => true,
 			),
 			'status_success_after_uncertain' => array(
				'title' => __("HTTP Status for Successful Payments", 'woocommerce_barclaycardcw'),
 				'default' => 'no_status_change',
 				'description' => __("You can specify the order status for orders that are successful after being in a uncertain state. In order to use this setting, you will need to activate the http-request for status changes as outlined in the manual.", 'woocommerce_barclaycardcw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
					'no_status_change' => __("Don't change order status", 'woocommerce_barclaycardcw'),
 				),
 				'is_order_status' => true,
 			),
 			'status_refused_after_uncertain' => array(
				'title' => __("HTTP Status for Refused Payments", 'woocommerce_barclaycardcw'),
 				'default' => 'no_status_change',
 				'description' => __("You can specify the order status for orders that are refused after being in a uncertain state. In order to use this feature you will have to set up the http request for status changes as outlined in the manual.", 'woocommerce_barclaycardcw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
					'no_status_change' => __("Don't change order status", 'woocommerce_barclaycardcw'),
 				),
 				'is_order_status' => true,
 			),
 			'refusing_threshold' => array(
				'title' => __("Refused Transaction Threshold", 'woocommerce_barclaycardcw'),
 				'default' => '3',
 				'description' => __("A typical pattern of a fraud transaction is a series of refused transaction before one of them is accepted. This setting defines the threshold after any following transaction is marked as uncertain. E.g. a threshold of three will mark any successful transaction after three refused transaction as uncertain.", 'woocommerce_barclaycardcw'),
 				'cwType' => 'textfield',
 				'type' => 'text',
 			),
 			'country_check' => array(
				'title' => __("Country Check", 'woocommerce_barclaycardcw'),
 				'default' => 'inactive',
 				'description' => __("The module can perform a check of the country code provided by the issuer of the card, the IP address country and the billing address country. In case they do not match, the transaction is marked as uncertain. This setting does not override any other rule for marking transaction as uncertain.", 'woocommerce_barclaycardcw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'inactive' => __("Inactive", 'woocommerce_barclaycardcw'),
 					'all' => __("All country codes must match.", 'woocommerce_barclaycardcw'),
 					'ip_country_code_issuer_code' => __("IP country code and issuer country code must match.", 'woocommerce_barclaycardcw'),
 					'ip_country_code_billing_code' => __("IP country and billing country code must match.", 'woocommerce_barclaycardcw'),
 					'issuer_code_billing_code' => __("Issuer country code and billing country code.", 'woocommerce_barclaycardcw'),
 				),
 			),
 			'authorizationMethod' => array(
				'title' => __("Authorization Method", 'woocommerce_barclaycardcw'),
 				'default' => 'PaymentPage',
 				'description' => __("Select the authorization method to use for processing this payment method.", 'woocommerce_barclaycardcw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'PaymentPage' => __("Payment Page", 'woocommerce_barclaycardcw'),
 					'HiddenAuthorization' => __("Hidden Authorization (Alias Gateway)", 'woocommerce_barclaycardcw'),
 				),
 			),
 		); 
	}
	
	public function __construct() {
		$this->icon = apply_filters(
			'woocommerce_barclaycardcw_maestro_icon', 
			BarclaycardCwUtil::getResourcesUrl('icons/maestro.png')
		);
		parent::__construct();
	}
	
	public function createMethodFormFields() {
		$formFields = parent::createMethodFormFields();

		
		
		return array_merge(
			$formFields,
			$this->getMethodSettings()
		);
	}

}