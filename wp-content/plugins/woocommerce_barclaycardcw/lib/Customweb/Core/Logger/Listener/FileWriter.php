2<?php

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

require_once 'Customweb/Core/Logger/Factory.php';
require_once 'Customweb/Core/Logger/Exceptions/FileWriteException.php';
require_once 'Customweb/Core/Logger/IListener.php';

/**
 * Listener class to write messages to a given file.
 * Register an instance with Customweb_Core_Logger_Factory::addListener
 * to receive the logs.
 *
 * @author Bjoern Hasselmann
 *
 */
class Customweb_Core_Logger_Listener_FileWriter implements Customweb_Core_Logger_IListener {
	/**
	 * 
	 * @var string
	 */
	private $fileName;
	
	/**
	 * 
	 * @param string $fileName
	 */
	public function __construct($fileName){
		$this->fileName = $fileName;
	}
	/**
	 * @see Customweb_Core_Logger_IListener::addLogEntry()
	 * @throws Customweb_Core_Logger_Exceptions_FileWriteException In case content could not be written to file
	 */
	public function addLogEntry($loggerName, $level, $message, Exception $e = null){
		$content = '[' . $level . '] ' . $loggerName . ': ' . $message . "\n";
		if ($e !== null) {
			$content .= $e->getMessage();
			$content .=  "\n";
			$content .=  $e->getTraceAsString();
			$content .=  "\n\n";
		}
		$result = file_put_contents($this->fileName, $content, FILE_APPEND);
		if($result === false){
			throw new Customweb_Core_Logger_Exceptions_FileWriteException($this->fileName);
		}
	}

	public function getFileName(){
		return $this->fileName;
	}

	public function setFileName($fileName){
		$this->fileName = $fileName;
		return $this;
	}
	
	
}