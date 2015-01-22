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

require_once 'Customweb/Payment/Authorization/Method/Sepa/Iban.php';
require_once 'Customweb/Payment/Authorization/Recurring/IAdapter.php';
require_once 'Customweb/Payment/Exception/PaymentErrorException.php';
require_once 'Customweb/Barclaycard/Method/DefaultMethod.php';
require_once 'Customweb/Payment/Authorization/Method/Sepa/Mandate.php';
require_once 'Customweb/Payment/Authorization/ErrorMessage.php';
require_once 'Customweb/I18n/Translation.php';
require_once 'Customweb/Payment/Authorization/Method/Sepa/ElementBuilder.php';


/**
 * 
 * @author Thomas Hunziker
 */
abstract class Customweb_Barclaycard_Method_DirectDebit_Server_Abstract extends Customweb_Barclaycard_Method_DefaultMethod {

	public function getFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $authorizationMethod, $isMoto, $customerPaymentContext) {
		$schema = '{year}-{month}-{day}: {random}';
		if ($this->existsPaymentMethodConfigurationValue('sepa_mandate_id_schema')) {
			$schema = $this->getPaymentMethodConfigurationValue('sepa_mandate_id_schema');
		}
		$mandateId = Customweb_Payment_Authorization_Method_Sepa_Mandate::generateMandateId($schema);
		Customweb_Payment_Authorization_Method_Sepa_Mandate::setMandateIdIntoCustomerContext($customerPaymentContext, $mandateId, $this);
		
		$builder = new Customweb_Payment_Authorization_Method_Sepa_ElementBuilder();
		$builder->setIbanFieldName('IBAN')->setMandateId($mandateId);
	
		return $builder->build();
	}
	
	public function getAuthorizationParameters(Customweb_Barclaycard_Authorization_Transaction $transaction, array $formData, $authorizationMethod) {
		$parameters = parent::getAuthorizationParameters($transaction, $formData, $authorizationMethod);
		if ($authorizationMethod !== Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME) {
			if (!isset($formData['IBAN']) || empty ($formData['IBAN'])) {
				throw new Customweb_Payment_Exception_PaymentErrorException(new Customweb_Payment_Authorization_ErrorMessage(
						Customweb_I18n_Translation::__("No IBAN provided.")
				));
			}
			$iban = $formData['IBAN'];
			$handler = new Customweb_Payment_Authorization_Method_Sepa_Iban();
			$iban = $handler->sanitize($iban);
			$handler->validate($iban);
			
			$mandateId = Customweb_Payment_Authorization_Method_Sepa_Mandate::getMandateIdFromCustomerContext($transaction->getTransactionContext()->getPaymentCustomerContext(), $this);
			// 		Customweb_Payment_Authorization_Method_Sepa_Mandate::resetMandateId($transaction->getTransactionContext()->getPaymentCustomerContext(), $this);
			
			$parameters['CARDNO'] = $iban;
			$parameters['MANDATEID'] = $mandateId;
		}
		return $parameters;
	}
}