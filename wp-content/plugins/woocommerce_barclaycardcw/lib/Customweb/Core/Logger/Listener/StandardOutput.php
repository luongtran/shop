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

require_once 'Customweb/Core/Logger/Factory.php';
require_once 'Customweb/Core/Logger/IListener.php';

/**
 * Listener class to log messages to the standard output.
 * Register an instance with Customweb_Core_Logger_Factory::addListener
 * to receive the logs.
 * 
 * @author Thomas Hunziker
 *
 */
class Customweb_Core_Logger_Listener_StandardOutput implements Customweb_Core_Logger_IListener {
	
	public function addLogEntry($loggerName, $level, $message, Exception $e = null) {
		echo '[' . $level . '] ' . $loggerName . ': ' . $message . "\n";
		if ($e !== null) {
			echo $e->getMessage();
			echo "\n";
			echo $e->getTraceAsString();
			echo "\n\n";
		}
	}

}