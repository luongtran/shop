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

require_once 'Customweb/Barclaycard/Configuration.php';
require_once 'Customweb/Barclaycard/IAdapter.php';
require_once 'Customweb/Barclaycard/Util.php';
require_once 'Customweb/I18n/Translation.php';


abstract class Customweb_Barclaycard_AbstractAdapter implements Customweb_Barclaycard_IAdapter {

	/**
	 * @var Customweb_DependencyInjection_IContainer
	 */
	private $container = null;
	
	/**
	 * Configuration object.
	 *
	 * @var Customweb_Barclaycard_Configuration
	 */
	private $configuration;
	
	public function __construct(Customweb_Payment_IConfigurationAdapter $configurationAdapter, Customweb_DependencyInjection_IContainer $container) {
		$this->configuration = new Customweb_Barclaycard_Configuration($configurationAdapter);
		$this->container = $container;
		
	}
	
	/**
	 * Returns the configuration object.
	 *
	 * @return Customweb_Barclaycard_Configuration
	 */
	public function getConfiguration() {
		return $this->configuration;
	}

	/**
	 * @return Customweb_DependencyInjection_IContainer
	 */
	public function getContainer() {
		return $this->container;
	}
	
	public function isTestMode() {
		return $this->getConfiguration()->isTestMode();
	}

	/**
	 * This method returns the base URL of OgoneDemo.
	 *          	  				   		
	 * @return The base URL without any specifict intention.
	 */
	protected final function getBaseUrl() {
		return $this->getConfiguration()->getBaseEndPointUrl();
	}

	/**
	 * @return Customweb_Barclaycard_Method_Factory
	 */
	public function getPaymentMethodFactory() {
		return $this->getContainer()->getBean('Customweb_Barclaycard_Method_Factory');
	}
	
	public function getPaymentMethodByTransaction(Customweb_Barclaycard_Authorization_Transaction $transaction){
		return $this->getMethodFactory()->getPaymentMethod($transaction->getTransactionContext()->getOrderContext()->getPaymentMethod(), $transaction->getAuthorizationMethod());
	}
	
	/**
	 * @return Customweb_Payment_Endpoint_IAdapter
	 */
	protected function getEndpointAdapter() {
		return $this->getContainer()->getBean('Customweb_Payment_Endpoint_IAdapter');
	}
	
	
	/**
	 * This method validates an incomming notification request from the payment service provider.
	 *
	 * @param array $responseParameters Key/value map of the parameter retunred by Barclaycard.
	 * @return boolean Whether the notification is valid and not manipulated or not. True means it is valid.
	 */
	protected function validateResponse(array $responseParameters) {
		
		if ((isset($responseParameters['SHASIGN']) && $responseParameters['SHASIGN'] == $this->calculateHashOut($responseParameters))
				) {  // && (isset($responseParameters['COMPLUS']) && true /* check here the response */ )
			return true;
		}
		else {
			return false;
		}
	}

	protected function sendMaintenanceRequest($parameters) {
		return $this->sendDirectRequest($this->getMaintenanceUrl(), $parameters);
	}
	
	protected function sendDirectRequest($url, $parameters) {
		$response = Customweb_Barclaycard_Util::sendRequest($url, $parameters);
		$responseParameters = Customweb_Barclaycard_Util::getXmlAttributes($response);
		
		// The request can not be validated, because the answer of Barclaycard does not contain the
		// sha signature.
		if (!is_array($responseParameters) || count($responseParameters) <= 0) {
			throw new Exception(Customweb_I18n_Translation::__(
					'The server response was not valid. Response: @response',
					array('@response' => $response)
			));
		}
	
		return $responseParameters;
	}

	public final function calculateHashIn($parameters) {
		return Customweb_Barclaycard_Util::calculateHash($parameters, 'IN', $this->getConfiguration());
	}

	public final function calculateHashOut($parameters) {
		return Customweb_Barclaycard_Util::calculateHash($parameters, 'OUT', $this->getConfiguration());
	}

	protected function getPaymentPageUrl() {
		return $this->getBaseUrl() . self::URL_PAYMENT_PAGE;
	}

	protected function getDirectOrderUrl() {
		return $this->getBaseUrl() . self::URL_DIRECT_ORDER;
	}

	protected function getMaintenanceUrl() {
		return $this->getBaseUrl() . self::URL_MAINTENANCE;
	}

	protected function getHiddenAuthorizationUrl() {
		return $this->getBaseUrl() . self::URL_ALIAS_GATEWAY;
	}
	
	protected function setTransactionAuthorizationState(Customweb_Barclaycard_Authorization_Transaction $transaction, $parameters) {
		
		$authorizationParameters = $transaction->getAuthorizationParameters();

		$transaction->appendAuthorizationParameters(array('INITIALSTATUS' => $parameters['STATUS']));
		$transaction->setPaymentId($parameters['PAYID']); 
	
		switch($parameters['STATUS']) {
			case Customweb_Barclaycard_IAdapter::STATUS_PAYMENT_PROCESSING:
			case Customweb_Barclaycard_IAdapter::STATUS_PAYMENT_REQUESTED:
			case Customweb_Barclaycard_IAdapter::STATUS_PAYMENT_PROCESSED_MERCHANT:
				$transaction->authorize()->capture();
				break;
	
			case Customweb_Barclaycard_IAdapter::STATUS_AUTHORISED:
				$transaction->authorize();
				break;
	
			case Customweb_Barclaycard_IAdapter::STATUS_WAITING_FOR_CLIENT_PAYMENT:
				$transaction->authorize(Customweb_I18n_Translation::__('Waiting for client payment'));
				break;
					
			case Customweb_Barclaycard_IAdapter::STATUS_ORDER_STORED:
				$transaction
				->authorize(Customweb_I18n_Translation::__('Order is stored, but not finally authorized or captured.'))
				->setAuthorizationUncertain();
				break;
	
			case Customweb_Barclaycard_IAdapter::STATUS_STORED_WAITING_EXTERNAL_RESULT:
			case Customweb_Barclaycard_IAdapter::STATUS_AUTHORISED_WAITING_EXTERNAL_RESULT:
				$transaction
				->authorize(Customweb_I18n_Translation::__('The authorization could not be completed, due to a delayed external validation of the payment.'))
				->setAuthorizationUncertain();
				break;
					
			case Customweb_Barclaycard_IAdapter::STATUS_AUTHORISED_NOT_KNOWN:
				$transaction
				->authorize(Customweb_I18n_Translation::__('The result of the authorization is not known.'))
				->setAuthorizationUncertain();
				break;
	
			case Customweb_Barclaycard_IAdapter::STATUS_PAYMENT_UNCERTAIN:
				$transaction
				->authorize(Customweb_I18n_Translation::__('The payment could not be authorized, because it is not certain.'))
				->setAuthorizationUncertain();
				break;
					
			case Customweb_Barclaycard_IAdapter::STATUS_PAYMENT_IN_PROGRESS:
				$transaction
				->authorize(Customweb_I18n_Translation::__('The payment is not finished. Hence it must be manually checked.'))
				->setAuthorizationUncertain();
				break;
					
			case Customweb_Barclaycard_IAdapter::STATUS_AUTHORISED_WAITING:
				$transaction
				->authorize(Customweb_I18n_Translation::__('The acquiring system is not available. Hence the authorization is not completed.'))
				->setAuthorizationUncertain();
				break;
					
			case Customweb_Barclaycard_IAdapter::STATUS_PAYMENT_REFUSED:
				$reason = Customweb_I18n_Translation::__('The payment is refused.');
				$transaction->setAuthorizationFailed($reason);
				break;
					
			case Customweb_Barclaycard_IAdapter::STATUS_PAYMENT_DECLINED_ACQUIRER:
				$reason = Customweb_I18n_Translation::__('The authorization declined by the aquirer.');
				$transaction->setAuthorizationFailed($reason);
				break;
	
			case Customweb_Barclaycard_IAdapter::STATUS_AUTHORISATION_REFUSED:
				$reason = Customweb_I18n_Translation::__('The authorization is refused.');
				$transaction->setAuthorizationFailed($reason);
				break;
	
			case Customweb_Barclaycard_IAdapter::STATUS_CANCELED_BY_CUSTOMER:
				$reason = Customweb_I18n_Translation::__('The transaction is cancelled.');
				$transaction->setAuthorizationFailed($reason);
				break;
	
			case Customweb_Barclaycard_IAdapter::STATUS_INVALID:
			default:
				$reason = Customweb_I18n_Translation::__('The transaction failed due to a unkown reason.');
				if (isset($parameters['NCERROR']) && !empty($parameters['NCERROR'])) {
					$reason = $parameters['NCERROR'];
				}
				if (isset($parameters['NCERRORPLUS'])) {
					$reason .= ': ' . $parameters['NCERRORPLUS'];
				}
				$transaction->setAuthorizationFailed($reason);
				break;
		}
		
		// If the transaction is authorized, ensure the card is masked
		if ($transaction->isAuthorized()) {
			// Set the display name for the alias and mark this transaction as a potential alias.
			if (isset($authorizationParameters['ALIAS']) && $transaction->getTransactionContext()->getAlias() !== null) {
				if (isset($authorizationParameters['CARDNO']) && !empty($authorizationParameters['CARDNO'])) {
					$displayName = $authorizationParameters['CARDNO'];
				}
				else {
					$displayName = $authorizationParameters['ALIAS'];
				}
				if (isset($authorizationParameters['ED'])) {
					$displayName .= ' (' . substr($authorizationParameters['ED'], 0, 2) . '/' . substr($authorizationParameters['ED'], 2, 4) . ')';
				}
				$transaction->setAliasForDisplay($displayName);
			}
			
			$params = $transaction->getDirectLinkCreationParameters();
			if (isset($params['CARDNO'])) {
				$cardNumber = $params['CARDNO'];
				$cleanedNumber = preg_replace('/[^0-9]*/', '', $cardNumber);
				if (strlen($cleanedNumber) > 4) {
					$params['CARDNO'] = str_repeat("X", 12) . substr($cleanedNumber, -4, 4);
					$transaction->setDirectLinkCreationParameters($params);
				}
			}
		}
		$this->handleExtendedFraud($transaction, $parameters);
		
		// In any case when we pass this code, we do not need to update the transaction.
		$transaction->setUpdateExecutionDate(null);
	}
	
	protected function handleExtendedFraud(Customweb_Barclaycard_Authorization_Transaction $transaction, $parameters) {
		
		if ($transaction->isAuthorized()) {
			$paymentMethod = $transaction->getPaymentMethod();
			
			if ($paymentMethod->existsPaymentMethodConfigurationValue('refusing_threshold')) {
				$threshold = $paymentMethod->getPaymentMethodConfigurationValue('refusing_threshold');
				if (count($transaction->getPreviousFailedTransactionIds()) >= $threshold) {
					$transaction->setAuthorizationUncertain(Customweb_I18n_Translation::__("More failed transaction as the configured threshold of failed transactions."));
				}
				
			}
			
			// We check the transactions only with additional 
			if (!isset($parameters['ECI']) || $parameters['ECI'] !== '5') {
				if ($paymentMethod->existsPaymentMethodConfigurationValue('three_d_secure_behavior') && isset($parameters['CCCTY'])) {
					$behavior = strtolower($paymentMethod->getPaymentMethodConfigurationValue('three_d_secure_behavior'));
				
					$countryList = strtoupper($paymentMethod->getPaymentMethodConfigurationValue('three_d_secure_country_list'));
					$countryList = preg_split('/[,;:]+/', $countryList);
				
					$isInList = false;
					if (in_array(strtoupper($parameters['CCCTY']), $countryList)) {
						$isInList = true;
					}
				
					if ($behavior == 'always' && !$isInList) {
						$transaction->setAuthorizationUncertain(Customweb_I18n_Translation::__("Card issue country was on the white list and hence the transaction is marked as uncertain."));
					}
					else if ($behavior == 'never' && $isInList) {
						$transaction->setAuthorizationUncertain(Customweb_I18n_Translation::__("Card issue country was on the black list and hence the transaction is marked as uncertain."));
					}
				}
			}

			if ($paymentMethod->existsPaymentMethodConfigurationValue('country_check')) {
				$issuerCountry = null;
				$billingCountry = strtoupper($transaction->getTransactionContext()->getOrderContext()->getBillingCountryIsoCode());
				$ipCountry = null;
				if (isset($parameters['CCCTY'])) {
					$issuerCountry = strtoupper($parameters['CCCTY']);
				}
					
				if (isset($parameters['IPCTY'])) {
					$ipCountry = strtoupper($parameters['IPCTY']);
				}
					
				switch(strtolower($paymentMethod->getPaymentMethodConfigurationValue('country_check'))) {
					case 'all':
						if ($issuerCountry !== null && $ipCountry !== null) {
							if ($issuerCountry != $billingCountry) {
								$transaction->setAuthorizationUncertain(Customweb_I18n_Translation::__("Card issuer country does not match with the billing country."));
							}
							else if ( $issuerCountry != $ipCountry) {
								$transaction->setAuthorizationUncertain(Customweb_I18n_Translation::__("Card issuer country does not match with the IP address country."));
							}
							else if ($ipCountry != $billingCountry) {
								$transaction->setAuthorizationUncertain(Customweb_I18n_Translation::__("Card IP address country does not match with the billing country."));
							}
						}
						break;
					case 'ip_country_code_issuer_code':
						if ( $issuerCountry != $ipCountry) {
							$transaction->setAuthorizationUncertain(Customweb_I18n_Translation::__("Card issuer country does not match with the IP address country."));
						}
						break;
					case 'ip_country_code_billing_code':
						if ($ipCountry != $billingCountry) {
							$transaction->setAuthorizationUncertain(Customweb_I18n_Translation::__("Card IP address country does not match with the billing country."));
						}
						break;
					case 'issuer_code_billing_code':
						if ($issuerCountry != $billingCountry) {
							$transaction->setAuthorizationUncertain(Customweb_I18n_Translation::__("Card issuer country does not match with the billing country."));
						}
						break;
				}
			}
			
		}
		
	}
	
	
}