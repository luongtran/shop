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
BarclaycardCwUtil::includeClass('BarclaycardCw_OrderContext');
library_load_class_by_name('Customweb_Util_Invoice');

class BarclaycardCw_RecurringOrderContext extends BarclaycardCw_OrderContext
{
	protected $productId;
	
	public function __construct($order, $paymentMethod, $amountToCharge, $productId, $userId = null) {
		parent::__construct($order, $paymentMethod, $userId);
		$this->orderAmount = $amountToCharge;
		$this->productId = $productId;
	}
	
	public function getInvoiceItems() {
		$items = array();
		$amountIncludingTax = $this->orderAmount;
		
		$taxRate = 0;
		
		// Recurring Itmes
		foreach (WC_Subscriptions_Order::get_recurring_items($this->getOrderObject()) as $reccuringItem) {
			$product = WC_Subscriptions::get_product($reccuringItem['product_id']);
			$amountExcludingTax = $reccuringItem['recurring_line_subtotal'];
			$tax = $reccuringItem['recurring_line_subtotal_tax'];
			$taxRate = $tax / $amountExcludingTax * 100;
			$amountIncludingTax = $amountExcludingTax + $tax;
			$item = new Customweb_Payment_Authorization_DefaultInvoiceItem($product->get_sku(), $product->get_title(), $taxRate, $amountIncludingTax, 1);
			$items[] = $item;
		}
		
		// Apply Cart discounts
		$cartDiscount = WC_Subscriptions_Order::get_recurring_discount_cart($this->getOrderObject());
		if ($cartDiscount > 0) {
			$items = $this->applyCartDiscounts($cartDiscount, $items);
		}
		
		// Apply Order discounts
		$orderDiscount = WC_Subscriptions_Order::get_recurring_discount_total($this->getOrderObject());
		if ($orderDiscount > 0) {
			$taxRate = 0;
			$items[] = new Customweb_Payment_Authorization_DefaultInvoiceItem(
				'order-discount',
				__('Discount', 'woocommerce_barclaycardcw'),
				$taxRate,
				$orderDiscount,
				1,
				Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT
			);
		}
		
		// Shipping
		$shipping = WC_Subscriptions_Order::get_recurring_shipping_total($this->getOrderObject());
		if ($shipping > 0) {
			$shippingExclTax = $shipping;
			$shippingTax = WC_Subscriptions_Order::get_recurring_shipping_tax_total($this->getOrderObject());
			$taxRate = $shippingTax / $shippingExclTax * 100;
			$items[] = new Customweb_Payment_Authorization_DefaultInvoiceItem(
				'shipping',
				$this->getShippingMethod(),
				$taxRate,
				$shippingExclTax + $shippingTax,
				1,
				Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_SHIPPING
			);
		}
		
		// Calculate the difference to the amountToCharge. This can happen, when some outstanding payments are added to this one.
		$total = $this->getLineTotalsWithTax($items);
		$difference = $this->orderAmount - $total;
		if ($difference > 0) {
			$taxRate = 0;
			$items[] = new Customweb_Payment_Authorization_DefaultInvoiceItem(
				'outstanding-payments',
				__('Outstanding Payments'),
				$taxRate,
				$difference,
				1,
				Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_PRODUCT
			);
		}
		else {
			$taxRate = 0;
			$items[] = new Customweb_Payment_Authorization_DefaultInvoiceItem(
				'other-discount',
				__('Other Discount', 'woocommerce_barclaycardcw'),
				$taxRate,
				abs($difference),
				1,
				Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT
			);
		}
		
		return Customweb_Util_Invoice::ensureUniqueSku($items);
	}
	
}