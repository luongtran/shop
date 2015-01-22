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

$base_dir = dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) );
require_once $base_dir . '/wp-load.php';

$_REQUEST = $realRequest;
$_POST = $realPost;
$_GET = $realGet;

BarclaycardCwUtil::includeClass('BarclaycardCw_Transaction');
library_load_class_by_name('Customweb_Util_System');


if (!isset($_REQUEST['cw_transaction_id'])) {
	die("No 'transaction_id' provided.");
}

$dbTransaction = BarclaycardCwUtil::getTransactionByTransactionNumber($_REQUEST['cw_transaction_id'], false);

if ($dbTransaction === null) {
	die("Invalid transaction id provided.");
}

$start = time();
$maxExecutionTime = Customweb_Util_System::getMaxExecutionTime() - 10;

if ($maxExecutionTime > 30) {
	$maxExecutionTime = 30;
}


$order = $dbTransaction->getTransactionObject()->getTransactionContext()->getOrderContext()->getOrderObject();
$method = BarclaycardCwUtil::getPaymentMehtodInstance($dbTransaction->getPaymentClass());
if (method_exists($method, 'get_return_url')) {
	$successUrl = $method->get_return_url($order);
}
else {
	$option = BarclaycardCwUtil::getShopOption('woocommerce_thanks_page_id');
	$checkout_redirect = apply_filters( 'woocommerce_get_checkout_redirect_page_id', $option );
	$successUrl = add_query_arg('key', $order->order_key, add_query_arg('order', $order->id, get_permalink( $checkout_redirect )));
}

// We have to close the session here otherwise the transaction may not be updated by the notification
// callback.
session_write_close();


// Wait as long as the notification is done in the background
while (true) {

	$dbTransaction = BarclaycardCwUtil::getTransactionByTransactionNumber($_REQUEST['cw_transaction_id'], false);

	$transactionObject = $dbTransaction->getTransactionObject();

	$url = null;
	if ($transactionObject->isAuthorizationFailed()) {
		$url = BarclaycardCwUtil::getPluginUrl('payment.php', array('cw_transaction_id' => $_REQUEST['cw_transaction_id']));
	}
	else if ($transactionObject->isAuthorized()) {
		$url = $successUrl;
		if (isset($woocommerce)) {
			$woocommerce->cart->empty_cart();
		}
	}

	if ($url !== null) {
		header('Location: ' . $url);
		die();
	}

	if (time() - $start > $maxExecutionTime) {
		BarclaycardCwUtil::includeTemplateFile('timeout', array('successUrl' => $successUrl));
		die();
	}
	else {
		// Wait 2 seconds for the next try.
		sleep(2);
	}

}