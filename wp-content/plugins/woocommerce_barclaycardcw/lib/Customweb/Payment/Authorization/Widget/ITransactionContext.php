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

require_once 'Customweb/Payment/Authorization/IFrontendTransactionContext.php';
require_once 'Customweb/Payment/Authorization/ITransactionContext.php';


interface Customweb_Payment_Authorization_Widget_ITransactionContext extends  
Customweb_Payment_Authorization_ITransactionContext, 
Customweb_Payment_Authorization_IFrontendTransactionContext {
	
	
	
}