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

// Make sure we don't expose any info if called directly           	  				   		
if (!function_exists('add_action')) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit();
}

// Get all admin functionality
require_once plugin_dir_path(__FILE__) . 'settings.php';

function woocommerce_barclaycardcw_meta_boxes(){
	global $post;
	
	BarclaycardCwUtil::includeClass('BarclaycardCw_Transaction');
	$transactions = BarclaycardCwUtil::getTransactionsByOrderId($post->ID);
	if (count($transactions) > 0) {
		add_meta_box('woocommerce-barclaycardcw-information', 
				__('Barclaycard Transactions', 'woocommerce_barclaycardcw'), 
				'woocommerce_barclaycardcw_transactions', 'shop_order', 'normal', 'default');
	}
}
add_action('add_meta_boxes', 'woocommerce_barclaycardcw_meta_boxes');

function woocommerce_barclaycardcw_transactions($post){
	BarclaycardCwUtil::includeClass('BarclaycardCw_Transaction');
	$transactions = BarclaycardCwUtil::getTransactionsByOrderId($post->ID);
	
	echo '<table class="wp-list-table widefat table barclaycardcw-transaction-table">';
	echo '<thead><tr>';
	echo '<th>#</th>';
	echo '<th>' . __('Transaction Number', 'woocommerce_barclaycardcw') . '</th>';
	echo '<th>' . __('Date', 'woocommerce_barclaycardcw') . '</th>';
	echo '<th>' . __('Payment Method', 'woocommerce_barclaycardcw') . '</th>';
	echo '<th>' . __('Is Authorized', 'woocommerce_barclaycardcw') . '</th>';
	echo '<th>' . __('Amount', 'woocommerce_barclaycardcw') . '</th>';
	echo '<th>&nbsp;</th>';
	echo '</tr></thead>';
	
	foreach ($transactions as $transaction) {
		echo '<tr class="barclaycardcw-main-row"  id="barclaycardcw-main_row_' . $transaction->getTransactionId() . '">';
		echo '<td>' . $transaction->getTransactionId() . '</td>';
		echo '<td>' . $transaction->getTransactionExternalId() . '</td>';
		echo '<td>' . $transaction->getCreatedOn()->format("Y-m-d H:i:s") . '</td>';
		echo '<td>';
		if ($transaction->getTransactionObject() != NULL) {
			echo $transaction->getTransactionObject()->getPaymentMethod()->getPaymentMethodDisplayName();
		}
		else {
			echo '--';
		}
		echo '</td>';
		echo '<td>';
		if ($transaction->getTransactionObject() != NULL && $transaction->getTransactionObject()->isAuthorized()) {
			echo __('Yes');
		}
		else {
			echo __('No');
		}
		echo '</td>';
		echo '<td>';
		if ($transaction->getTransactionObject() != NULL) {
			echo number_format($transaction->getTransactionObject()->getAuthorizationAmount(), 2);
		}
		else {
			echo '--';
		}
		echo '</td>';
		echo '<td>
				<a class="barclaycardcw-more-details-button button">' .
				 __('More Details', 'woocommerce_barclaycardcw') . '</a>
				<a class="barclaycardcw-less-details-button button">' .
				 __('Less Details', 'woocommerce_barclaycardcw') . '</a>
			</td>';
		echo '</tr>';
		echo '<tr class="barclaycardcw-details-row" id="barclaycardcw_details_row_' . $transaction->getTransactionId() . '">';
		echo '<td colspan="7">';
		echo '<div class="barclaycardcw-box-labels">';
		if ($transaction->getTransactionObject() !== NULL) {
			foreach ($transaction->getTransactionObject()->getTransactionLabels() as $label) {
				echo '<div class="label-box">';
				echo '<div class="label-title">' . $label['label'] . ' ';
				if (isset($label['description']) && !empty($label['description'])) {
					echo woocommerce_barclaycardcw_get_help_box($label['description']);
				}
				echo '</div>';
				echo '<div class="label-value">' . $label['value'] . '</div>';
				echo '</div>';
			}
		}
		else {
			echo __("No more details available.", 'woocommerce_barclaycardcw');
		}
		echo '</div>';
		
		if ($transaction->getTransactionObject() !== NULL) {
			
			
			echo '<div class="capture-box box">';
			if ($transaction->getTransactionObject()->isCapturePossible()) {
				
				$url = Customweb_Util_Url::appendParameters(get_option('siteurl') . '/wp-admin/admin.php', 
						array(
							'page' => 'woocommerce-barclaycardcw_capture',
							'cwTransactionId' => $transaction->getTransactionId(),
							'noheader' => 'true' 
						));
				echo '<p><a href="' . $url . '" class="button">Capture</a></p>';
			}
			
			if ($transaction->getTransactionObject()->isCancelPossible()) {
				$url = Customweb_Util_Url::appendParameters(get_option('siteurl') . '/wp-admin/admin.php', 
						array(
							'page' => 'woocommerce-barclaycardcw_cancel',
							'cwTransactionId' => $transaction->getTransactionId(),
							'noheader' => 'true' 
						));
				echo '<p><a href="' . $url . '" class="button">Cancel</a></p>';
			}
			echo '</div>';
			
									
			if ($transaction->getTransactionObject()->isRefundPossible()) {
				echo '<div class="refund-box box">';
				$url = Customweb_Util_Url::appendParameters(get_option('siteurl') . '/wp-admin/admin.php', 
						array(
							'page' => 'woocommerce-barclaycardcw_refund',
							'cwTransactionId' => $transaction->getTransactionId(),
							'noheader' => 'true' 
						));
				echo '<p><a href="' . $url . '" class="button">Refund</a></p>';
				echo '</div>';
			}
			
			

			
			if (count($transaction->getTransactionObject()->getCaptures())) {
				echo '<div class="capture-history-box box">';
				echo '<h4>' . __('Captures', 'woocommerce_barclaycardcw') . '</h4>';
				echo '<table class="table" cellpadding="0" cellspacing="0" width="100%">';
				echo '<thead>';
				echo '<tr>';
				echo '<th>' . __('Date', 'woocommerce_barclaycardcw') . '</th>';
				echo '<th>' . __('Amount', 'woocommerce_barclaycardcw') . '</th>';
				echo '<th>' . __('Status', 'woocommerce_barclaycardcw') . '</th>';
				echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
				foreach ($transaction->getTransactionObject()->getCaptures() as $capture) {
					echo '<tr>';
					echo '<td>' . $capture->getCaptureDate()->format("Y-m-d H:i:s") . '</td>';
					echo '<td>' . $capture->getAmount() . '</td>';
					echo '<td>' . $capture->getStatus() . '</td>';
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
				echo '</div>';
			}
			
			

			
			if (count($transaction->getTransactionObject()->getRefunds())) {
				echo '<div class="refund-history-box box">';
				echo '<h4>' . __('Refunds', 'woocommerce_barclaycardcw') . '</h4>';
				echo '<table class="table" cellpadding="0" cellspacing="0" width="100%">';
				echo '<thead>';
				echo '<tr>';
				echo '<th>' . __('Date', 'woocommerce_barclaycardcw') . '</th>';
				echo '<th>' . __('Amount', 'woocommerce_barclaycardcw') . '</th>';
				echo '<th>' . __('Status', 'woocommerce_barclaycardcw') . '</th>';
				echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
				foreach ($transaction->getTransactionObject()->getRefunds() as $refund) {
					echo '<tr>';
					echo '<td>' . $refund->getRefundedDate()->format("Y-m-d H:i:s") . '</td>';
					echo '<td>' . $refund->getAmount() . '</td>';
					echo '<td>' . $refund->getStatus() . '</td>';
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
				echo '</div>';
			}
			
			

			if (count($transaction->getTransactionObject()->getHistoryItems())) {
				echo '<div class="previous-actions box">';
				echo '<h4>' . __('Previous Actions', 'woocommerce_barclaycardcw') . '</h4>';
				echo '<table class="table" cellpadding="0" cellspacing="0" width="100%">';
				echo '<thead>';
				echo '<tr>';
				echo '<th>' . __('Date', 'woocommerce_barclaycardcw') . '</th>';
				echo '<th>' . __('Action', 'woocommerce_barclaycardcw') . '</th>';
				echo '<th>' . __('Message', 'woocommerce_barclaycardcw') . '</th>';
				echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
				foreach ($transaction->getTransactionObject()->getHistoryItems() as $historyItem) {
					echo '<tr>';
					echo '<td>' . $historyItem->getCreationDate()->format("Y-m-d H:i:s") . '</td>';
					echo '<td>' . $historyItem->getActionPerformed() . '</td>';
					echo '<td>' . $historyItem->getMessage() . '</td>';
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
				echo '</div>';
			}
		}
		echo '</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	
	if (class_exists('WC_Subscriptions_Order') && WC_Subscriptions_Order::order_contains_subscription($post->ID)) {
		echo '<div class="barclaycardcw-renewal">';
		echo '<span>' . __('Subscriptions: Add Manual Renewal', 'woocommerce_barclaycardcw') . '</span>';
		echo ' <input type="submit" class="button button-primary tips" 
			name="barclaycardcw_manual_renewal" 
			value="' . __('Add manual renewal', 'woocommerce_barclaycardcw') .
				 '" 
			data-tip="' .
				 __(
						'A manual renewal debits the customer directly for this subscription. This by pass any time restriction of the automatic subscription plugin.', 
						'woocommerce_barclaycardcw') . '" />';
		echo '</div>';
	}
	
}

function woocommerce_barclaycardcw_get_help_box($text){
		return '<img class="help_tip" data-tip="' . $text . '" src="' . BarclaycardCwUtil::getResourcesUrl('image/help.png') . '" height="16" width="16" />';
}

function woocommerce_barclaycardcw_transactions_process($orderId, $post){
	if ($post->post_type == 'shop_order') {
		global $barclaycardcw_processing;
		
		try {
			
			//The default payment methods get instanciated per wc_payment_gateways instance
			if (class_exists('WC_Payment_Gateways')) {
				//Method introduced in Woo 2.1
				if (method_exists('WC_Payment_Gateways', 'instance')) {
					WC_Payment_Gateways::instance();
				}
				//Only created instances once
				elseif (!isset($GLOBALS['woocommerce_cw_method_instances_created'])) {
					new WC_Payment_Gateways();
					$GLOBALS['woocommerce_cw_method_instances_created'] = true;
				}
			}
			
			

			if (isset($_POST['barclaycardcw_manual_renewal']) && $barclaycardcw_processing == NULL) {
				
				$barclaycardcw_processing = true;
				
				$initialTransaction = BarclaycardCwUtil::getInitialTransactionByOrderId($orderId);
				if ($initialTransaction === NULL) {
					throw new Exception("This order has no initial transaction, hence no new renewal can be created.");
				}
				$order = $initialTransaction->getTransactionObject()->getTransactionContext()->getOrderContext()->getOrderObject();
				$userId = $order->customer_user;
				$subscriptionKey = WC_Subscriptions_Manager::get_subscription_key($orderId);
				WC_Subscriptions_Payment_Gateways::gateway_scheduled_subscription_payment($userId, $subscriptionKey);
				global $barclaycardcw_recurring_process_failure;
				if ($barclaycardcw_recurring_process_failure === NULL) {
					woocommerce_barclaycardcw_admin_show_message(
							__("Successfully add a manual renewal payment.", 'woocommerce_barclaycardcw'), 'info');
				}
				else {
					woocommerce_barclaycardcw_admin_show_message($barclaycardcw_recurring_process_failure, 'error');
				}
			}
			
		}
		catch (Exception $e) {
			woocommerce_barclaycardcw_admin_show_message($e->getMessage(), 'error');
		}
	}
}
add_action('save_post', 'woocommerce_barclaycardcw_transactions_process', 1, 2);

