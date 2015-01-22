<?php
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
// Force language for WPML
if (isset($_GET['wpml-lang'])) {
	$_SERVER['REQUEST_URI'] = str_replace('wp-content', $_GET['wpml-lang'] . '/wp-content', $_SERVER['REQUEST_URI']);
	if (!isset($_GET['lang'])) {
		$_GET['lang'] = $_GET['wpml-lang'];
	}
}

$realRequest = $_REQUEST;
$realPost = $_POST;
$realGet = $_GET;

$base_dir = dirname(dirname(dirname(dirname(__FILE__))));
require_once $base_dir . '/wp-load.php';

$_REQUEST = $realRequest;
$_POST = $realPost;
$_GET = $realGet;

BarclaycardCwUtil::includeClass('BarclaycardCw_Transaction');

if (isset($GLOBALS['woocommerce'])) {
	if (method_exists($GLOBALS['woocommerce'], 'frontend_scripts')) {
		$GLOBALS['woocommerce']->frontend_scripts();
	}
}

$aliasTransactionId = NULL;
$failedTransactionId = NULL;
if (isset($_REQUEST['cw_transaction_id'])) {
	$failedTransactionExternalId = $_REQUEST['cw_transaction_id'];
	$failedTransaction = BarclaycardCwUtil::getTransactionByTransactionNumber($failedTransactionExternalId);
	if (!$failedTransaction->getTransactionObject()->isAuthorizationFailed()) {
		$option = BarclaycardCwUtil::getShopOption('woocommerce_checkout_page_id');
		header('Location: ' . get_permalink($option));
		die();
	}
	$failedTransactionId = $failedTransaction->getTransactionId();
	$adapter = BarclaycardCwUtil::getAuthorizationAdapter($failedTransaction->getTransactionObject()->getAuthorizationMethod());
	if (($adapter instanceof Customweb_Payment_Authorization_PaymentPage_IAdapter) ||
			 ($adapter instanceof Customweb_Payment_Authorization_Iframe_IAdapter) ||
			 ($adapter instanceof Customweb_Payment_Authorization_Widget_IAdapter)) {
		$option = BarclaycardCwUtil::getShopOption('woocommerce_checkout_page_id');
		header(
				'Location: ' . Customweb_Util_Url::appendParameters(get_permalink($option), 
						array(
							'barclaycardcw_failed_transaction_id' => $failedTransactionId 
						)));
		die();
	}
	
	$orderId = $failedTransaction->getOrderId();
	$paymentMethodClass = $failedTransaction->getPaymentClass();
}
else {
	if (!isset($_REQUEST['order_id'])) {
		die("No order_id provided.");
	}
	$orderId = $_REQUEST['order_id'];
	
	if (!isset($_REQUEST['payment_method_class'])) {
		die("No payment_method_class provided.");
	}
	$paymentMethodClass = $_REQUEST['payment_method_class'];
	
	if (isset($_REQUEST['alias_transaction_id'])) {
		$aliasTransactionId = $_REQUEST['alias_transaction_id'];
	}
}

$paymentMethod = BarclaycardCwUtil::getPaymentMehtodInstance(strip_tags($paymentMethodClass));
$vars = array();

ob_start();
$response = $paymentMethod->getPaymentForm($orderId, $aliasTransactionId, $failedTransactionId, false);
$vars['form'] = ob_get_contents();
ob_end_clean();

if (is_array($response) && isset($response['redirect'])) {
	header('Location: ' . $response['redirect']);
	die();
}

BarclaycardCwUtil::includeTemplateFile('payment', $vars);
