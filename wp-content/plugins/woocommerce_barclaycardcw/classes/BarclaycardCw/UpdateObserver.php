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



/**
 *
* @author Nico Eigenmann
*
*/
class BarclaycardCw_UpdateObserver {

	
	/**
	 * This method must be called by a cron task. The call can be done by anyone!
	 */
	public static function run() {
		library_load_class_by_name('Customweb_Payment_Update_ScheduledProcessor');
		library_load_class_by_name('Customweb_Cron_Processor');
		
		try {
			$packages = array(
			0 => 'Customweb_Barclaycard',
 			1 => 'Customweb_Payment_Authorization',
 		);
			$packages[] = 'Customweb_Payment_Update_ScheduledProcessor';
			$cronProcessor = new Customweb_Cron_Processor(BarclaycardCwUtil::createContainer(), $packages);
			$cronProcessor->run();
		} catch (Exception $e) {
			//If wordpress provides nice logging function add it here	
		}
	}
	
	
}