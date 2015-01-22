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

require_once 'Customweb/Barclaycard/IAdapter.php';
require_once 'Customweb/Payment/Endpoint/Annotation/ExtractionMethod.php';
require_once 'Customweb/Core/Http/Response.php';
require_once 'Customweb/Payment/Endpoint/Controller/Process.php';
require_once 'Customweb/Barclaycard/Util.php';
require_once 'Customweb/I18n/Translation.php';



/**
 *
 * @author Thomas Hunziker
 * @Controller("process")
 *
 */
class Customweb_Barclaycard_Endpoint_Process extends Customweb_Payment_Endpoint_Controller_Process {

	/**
	 *
	 * @param Customweb_Core_Http_IRequest $request @ExtractionMethod
	 */
	public function getTransactionIdentifier(Customweb_Core_Http_IRequest $request){
		$parameters = $request->getParameters();
		
		if (isset($parameters['cwTransId'])) {
			return array(
				'id' => $parameters['cwTransId'],
				'key' => Customweb_Payment_Endpoint_Annotation_ExtractionMethod::EXTERNAL_TRANSACTION_ID_KEY
			);
		}
		if (isset($parameters['cw_transaction_id'])) {
			return array(
				'id' => $parameters['cw_transaction_id'],
				'key' => Customweb_Payment_Endpoint_Annotation_ExtractionMethod::EXTERNAL_TRANSACTION_ID_KEY 
			);
		}
		if (isset($parameters['PAYID'])) {
			return array(
				'id' => $parameters['PAYID'],
				'key' => Customweb_Payment_Endpoint_Annotation_ExtractionMethod::PAYMENT_ID_KEY 
			);
		}
		
		throw new Exception("No transaction identifier present in the request.");
	}

	/**
	 * @Action("update")
	 */
	public function update(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request){
		if (! $transaction->isAuthorized() && ! $transaction->isAuthorizationFailed()) {
			return $this->process($transaction, $request);
		}
		if ($transaction->getStatusAfterReceivingUpdate() !== null) {
			//We already handled an update successfully
			return new Customweb_Core_Http_Response();
		}
		$responseParameters = $request->getParameters();
		$parameters = $transaction->getAuthorizationParameters();
		$config = $this->getContainer()->getBean('Customweb_Barclaycard_Configuration');
		
		$hash = Customweb_Barclaycard_Util::calculateHash($responseParameters, 'OUT', $config);
		
		if (isset($responseParameters['SHASIGN']) && $responseParameters['SHASIGN'] == $hash) {
			if ($parameters['INITIALSTATUS'] == Customweb_Barclaycard_IAdapter::STATUS_PAYMENT_UNCERTAIN || 
					$parameters['INITIALSTATUS'] == Customweb_Barclaycard_IAdapter::STATUS_AUTHORISED_NOT_KNOWN || 
					$parameters['INITIALSTATUS'] == Customweb_Barclaycard_IAdapter::STATUS_AUTHORISED_WAITING || 
					$parameters['INITIALSTATUS'] == Customweb_Barclaycard_IAdapter::STATUS_WAITING_FOR_CLIENT_PAYMENT) {
						
				$this->processNewStatus($transaction, $responseParameters, $parameters['INITIALSTATUS']);
			}
		}
		return new Customweb_Core_Http_Response();
	}

	private function processNewStatus(Customweb_Barclaycard_Authorization_Transaction $transaction, $parameters, $initial){
		switch ($parameters['STATUS']) {
			
			case Customweb_Barclaycard_IAdapter::STATUS_AUTHORISED:
				if (isset($parameters['ACCEPTANCE']) && $parameters['ACCEPTANCE'] != '') {
					$transaction->appendAuthorizationParameters(array(
						'ACCEPTANCE' => $parameters['ACCEPTANCE'] 
					));
				}
				$transaction->setStatusAfterReceivingUpdate('success');
				break;
			
			case Customweb_Barclaycard_IAdapter::STATUS_AUTHORISATION_REFUSED:
			case Customweb_Barclaycard_IAdapter::STATUS_PAYMENT_REFUSED:
				$transaction->setStatusAfterReceivingUpdate('refused');
				$transaction->setAuthorizationUncertain(false);
				break;
			case Customweb_Barclaycard_IAdapter::STATUS_CANCELED:
				if ($initial == Customweb_Barclaycard_IAdapter::STATUS_WAITING_FOR_CLIENT_PAYMENT) {
					if ($transaction->isCancelPossible()) {
						try {
							$transaction->cancel('Customer failed to pay');
							$container = $this->getContainer();
							if ($container->hasBean('Customweb_Payment_BackendOperation_Adapter_Shop_ICancel')) {
								$cancelAdapter = $container->getBean('Customweb_Payment_BackendOperation_Adapter_Shop_ICancel');
								$cancelAdapter->cancel($transaction);
							}
						}
						catch (Exception $e) {
							$transaction->createHistoryItem(Customweb_I18n_Translation::__('Failure during cancellation of the transaction. (Cancellation reason: Customer did not pay'), 'update');
						}
					}
				}
				break;
			case Customweb_Barclaycard_IAdapter::STATUS_PAYMENT_PROCESSED_MERCHANT:
			case Customweb_Barclaycard_IAdapter::STATUS_PAYMENT_REQUESTED:
				if (isset($parameters['ACCEPTANCE']) && $parameters['ACCEPTANCE'] != '') {
					$transaction->appendAuthorizationParameters(array(
						'ACCEPTANCE' => $parameters['ACCEPTANCE'] 
					));
				}
				if ($transaction->isCapturePossible()) {
					$transaction->capture();
					$container = $this->getContainer();
					if ($container->hasBean('Customweb_Payment_BackendOperation_Adapter_Shop_ICapture')) {
						$captureAdapter = $container->getBean('Customweb_Payment_BackendOperation_Adapter_Shop_ICapture');
						$captureAdapter->capture($transaction);
					}
				}
				$transaction->setStatusAfterReceivingUpdate('success');
				$transaction->setAuthorizationUncertain(false);
				break;
			default:
				$transaction->createHistoryItem(Customweb_I18n_Translation::__('Received update notification with unexcpected status: !status', array(
					'!status' => $parameters['STATUS'] 
				)), 'update');
				break;
		}
	}
}