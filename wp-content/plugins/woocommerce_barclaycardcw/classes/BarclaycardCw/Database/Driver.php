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
require_once 'Customweb/Database/Driver/AbstractDriver.php';
require_once 'BarclaycardCw/Database/Statement.php';

final class BarclaycardCw_Database_Driver extends Customweb_Database_Driver_AbstractDriver {
	
	/**
	 * @var wpdb
	 */
	private $link;
	
	/**
	 * The resource link is the connection link to the database.
	 *
	 * @param resource $resourceLink
	 */
	public function __construct(wpdb $wpdb){
		$this->link = $wpdb;
	}
	
	public function beginTransaction(){
		$this->query("START TRANSACTION;");
		$this->setTransactionRunning(true);
	}
	
	public function commit(){
		$this->query("COMMIT;");
		$this->setTransactionRunning(false);
	}
	
	public function rollBack(){
		$this->query("ROLLBACK;");
		$this->setTransactionRunning(false);
	}
	
	public function query($query){
		$statement = new BarclaycardCw_Database_Statement($query, $this);
		return $statement;
	}
	
	public function quote($string){
		if (method_exists($this->link, '_real_escape')) {
			$string = $this->link->_real_escape($string);
		}
		else {
			$string = $this->link->escape($string);
		}
	
		return '"' . addslashes($string) . '"';
	}
	
	public function getLink(){
		return $this->link;
	}
	
}