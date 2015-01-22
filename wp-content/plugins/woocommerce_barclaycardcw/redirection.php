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


if (!isset($_REQUEST['cw_transaction_id'])) {
	die("No 'transaction_id' provided.");
}

$dbTransaction = BarclaycardCwUtil::getTransactionByTransactionNumber($_REQUEST['cw_transaction_id']);
if ($dbTransaction->getTransactionObject() == null) {
	die("Not a valid transaction provided.");
}


$paymentModule = BarclaycardCwUtil::getPaymentMehtodInstance($dbTransaction->getPaymentClass());

if ($paymentModule === NULL) {
	die("Could not load payment module. May be the module id is not set.");
}

$authorizationAdapter = BarclaycardCwUtil::getAuthorizationAdapter($dbTransaction->getTransactionObject()->getAuthorizationMethod());

if (!($authorizationAdapter instanceof Customweb_Payment_Authorization_PaymentPage_IAdapter)) {
	throw new Exception("Only supported for payment page authorization.");
}

$headerRedirection = $authorizationAdapter->isHeaderRedirectionSupported($dbTransaction->getTransactionObject(), $_REQUEST);

if ($headerRedirection) {
	$url = $authorizationAdapter->getRedirectionUrl($dbTransaction->getTransactionObject(), $_REQUEST);
	BarclaycardCwUtil::getEntityManager()->persist($dbTransaction);
	header('Location: ' . $url);
	die();
}
else {
	$variables = array(
		'paymentMethodName' => $dbTransaction->getTransactionObject()->getPaymentMethod()->getPaymentMethodDisplayName(),
		'form_target_url' => $authorizationAdapter->getFormActionUrl($dbTransaction->getTransactionObject(), $_REQUEST),
		'hidden_fields' => $authorizationAdapter->getParameters($dbTransaction->getTransactionObject(), $_REQUEST),
	);
	BarclaycardCwUtil::getEntityManager()->persist($dbTransaction);
	BarclaycardCwUtil::includeTemplateFile('redirection', $variables);
}
