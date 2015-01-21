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

library_load_class_by_name ( 'Customweb_Payment_Endpoint_Dispatcher' );
library_load_class_by_name ( 'Customweb_Core_Http_Response' );

$container = BarclaycardCwUtil::createContainer();
$packages = array(
			0 => 'Customweb_Barclaycard',
 			1 => 'Customweb_Payment_Authorization',
 		);
$adapter = new BarclaycardCw_EndpointAdapter();

$dispatcher = new Customweb_Payment_Endpoint_Dispatcher($adapter, $container, $packages);
$response = $dispatcher->invokeControllerAction(Customweb_Core_Http_ContextRequest::getInstance(), 'process', 'index');
$wrapper = new Customweb_Core_Http_Response($response);
$wrapper->send();
die();