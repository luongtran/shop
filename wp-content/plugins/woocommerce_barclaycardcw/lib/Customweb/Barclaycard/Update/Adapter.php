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

require_once 'Customweb/Barclaycard/Update/PullRequest.php';
require_once 'Customweb/Payment/Update/Util.php';
require_once 'Customweb/Payment/Update/IAdapter.php';
require_once 'Customweb/Barclaycard/AbstractAdapter.php';


/**
 * 
 * @author Thomas Hunziker
 * @Bean
 *
 */
class Customweb_Barclaycard_Update_Adapter extends Customweb_Barclaycard_AbstractAdapter
implements Customweb_Payment_Update_IAdapter
{
	/**
	 * @var numeric
	 */
	// TODO: Make a configuration for timeout
	const TRANSACTION_TIMEOUT = 7200;
	
	// TODO: Make a configuration for the interval
	const TRANSACTION_UPDATE_INTERVAL = 600;
	
	
	public function updateTransaction(Customweb_Payment_Authorization_ITransaction $transaction) {
		/* @var $transaction Customweb_Barclaycard_Authorization_Transaction */
		if ($this->getConfiguration()->isTransactionUpdateActive() && !$transaction->isAuthorizationFailed()) {
			
			
			
			$request = new Customweb_Barclaycard_Update_PullRequest($transaction, $this->getContainer());
			$responseParameters = $request->pull();
			$responseParameters = array_change_key_case($responseParameters, CASE_UPPER);
			
			if (!isset($responseParameters['STATUS']) || $responseParameters['STATUS'] == '88') {
				throw new Exception("The status update of the transaction failed.");
			}
			// In case of error '50001130' we do not get any order, means the customer has not finished yet the order.
			if (isset($responseParameters['NCERROR']) && $responseParameters['NCERROR'] != '50001130') {
				$this->setTransactionAuthorizationState($transaction, $responseParameters);
				$transaction->setAuthorizationParameters($responseParameters);
			}
			
			
			Customweb_Payment_Update_Util::handlePendingTransaction($transaction, self::TRANSACTION_TIMEOUT, self::TRANSACTION_UPDATE_INTERVAL);
		}
		
	}
}