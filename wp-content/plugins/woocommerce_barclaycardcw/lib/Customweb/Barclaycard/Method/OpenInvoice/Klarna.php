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

require_once 'Customweb/Util/Address.php';
require_once 'Customweb/Barclaycard/Method/OpenInvoice/Abstract.php';
require_once 'Customweb/Util/String.php';
require_once 'Customweb/Barclaycard/Util.php';
require_once 'Customweb/Util/Currency.php';
require_once 'Customweb/Payment/Authorization/IInvoiceItem.php';
require_once 'Customweb/Barclaycard/Method/OpenInvoice/Klarna/LineItemBuilder.php';


/**
 * 
 * @author Thomas Hunziker
 * @Method(paymentMethods={'OpenInvoice'}, processors={'Klarna'})
 */
class Customweb_Barclaycard_Method_OpenInvoice_Klarna extends Customweb_Barclaycard_Method_OpenInvoice_Abstract {

	public function getSupportedCountries() {
		return array(
			'DE',
			'DK',
			'FI',
			'NL',
			'NO',
			'SE',	
		);
	}


	public function getFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $authorizationMethod, $isMoto, $customerPaymentContext) {
		$elements = parent::getFormFields($orderContext, $aliasTransaction, $failedTransaction, $authorizationMethod, $isMoto, $customerPaymentContext);
		
		$countryCode = strtoupper($orderContext->getBillingCountryIsoCode());
		if ($countryCode == 'DE' || $countryCode == 'NL') {
			$elements = array_merge(
					$elements, 
					$this->getGenderElements($orderContext, $failedTransaction), 
					$this->getBirthdayElements($orderContext, $failedTransaction)
			);
		}
		else {
			$elements = array_merge(
				$this->getCommericalRegisterNumberElements($orderContext, $failedTransaction),
				$this->getSocialSecurityNumberElement($orderContext, $failedTransaction)
			);
		}
		return array_merge(
				$elements,
				$this->getPhoneNumberElements($orderContext, $failedTransaction)				
		);
	}
	
	public function getAuthorizationParameters(Customweb_Barclaycard_Authorization_Transaction $transaction, array $formData, $authorizationMethod) {
		$parameters = parent::getAuthorizationParameters($transaction, $formData, $authorizationMethod);
	
		$builder = new Customweb_Barclaycard_Method_OpenInvoice_Klarna_LineItemBuilder($transaction->getTransactionContext()->getOrderContext());
		$lineItemParameters = $builder->build();
		
		$orderContext = $transaction->getTransactionContext()->getOrderContext();
		$countryCode = strtoupper($orderContext->getBillingCountryIsoCode());
		if ($countryCode == 'DE' || $countryCode == 'NL') {
			$parameters = array_merge(
					$parameters,
					$this->getCustomerGenderParameter($transaction, $formData),
					$this->getShippingBirthdateParameter($transaction, $formData)
			);
		}
		
		$parameters['ORDERSHIPMETH'] = Customweb_Util_String::substrUtf8($orderContext->getShippingMethod(), 0, 25);
		
		$shippingCostsExcludingTax = 0;
		$taxAmount = 0;
		foreach ($orderContext->getInvoiceItems() as $item) {
			if ($item->getType() === Customweb_Payment_Authorization_IInvoiceItem::TYPE_SHIPPING) {
				$shippingCostsExcludingTax += $item->getAmountExcludingTax();
				$taxAmount += $item->getTaxAmount();
			}
		}
		
		$parameters['ORDERSHIPCOST'] = Customweb_Util_Currency::formatAmount($shippingCostsExcludingTax, $orderContext->getCurrencyCode(), '', '')  ;
		$parameters['ORDERSHIPTAXCODE'] = round($taxAmount / ($shippingCostsExcludingTax + $taxAmount) * 100, 1) . '%';
	
		return array_merge(
				$parameters,
				$this->getBillingAddressParameters($transaction),
				$this->getOwnerPhoneNumberParameters($transaction, $formData),
				$this->getCuidParameters($transaction, $formData),
				$lineItemParameters
		);
	}
	
	
	protected function getBillingAddressParameters(Customweb_Barclaycard_Authorization_Transaction $transaction) {
		$parameters = array();
		$orderContext = $transaction->getTransactionContext()->getOrderContext();
		$splits = Customweb_Util_Address::splitStreet($orderContext->getBillingStreet(), $orderContext->getBillingCountryIsoCode(), $orderContext->getBillingPostCode());
	
		$parameters['ECOM_BILLTO_POSTAL_NAME_FIRST'] = Customweb_Barclaycard_Util::substrUtf8($orderContext->getBillingFirstName(), 0, 50);
		$parameters['ECOM_BILLTO_POSTAL_NAME_LAST'] = Customweb_Barclaycard_Util::substrUtf8($orderContext->getBillingLastName(), 0, 50);
		$parameters['OWNERADDRESS'] = Customweb_Barclaycard_Util::substrUtf8($splits['street'], 0, 35);
		
		$countryCode = strtoupper($orderContext->getBillingCountryIsoCode());
		if ($countryCode == 'DE' || $countryCode == 'NL') {
			$parameters['ECOM_BILLTO_POSTAL_STREET_NUMBER'] = Customweb_Barclaycard_Util::substrUtf8($splits['street-number'], 0, 10);
		}
		$parameters['OWNERZIP'] = Customweb_Barclaycard_Util::substrUtf8($orderContext->getBillingPostCode(), 0, 10);
		$parameters['OWNERTOWN'] = Customweb_Barclaycard_Util::substrUtf8($orderContext->getBillingCity(), 0, 25);
		$parameters['OWNERCTY'] = $orderContext->getBillingCountryIsoCode();
		$parameters['EMAIL'] = Customweb_Barclaycard_Util::substrUtf8($orderContext->getBillingEMailAddress(), 0, 50);
	
		return $parameters;
	}
	
	protected function getCuidParameters(Customweb_Barclaycard_Authorization_Transaction $transaction, array $formData) {
		$orderContext = $transaction->getTransactionContext()->getOrderContext();
		
		$billingCountry = $orderContext->getBillingCountryIsoCode();
		if ($billingCountry == 'DE' || $billingCountry == 'NL') {
			return array();
		}
		
		$billingCompany = $orderContext->getBillingCompanyName();
		if (empty($billingCompany)) {
			$cuid = $this->getSocialSecurityNumber($transaction, $formData);
		}
		else {
			$cuid = $this->getCommericalRegisterNumber($transaction, $formData);
		}
		return array(
			'CUID' => Customweb_Util_String::substrUtf8($cuid, 0, 50),
		);
	}
	
}

