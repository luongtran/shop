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

BarclaycardCwUtil::includeClass('BarclaycardCw_AbstractCartOrderContext');

/**
 * This class implements a order context based on user data and the current cart. This order context should never be persisted!
 * @author hunziker
 *
 */
class BarclaycardCw_CartOrderContext extends BarclaycardCw_AbstractCartOrderContext
{
	
	public function __construct($userData, Customweb_Payment_Authorization_IPaymentMethod $paymentMethod, $userId = null) {
		global $woocommerce;
		
		if (!isset($_SESSION['BarclaycardCw']['checkoutId'])) {
			if (!isset($_SESSION['BarclaycardCw'])) {
				$_SESSION['BarclaycardCw'] = array();
			}
			$_SESSION['BarclaycardCw']['checkoutId'] = Customweb_Core_Util_Rand::getUuid();
		}
		$this->checkoutId = $_SESSION['BarclaycardCw']['checkoutId'];
		
		$this->cart = $woocommerce->cart;
		$this->userData = $userData;
		$this->currencyCode = get_woocommerce_currency();
		$this->paymentMethod = $paymentMethod;
		$this->orderAmount = $this->cart->get_total();
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
	}
	
	public function isSubscription() {
		return class_exists('WC_Subscriptions_Cart') && WC_Subscriptions_Cart::cart_contains_subscription();
	}
	
	public function getInvoiceItems() {
		
		if (empty($this->cart->cart_contents)) {
			return array();
		}
		
		$items = array();
		$wooCommerceItems = $this->cart->cart_contents;
		foreach($wooCommerceItems as $wooItem) {
			
			$product = $wooItem['data'];
			$item_meta = new WC_Order_Item_Meta( $wooItem['item_meta'] );
			
			$sku = $product->get_sku();
			$name = $product->get_title();
			if(empty($sku)) {
				$sku = $name;
			}	
			
			if(isset($wooItem['line_subtotal']) && isset($wooItem['qty']) && isset($wooItem['line_subtotal_tax'])) {
				$amountExclTax = $wooItem['line_subtotal'];
				$amountIncludingTax = $wooItem['line_subtotal'] + $wooItem['line_subtotal_tax'];
				$taxRate = 0;
				if($amountExclTax != 0) {
					$taxRate = ($amountIncludingTax - $amountExclTax) / $amountExclTax * 100;
				}
				$quantity = $wooItem['qty'];
			}
			else {
				$quantity = 1;
				$amountExclTax = $wooItem['line_total'];
				$amountIncludingTax = $wooItem['line_total'] + $wooItem['line_tax'];
				$taxRate = 0;
				if($amountExclTax != 0) {
					$taxRate = ($amountIncludingTax - $amountExclTax) / $amountExclTax * 100;
				}
			}
			
			$item = new Customweb_Payment_Authorization_DefaultInvoiceItem($sku, $name, $taxRate, $amountIncludingTax, $quantity);
			$items[] = $item;
		}
		
		if ($this->cart->discount_cart > 0) {
			$items = $this->applyCartDiscounts($this->cart->discount_cart, $items);
		}
		
		// Add order discounts 
		if ($this->cart->discount_total > 0 ) {
			$taxRate = 0;
			$items[] = new Customweb_Payment_Authorization_DefaultInvoiceItem(
				'order-discount',
				__('Discount', 'woocommerce_barclaycardcw'),
				$taxRate,
				$this->cart->discount_total,
				1,
				Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT
			);
		}
		
		// Add Shipping
		if ($this->cart->shipping_total > 0) {
			$shippingExclTax = $this->cart->shipping_total;
			$shippingTax = $this->cart->shipping_tax_total;
			$taxRate = 0;
			if($shippingExclTax != 0) {
				$taxRate = $shippingTax / $shippingExclTax * 100;
			}
			$items[] = new Customweb_Payment_Authorization_DefaultInvoiceItem(
				'shipping',
				$this->getShippingMethod(),
				$taxRate,
				$shippingExclTax + $shippingTax,
				1,
				Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_SHIPPING
			);
		}
		
		return Customweb_Util_Invoice::ensureUniqueSku($items);
	}
	
	public function getShippingMethod() {
		return $this->cart->shipping_label;
	}
}