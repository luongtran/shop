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

$base_dir = dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) );
require_once $base_dir . '/wp-load.php';

BarclaycardCwUtil::includeClass('BarclaycardCw_Transaction');


if (!isset($_REQUEST['cw_transaction_id'])) {
	die("No transaction_id provided.");
}

$dbTransaction = BarclaycardCwUtil::getTransactionByTransactionNumber($_REQUEST['cw_transaction_id']);

$redirectionUrl = '';
if ($dbTransaction->getTransactionObject()->isAuthorizationFailed()) {
	$redirectionUrl = Customweb_Util_Url::appendParameters(
		$dbTransaction->getTransactionObject()->getTransactionContext()->getFailedUrl(),
		$dbTransaction->getTransactionObject()->getTransactionContext()->getCustomParameters()
	);
}
else {
	$redirectionUrl = Customweb_Util_Url::appendParameters(
		$dbTransaction->getTransactionObject()->getTransactionContext()->getSuccessUrl(),
		$dbTransaction->getTransactionObject()->getTransactionContext()->getCustomParameters()
	);
}


$vars = array();
$vars['url'] = $redirectionUrl;
BarclaycardCwUtil::includeTemplateFile('iframe_break_out', $vars);

