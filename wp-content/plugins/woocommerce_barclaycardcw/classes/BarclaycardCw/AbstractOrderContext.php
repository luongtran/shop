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

library_load_class_by_name('Customweb_Payment_Authorization_DefaultInvoiceItem');
library_load_class_by_name('Customweb_Payment_Authorization_OrderContext_AbstractDeprecated');
library_load_class_by_name('Customweb_Util_Invoice');
library_load_class_by_name('Customweb_Core_Language');
library_load_class_by_name('Customweb_Core_Util_Rand');

abstract class BarclaycardCw_AbstractOrderContext extends  Customweb_Payment_Authorization_OrderContext_AbstractDeprecated
{
	protected $order;
	protected $orderAmount;
	protected $currencyCode;
	protected $paymentMethod;
	protected $language;
	protected $userId;
	
	protected $checkoutId;
	
	public function __construct($order, Customweb_Payment_Authorization_IPaymentMethod $paymentMethod, $userId = null) {
		
		if ($order == null) {
			throw new Exception("The order parameter cannot be null.");
		}
		
		if (!isset($_SESSION['BarclaycardCw']['checkoutId'])) {
			if (!isset($_SESSION['BarclaycardCw'])) {
				$_SESSION['BarclaycardCw'] = array();
			}
			$_SESSION['BarclaycardCw']['checkoutId'] = Customweb_Core_Util_Rand::getUuid();
		}
		$this->checkoutId = $_SESSION['BarclaycardCw']['checkoutId'];
		
		if (function_exists('get_woocommerce_currency')) {
			$this->currencyCode = get_woocommerce_currency();
		}
		else {
			$this->currencyCode = Jigoshop_Base::get_options()->get_option('jigoshop_currency');
		}
		$this->order = $order;
		$this->paymentMethod = $paymentMethod;
		if(method_exists($order, 'get_total')){
			$this->orderAmount = $order->get_total();
		}
		else if (method_exists($order, 'get_order_total')) {
			$this->orderAmount = $order->get_order_total();
		}
		else {
			$this->orderAmount = $order->order_total;
		}
		
		$this->language = get_bloginfo('language');
		/* 
		if (defined('ICL_LANGUAGE_CODE')) {
			$lang = ICL_LANGUAGE_CODE;
			if(!empty($lang)) {
				$this->language = ICL_LANGUAGE_CODE;
			}
		}
		*/
		if ($userId === null) {
			$this->userId = get_current_user_id();
		}
		else {
			$this->userId = $userId;
		}
		
		if ($this->userId === null) {
			$this->userId = 0;
		}
	}
	
	public function getCustomerId() {
		return $this->userId;
	}
	
	public function isNewCustomer() {
		return 'unkown';
	}
	
	public function getCustomerRegistrationDate() {
		return null;
	}
	
	public function getOrderObject() {
		return $this->order;
	}
	
	public function getOrderAmountInDecimals() {
		return $this->orderAmount;
	}
	
	public function getCurrencyCode() {
		return $this->currencyCode;
	}
	
	public function getPaymentMethod() {
		return $this->paymentMethod;
	}
	
	public function getLanguage() {
		return new Customweb_Core_Language($this->language);
	}
	
	public function getCustomerEMailAddress() {
		return $this->getBillingEMailAddress();
	}
	
	public function getBillingEMailAddress() {
		return $this->order->billing_email;
	}
	
	public function getBillingGender() {
		$billingCompany = trim($this->getBillingCompanyName());
		if (!empty($billingCompany)) {
			return 'company';
		}
		else {
			return null;
		}
	}
	
	public function getBillingSalutation() {
		return null;
	}
	
	public function getBillingFirstName() {
		return $this->order->billing_first_name;
	}
	
	public function getBillingLastName() {
		return $this->order->billing_last_name;
	}
	
	public function getBillingStreet() {
		return $this->order->billing_address_1 . " " . $this->order->billing_address_2;
	}
	
	public function getBillingCity() {
		return $this->order->billing_city;
	}
	
	public function getBillingPostCode() {
		return $this->order->billing_postcode;
	}
	
	public function getBillingState() {
		if (isset($this->order->billing_state)) {
			$state = $this->order->billing_state;
			if (!empty($state) && strlen($state) == 2) {
				return $state;
			}
		}
		
		return null;
	}
	
	public function getBillingCountryIsoCode() {
		return $this->order->billing_country;
	}
	
	public function getBillingPhoneNumber() {
		return null;
	}
	
	public function getBillingMobilePhoneNumber() {
		return null;
	}
	
	public function getBillingDateOfBirth() {
		return null;
	}
	
	public function getBillingCompanyName() {
		return $this->order->billing_company;
	}
	
	public function getBillingCommercialRegisterNumber() {
		return null;
	}
	
	public function getBillingSalesTaxNumber() {
		return null;
	}
	
	public function getBillingSocialSecurityNumber() {
		return null;
	}
	
	public function getShippingEMailAddress() {
		$shippingEmail = $this->order->shipping_email;
		if (!empty($shippingEmail)) {
			return $shippingEmail;
		}
		else {
			return $this->getBillingEMailAddress();
		}
	}
	
	public function getShippingGender() {
		$company = trim($this->getShippingCompanyName());
		if (!empty($company)) {
			return 'company';
		}
		else {
			return null;
		}
	}
	
	public function getShippingSalutation() {
		return null;
	}
	
	public function getShippingFirstName() {
		return $this->order->shipping_first_name;
	}
	
	public function getShippingLastName() {
		return $this->order->shipping_last_name;
	}
	
	public function getShippingStreet() {
		return $this->order->shipping_address_1 . " " . $this->order->shipping_address_2;
	}
	
	public function getShippingCity() {
		return $this->order->shipping_city;
	}
	
	public function getShippingPostCode() {
		return $this->order->shipping_postcode;
	}
	
	public function getShippingState() {
		if (isset($this->order->shipping_state)) {
			$state = $this->order->shipping_state;
			if (!empty($state) && strlen($state) == 2) {
				return $state;
			}
		}
		
		return null;
	}
	
	public function getShippingCountryIsoCode() {
		return $this->order->shipping_country;
	}
	
	public function getShippingPhoneNumber() {
		return null;
	}
	
	public function getShippingMobilePhoneNumber() {
		return null;
	}
	
	public function getShippingDateOfBirth() {
		return null;
	}
	
	public function getShippingCompanyName() {
		return $this->order->billing_company;
	}
	
	public function getShippingCommercialRegisterNumber() {
		return null;
	}
	
	public function getShippingSalesTaxNumber() {
		return null;
	}
	
	public function getShippingSocialSecurityNumber() {
		return null;
	}
	
	public function getOrderParameters() {
		return array();
	}
	
	protected function getLineTotalsWithoutTax(array $lines) {
		$total = 0;
	
		foreach ($lines as $line) {
			if ($line->getType() == Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT) {
				$total -= $line->getAmountExcludingTax();
			}
			else {
				$total += $line->getAmountExcludingTax();
			}
		}
	
		return $total;
	}
	
	protected function getLineTotalsWithTax(array $lines) {
		$total = 0;
	
		foreach ($lines as $line) {
			if ($line->getType() == Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT) {
				$total -= $line->getAmountIncludingTax();
			}
			else {
				$total += $line->getAmountIncludingTax();
			}
		}
	
		return $total;
	}
	
	protected function applyCartDiscounts($discount, $items) {
		// Add cart discounts: We need to apply the discount direclty on the line items, because we can not
		// show a discount with a tax. The tax may be a mixure of multiple taxes, which leads to a strange tax
		// rate.
		if ($discount > 0 ) {
			$newItems = array();
	
			$total = $this->getLineTotalsWithoutTax($items);
			$reductionRate = $discount / $total;
	
			foreach ($items as $item) {
				$newTotalAmount = $item->getAmountExcludingTax() * (1 - $reductionRate) * (1 + $item->getTaxRate()/100);
	
				$newItem = new Customweb_Payment_Authorization_DefaultInvoiceItem(
					$item->getSku(),
					$item->getName(),
					$item->getTaxRate(),
					$newTotalAmount,
					$item->getQuantity()
				);
				$newItems[] = $newItem;
			}
	
			return $newItems;
		}
		else {
			return $items;
		}
	}
	
	public function getCheckoutId() {
		return $this->checkoutId;
	}
	
	
}