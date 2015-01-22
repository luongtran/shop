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

BarclaycardCwUtil::includeClass('BarclaycardCw_AbstractOrderContext');

class BarclaycardCw_OrderContext extends BarclaycardCw_AbstractOrderContext {
	
	
	public function getInvoiceItems() {
		$items = array();
		$wooCommerceItems = $this->order->get_items(array('line_item', 'fee'));
		foreach($wooCommerceItems as $wooItem) {
			
			$product = $this->order->get_product_from_item( $wooItem );
			if (is_object($product)) {
				$sku = $product->get_sku();
			}
			else {
				$sku = $wooItem['name'];
			}
			$item_meta = new WC_Order_Item_Meta( $wooItem['item_meta'] );
			
			$name = $wooItem['name'];
			if ($meta = $item_meta->display(true, true)) {
				$name .= ' ( ' . $meta . ' )';
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
		
		if ($this->order->get_cart_discount()) {
			$items = $this->applyCartDiscounts($this->order->get_cart_discount(), $items);
		}
		
		// Add order discounts 
		if ($this->order->get_order_discount() > 0 ) {
			$taxRate = 0;
			$items[] = new Customweb_Payment_Authorization_DefaultInvoiceItem(
				'order-discount',
				__('Discount', 'woocommerce_barclaycardcw'),
				$taxRate,
				$this->order->get_order_discount(),
				1,
				Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT
			);
		}
		
		// Add Shipping
		$shipping = 0;
		if(method_exists($this->order, 'get_total_shipping')){
			$shipping = $this->order->get_total_shipping();
		}
		else {
			$shipping = $this->order->get_shipping();
		}
		if ($shipping > 0) {
			$shippingExclTax = $shipping;
			$shippingTax = $this->order->get_shipping_tax();
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
		return __('Shipping via', 'woocommerce') . ' ' . ucwords( $this->order->shipping_method_title );
	}
	
	public function isSubscription() {
		return class_exists('WC_Subscriptions_Order') && WC_Subscriptions_Order::order_contains_subscription($this->getOrderObject()->id);
	}
	
}