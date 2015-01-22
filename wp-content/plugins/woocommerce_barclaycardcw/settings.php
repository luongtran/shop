<?php

require_once 'BarclaycardCw/BackendFormRenderer.php';
require_once 'Customweb/Form/Control/IEditableControl.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/IRefund.php';
require_once 'Customweb/Core/Http/ContextRequest.php';
require_once 'Customweb/Payment/Authorization/IInvoiceItem.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICancel.php';
require_once 'Customweb/Form/Control/MultiControl.php';
require_once 'Customweb/Form.php';
require_once 'Customweb/Licensing/BarclaycardCw/License.php';
require_once 'Customweb/Payment/Authorization/DefaultInvoiceItem.php';
require_once 'Customweb/Util/Url.php';
require_once 'Customweb/Util/Currency.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICapture.php';
require_once 'Customweb/IForm.php';


// Make sure we don't expose any info if called directly           	  				   		
if (!function_exists('add_action')) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

// Add some CSS for admin           	  				   		
if (is_admin()) {
	add_action( 'admin_init', 'woocommerce_barclaycardcw_admin_add_scripts' );
	function woocommerce_barclaycardcw_admin_add_scripts() {
		wp_register_style('woocommerce_barclaycardcw_admin_styles', plugins_url('resources/css/admin_settings.css', __FILE__) );
		wp_enqueue_style('woocommerce_barclaycardcw_admin_styles');
		
		wp_register_script('woocommerce_barclaycardcw_admin_js', plugins_url('resources/js/admin_settings.js', __FILE__) );
		wp_enqueue_script('woocommerce_barclaycardcw_admin_js');
		if (!session_id()) {
			session_start();
		}
	}
	
	function woocommerce_barclaycardcw_admin_notice_handler() {
		if (isset($_SESSION['woocommerce_barclaycardcw__messages']) && count($_SESSION['woocommerce_barclaycardcw__messages']) > 0) {
			
			foreach ($_SESSION['woocommerce_barclaycardcw__messages'] as $message) {
				$cssClass = '';
				if (strtolower($message['type']) == 'error') {
					$cssClass = 'error';
				}
				else if (strtolower($message['type']) == 'info') {
					$cssClass = 'updated';
				}
				
				echo '<div class="' . $cssClass . '">';
					echo '<p>Barclaycard: ' . $message['message'] . '</p>';
				echo '</div>';
			}
			
			$_SESSION['woocommerce_barclaycardcw__messages'] = array();
		}
	}
	add_action('admin_notices', 'woocommerce_barclaycardcw_admin_notice_handler');
}


function woocommerce_barclaycardcw_admin_show_message($message, $type) {
	if (!session_id()) {
		session_start();
	}

	if (!isset($_SESSION['woocommerce_barclaycardcw__messages'])) {
		$_SESSION['woocommerce_barclaycardcw__messages'] = array();
	}
	$_SESSION['woocommerce_barclaycardcw__messages'][] = array(
			'message' => $message,
			'type' => $type,
	);
}


/**
 * Add the configuration menu
 */
function woocommerce_barclaycardcw_menu() {
	add_menu_page('Barclaycard', __('Barclaycard', 'woocommerce_barclaycardcw'), 'manage_options', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_options');
	$container = BarclaycardCwUtil::createContainer();
	if ($container->hasBean('Customweb_Payment_BackendOperation_Form_IAdapter')) {
		$adapter = $container->getBean('Customweb_Payment_BackendOperation_Form_IAdapter');
		foreach ($adapter->getForms() as $form) {
			add_submenu_page('woocommerce-barclaycardcw', 'Barclaycard '.$form->getTitle(), $form->getTitle(), 'manage_options', 'woocommerce-barclaycardcw'.$form->getMachineName(), 'woocommerce_barclaycardcw_extended_options');
		}
	}
	
	
	add_submenu_page(null, 'Barclaycard Capture', 'Barclaycard Capture', 'manage_options', 'woocommerce-barclaycardcw_capture', 'woocommerce_barclaycardcw_render_capture');
	add_submenu_page(null, 'Barclaycard Cancel', 'Barclaycard Cancel', 'manage_options', 'woocommerce-barclaycardcw_cancel', 'woocommerce_barclaycardcw_render_cancel');
	add_submenu_page(null, 'Barclaycard Refund', 'Barclaycard Refund', 'manage_options', 'woocommerce-barclaycardcw_refund', 'woocommerce_barclaycardcw_render_refund');
	
}
add_action('admin_menu', 'woocommerce_barclaycardcw_menu');



function woocommerce_barclaycardcw_render_cancel() {

	
	
	
	$request = Customweb_Core_Http_ContextRequest::getInstance();
	$query = $request->getParsedQuery();
	$post = $request->getParsedBody();
	$transactionId = $query['cwTransactionId'];
	
	if(empty($transactionId)) {
		wp_redirect(get_option('siteurl').'/wp-admin');
		exit;
	}
	
	
	
	$transaction = BarclaycardCwUtil::getTransactionById($transactionId);
	$orderId = $transaction->getOrderId();
	$url = str_replace('>orderId', $orderId, get_option('siteurl').'/wp-admin/post.php?post=>orderId&action=edit');
	if($request->getMethod() == 'POST') {
		if(isset($post['cancel'])) {
			$adapter = BarclaycardCwUtil::createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_ICancel');
			if (!($adapter instanceof Customweb_Payment_BackendOperation_Adapter_Service_ICancel)) {
				throw new Exception("No adapter with interface 'Customweb_Payment_BackendOperation_Adapter_Service_ICancel' provided.");
			}
			
			
			try {
				$adapter->cancel($transaction->getTransactionObject());
				woocommerce_barclaycardcw_admin_show_message(__("Successfully cancelled the transaction.", 'woocommerce_barclaycardcw'), 'info');
			} catch(Exception $e) {
				woocommerce_barclaycardcw_admin_show_message($e->getMessage(), 'error');
			}
			BarclaycardCwUtil::getEntityManager()->persist($transaction);
		}
		wp_redirect($url);
		exit;
	}
	else {
		$orderId = $transaction->getOrderId();
		if (!$transaction->getTransactionObject()->isPartialCapturePossible()) {
			woocommerce_barclaycardcw_admin_show_message(__('Capture not possible', 'woocommerce_barclaycardcw'), 'info');
			wp_redirect($url);
			exit;
		}
		if (isset($_GET['noheader'])) {
			require_once(ABSPATH . 'wp-admin/admin-header.php');
		}
		
		echo '<div class="wrap">';
		echo '<form method="POST" class="barclaycardcw-line-item-grid" id="cancel-form">';
		echo '<table class="list">
				<tbody>';
		echo '<tr>
				<td class="left-align">'.__('Are you sure you want to cancel this transaction?', 'woocommerce_barclaycardcw').'</td>
			</tr>';
		echo '<tr>
				<td colspan="1" class="left-align"><a class="button" href="'.$url.'">'.__('No', 'woocommerce_barclaycardcw').'</a></td>
				<td colspan="1" class="right-align">
					<input class="button" type="submit" name="cancel" value="'.__('Yes', 'woocommerce_barclaycardcw').'" />
				</td>
			</tr>
								</tfoot>
			</table>
		</form>';
		
		echo '</div>';
		
	}

	
	
}


function woocommerce_barclaycardcw_render_capture() {
	
	
	
	$request = Customweb_Core_Http_ContextRequest::getInstance();
	$query = $request->getParsedQuery();
	$post = $request->getParsedBody();
	$transactionId = $query['cwTransactionId'];

	if(empty($transactionId)) {
		wp_redirect(get_option('siteurl').'/wp-admin');
		exit;
	}
	

	
	$transaction = BarclaycardCwUtil::getTransactionById($transactionId);
	$orderId = $transaction->getOrderId();
	$url = str_replace('>orderId', $orderId, get_option('siteurl').'/wp-admin/post.php?post=>orderId&action=edit');
	if($request->getMethod() == 'POST') {
		
		if (isset($post['quantity'])) {
		
			$captureLineItems = array();
			$lineItems = $transaction->getTransactionObject()->getUncapturedLineItems();
			foreach ($post['quantity'] as $index => $quantity) {
				if (isset($post['price_including'][$index]) && floatval($post['price_including'][$index]) > 0) {
					$originalItem = $lineItems[$index];
					$captureLineItems[$index] = new Customweb_Payment_Authorization_DefaultInvoiceItem(
							$originalItem->getSku(), $originalItem->getName(), $originalItem->getTaxRate(), floatval($post['price_including'][$index]), $quantity, $originalItem->getType()
					);
				}
			}
			if (count($captureLineItems) > 0) {
				$adapter = BarclaycardCwUtil::createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_ICapture');
				if (!($adapter instanceof Customweb_Payment_BackendOperation_Adapter_Service_ICapture)) {
					throw new Exception("No adapter with interface 'Customweb_Payment_BackendOperation_Adapter_Service_ICapture' provided.");
				}
		
				$close = false;
				if (isset($post['close']) && $post['close'] == 'on') {
					$close = true;
				}
				try {
					$adapter->partialCapture($transaction->getTransactionObject(), $captureLineItems, $close);
					woocommerce_barclaycardcw_admin_show_message(__("Successfully added a new capture.", 'woocommerce_barclaycardcw'), 'info');
				} catch(Exception $e) {
					woocommerce_barclaycardcw_admin_show_message($e->getMessage(), 'error');
				}
				BarclaycardCwUtil::getEntityManager()->persist($transaction);
			}
		}		
		
		wp_redirect($url);
		exit;
	}
	else {
		if (!$transaction->getTransactionObject()->isPartialCapturePossible()) {
			woocommerce_barclaycardcw_admin_show_message(__('Capture not possible', 'woocommerce_barclaycardcw'), 'info');
			
			wp_redirect($url);
			exit;
		}
		if (isset($_GET['noheader'])) {
			require_once(ABSPATH . 'wp-admin/admin-header.php');
		}
		
		echo '<div class="wrap">';
		echo '<form method="POST" class="barclaycardcw-line-item-grid" id="capture-form">';
		echo '<input type="hidden" id="barclaycardcw-decimal-places" value="'.Customweb_Util_Currency::getDecimalPlaces($transaction->getTransactionObject()->getCurrencyCode()).'" />';
		echo '<input type="hidden" id="barclaycardcw-currency-code" value="'.strtoupper($transaction->getTransactionObject()->getCurrencyCode()).'" />';
		echo '<table class="list">
					<thead>
						<tr>
						<th class="left-align">'.__('Name', 'woocommerce_barclaycardcw').'</th>
						<th class="left-align">'.__('SKU', 'woocommerce_barclaycardcw').'</th>
						<th class="left-align">'.__('Type', 'woocommerce_barclaycardcw').'</th>
						<th class="left-align">'.__('Tax Rate', 'woocommerce_barclaycardcw').'</th>
						<th class="right-align">'.__('Quantity', 'woocommerce_barclaycardcw').'</th>
						<th class="right-align">'.__('Total Amount (excl. Tax)', 'woocommerce_barclaycardcw').'</th>
						<th class="right-align">'.__('Total Amount (incl. Tax)', 'woocommerce_barclaycardcw').'</th>
						</tr>
				</thead>
				<tbody>';
		foreach ($transaction->getTransactionObject()->getUncapturedLineItems() as $index => $item) {
			

			$amountExcludingTax = Customweb_Util_Currency::formatAmount($item->getAmountExcludingTax(), $transaction->getTransactionObject()->getCurrencyCode());
			$amountIncludingTax = Customweb_Util_Currency::formatAmount($item->getAmountIncludingTax(), $transaction->getTransactionObject()->getCurrencyCode());
			if ($item->getType() == Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT) {
				$amountExcludingTax = $amountExcludingTax * -1;
				$amountIncludingTax = $amountIncludingTax * -1;
			}
			echo '<tr id="line-item-row-'.$index.'" class="line-item-row" data-line-item-index="'.$index,'" >
						<td class="left-align">'.$item->getName().'</td>
						<td class="left-align">'.$item->getSku().'</td>
						<td class="left-align">'.$item->getType().'</td>
						<td class="left-align">'.$item->getTaxRate().' %<input type="hidden" class="tax-rate" value="'.$item->getTaxRate().'" /></td>
						<td class="right-align"><input type="text" class="line-item-quantity" name="quantity['.$index.']" value="'.$item->getQuantity().'" /></td>
						<td class="right-align"><input type="text" class="line-item-price-excluding" name="price_excluding['.$index.']" value="'.$amountExcludingTax.'" /></td>
						<td class="right-align"><input type="text" class="line-item-price-including" name="price_including['.$index.']" value="'.$amountIncludingTax.'" /></td>
					</tr>';
		}	
		echo '</tbody>
				<tfoot>
					<tr>
						<td colspan="6" class="right-align">'.__('Total Capture Amount', 'woocommerce_barclaycardcw').':</td>
						<td id="line-item-total" class="right-align">'.
						Customweb_Util_Currency::formatAmount($transaction->getTransactionObject()->getCapturableAmount(), $transaction->getTransactionObject()->getCurrencyCode()).
						strtoupper($transaction->getTransactionObject()->getCurrencyCode()).'
					</tr>';
				
		if ($transaction->getTransactionObject()->isCaptureClosable()) {
			
			echo '<tr>
					<td colspan="7" class="right-align">
						<label for="close-transaction">'.__('Close transaction for further captures', 'woocommerce_barclaycardcw').'</label>
						<input id="close-transaction" type="checkbox" name="close" value="on" />
					</td>
				</tr>';
		}
		
		echo '<tr>
				<td colspan="2" class="left-align"><a class="button" href="'.$url.'">'.__('Back', 'woocommerce_barclaycardcw').'</a></td>
				<td colspan="5" class="right-align">
					<input class="button" type="submit" value="'.__('Capture', 'woocommerce_barclaycardcw').'" />
				</td>
			</tr>
			</tfoot>
			</table>
		</form>';
			
		echo '</div>';
		
	}
	
	

}


function woocommerce_barclaycardcw_render_refund() {

	
	
	$request = Customweb_Core_Http_ContextRequest::getInstance();
	$query = $request->getParsedQuery();
	$post = $request->getParsedBody();
	$transactionId = $query['cwTransactionId'];

	if(empty($transactionId)) {
	wp_redirect(get_option('siteurl').'/wp-admin');
	exit;
	}



	$transaction = BarclaycardCwUtil::getTransactionById($transactionId);
	$orderId = $transaction->getOrderId();
	$url = str_replace('>orderId', $orderId, get_option('siteurl').'/wp-admin/post.php?post=>orderId&action=edit');
	if($request->getMethod() == 'POST') {

	if (isset($post['quantity'])) {

			$refundLineItems = array();
			$lineItems = $transaction->getTransactionObject()->getUncapturedLineItems();
			foreach ($post['quantity'] as $index => $quantity) {
				if (isset($post['price_including'][$index]) && floatval($post['price_including'][$index]) > 0) {
					$originalItem = $lineItems[$index];
					$refundLineItems[$index] = new Customweb_Payment_Authorization_DefaultInvoiceItem(
						$originalItem->getSku(), $originalItem->getName(), $originalItem->getTaxRate(), floatval($post['price_including'][$index]), $quantity, $originalItem->getType()
					);
				}
			}
			if (count($refundLineItems) > 0) {
				$adapter = BarclaycardCwUtil::createContainer()->getBean('Customweb_Payment_BackendOperation_Adapter_Service_IRefund');
				if (!($adapter instanceof Customweb_Payment_BackendOperation_Adapter_Service_IRefund)) {
					throw new Exception("No adapter with interface 'Customweb_Payment_BackendOperation_Adapter_Service_IRefund' provided.");
				}

				$close = false;
				if (isset($post['close']) && $post['close'] == 'on') {
					$close = true;
				}
				try {
					$adapter->partialRefund($transaction->getTransactionObject(), $refundLineItems, $close);
					woocommerce_barclaycardcw_admin_show_message(__("Successfully added a new refund.", 'woocommerce_barclaycardcw'), 'info');
				}
				catch(Exception $e) {
					woocommerce_barclaycardcw_admin_show_message($e->getMessage(), 'error');
				}
				BarclaycardCwUtil::getEntityManager()->persist($transaction);
			}
		}
		wp_redirect($url);
		exit;
	}
	else {
		if (!$transaction->getTransactionObject()->isPartialRefundPossible()) {
			woocommerce_barclaycardcw_admin_show_message(__('Refund not possible', 'woocommerce_barclaycardcw'), 'info');
			wp_redirect($url);
			exit;
		}
		if (isset($query['noheader'])) {
			require_once(ABSPATH . 'wp-admin/admin-header.php');
		}

		echo '<div class="wrap">';
		echo '<form method="POST" class="barclaycardcw-line-item-grid" id="refund-form">';
		echo '<input type="hidden" id="barclaycardcw-decimal-places" value="'.Customweb_Util_Currency::getDecimalPlaces($transaction->getTransactionObject()->getCurrencyCode()).'" />';
		echo '<input type="hidden" id="barclaycardcw-currency-code" value="'.strtoupper($transaction->getTransactionObject()->getCurrencyCode()).'" />';
		echo '<table class="list">
					<thead>
						<tr>
						<th class="left-align">'.__('Name', 'woocommerce_barclaycardcw').'</th>
						<th class="left-align">'.__('SKU', 'woocommerce_barclaycardcw').'</th>
						<th class="left-align">'.__('Type', 'woocommerce_barclaycardcw').'</th>
						<th class="left-align">'.__('Tax Rate', 'woocommerce_barclaycardcw').'</th>
						<th class="right-align">'.__('Quantity', 'woocommerce_barclaycardcw').'</th>
						<th class="right-align">'.__('Total Amount (excl. Tax)', 'woocommerce_barclaycardcw').'</th>
						<th class="right-align">'.__('Total Amount (incl. Tax)', 'woocommerce_barclaycardcw').'</th>
						</tr>
				</thead>
				<tbody>';
		foreach ($transaction->getTransactionObject()->getNonRefundedLineItems() as $index => $item) {
			$amountExcludingTax = Customweb_Util_Currency::formatAmount($item->getAmountExcludingTax(), $transaction->getTransactionObject()->getCurrencyCode());
			$amountIncludingTax = Customweb_Util_Currency::formatAmount($item->getAmountIncludingTax(), $transaction->getTransactionObject()->getCurrencyCode());
			if ($item->getType() == Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT) {
				$amountExcludingTax = $amountExcludingTax * -1;
				$amountIncludingTax = $amountIncludingTax * -1;
			}
			echo '<tr id="line-item-row-'.$index.'" class="line-item-row" data-line-item-index="'.$index,'" >
					<td class="left-align">'.$item->getName().'</td>
					<td class="left-align">'.$item->getSku().'</td>
					<td class="left-align">'.$item->getType().'</td>
					<td class="left-align">'.$item->getTaxRate().' %<input type="hidden" class="tax-rate" value="'.$item->getTaxRate().'" /></td>
					<td class="right-align"><input type="text" class="line-item-quantity" name="quantity['.$index.']" value="'.$item->getQuantity().'" /></td>
					<td class="right-align"><input type="text" class="line-item-price-excluding" name="price_excluding['.$index.']" value="'.$amountExcludingTax.'" /></td>
					<td class="right-align"><input type="text" class="line-item-price-including" name="price_including['.$index.']" value="'.$amountIncludingTax.'" /></td>
				</tr>';
		}
		echo '</tbody>
				<tfoot>
					<tr>
						<td colspan="6" class="right-align">'.__('Total Refund Amount', 'woocommerce_barclaycardcw').':</td>
						<td id="line-item-total" class="right-align">'.
						Customweb_Util_Currency::formatAmount($transaction->getTransactionObject()->getRefundableAmount(), $transaction->getTransactionObject()->getCurrencyCode()).
						strtoupper($transaction->getTransactionObject()->getCurrencyCode()).'
						</tr>';

		if ($transaction->getTransactionObject()->isRefundClosable()) {
			echo '<tr>
					<td colspan="7" class="right-align">
						<label for="close-transaction">'.__('Close transaction for further refunds', 'woocommerce_barclaycardcw').'</label>
						<input id="close-transaction" type="checkbox" name="close" value="on" />
					</td>
				</tr>';
		}

		echo '<tr>
				<td colspan="2" class="left-align"><a class="button" href="'.$url.'">'.__('Back', 'woocommerce_barclaycardcw').'</a></td>
				<td colspan="5" class="right-align">
					<input class="button" type="submit" value="'.__('Refund', 'woocommerce_barclaycardcw').'" />
				</td>
			</tr>
		</tfoot>
		</table>
		</form>';
		
		echo '</div>';

	}

		

	}


function woocommerce_barclaycardcw_extended_options() {
	$container = BarclaycardCwUtil::createContainer();
	$request = Customweb_Core_Http_ContextRequest::getInstance();
	$query = $request->getParsedQuery();
	$formName = substr($query['page'], strlen('woocommerce-barclaycardcw'));
	
	$renderer = new BarclaycardCw_BackendFormRenderer();
	
	if ($container->hasBean('Customweb_Payment_BackendOperation_Form_IAdapter')) {
		$adapter = $container->getBean('Customweb_Payment_BackendOperation_Form_IAdapter');
		
		foreach ($adapter->getForms() as $form) {
			if($form->getMachineName() == $formName) {
				$currentForm = $form;
				break;
			}
		}
		if($currentForm === null) {
			if (isset($query['noheader'])) {
				require_once(ABSPATH . 'wp-admin/admin-header.php');
			}
			return;
		}
		
		if ($request->getMethod() == 'POST') {

			$pressedButton = null;
			$body = stripslashes_deep($request->getParsedBody());
			foreach ($form->getButtons() as $button) {
				
				if (array_key_exists($button->getMachineName(), $body['button'])) {
					$pressedButton = $button;
					break;
				}
			}
			$formData = array();
			foreach($form->getElements() as $element) {
				$control = $element->getControl();
				if(! ($control instanceof Customweb_Form_Control_IEditableControl)) {
					continue;
				}
				$dataValue = $control->getFormDataValue($body);
				if( $control instanceof Customweb_Form_Control_MultiControl) {
					foreach( woocommerce_barclaycardcw_array_flatten($dataValue) as $key => $value){
						$formData[$key] = $value;
					}
				}
				else {
					$formData[$control->getControlName()] = $dataValue;
				}
			}
			$adapter->processForm($currentForm, $pressedButton, $formData);
			wp_redirect(Customweb_Util_Url::appendParameters($request->getUrl(), $request->getParsedQuery()));
			die();
		}
		
		if (isset($query['noheader'])) {
			require_once(ABSPATH . 'wp-admin/admin-header.php');
		}
		
		$currentForm = null;
		foreach ($adapter->getForms() as $form) {
			if($form->getMachineName() == $formName) {
				$currentForm = $form;
				break;
			}
		}

		if($currentForm->isProcessable()) {
			$currentForm = new Customweb_Form($currentForm);
			$currentForm->setRequestMethod(Customweb_IForm::REQUEST_METHOD_POST);
			$currentForm->setTargetUrl(Customweb_Util_Url::appendParameters($request->getUrl(), array_merge($request->getParsedQuery(), array( 'noheader' => 'true'))));
		}
		echo '<div class="wrap">';
		echo $renderer->renderForm($currentForm);
		echo '</div>';

	}
	
}


function woocommerce_barclaycardcw_array_flatten($array) {

	$return = array();
	foreach ($array as $key => $value) {
		if (is_array($value)){ 
			$return = array_merge($return, woocommerce_barclaycardcw_array_flatten($value));
		}
		else {
			$return[$key] = $value;
		}
	}
	return $return;

}

/**
 * Setup the configuration page with the callbacks to the configuration API.
 */
require_once 'Customweb/Licensing/BarclaycardCw/License.php';
Customweb_Licensing_BarclaycardCw_License::run('oagl1q4ir1egg2p3');

/**
 * Register Settings
 */
function woocommerce_barclaycardcw_admin_init() {
	
	// Append order status for pending payments
	if (!term_exists('barclaycardcw-pending', 'shop_order_status')) {
		wp_insert_term(
			'Barclaycard Pending',
			'shop_order_status',
			array(
				'description'=> 'Orders with that order status are currently in the checkout of Barclaycard.',
				'slug' => 'barclaycardcw-pending',
			)
		);
	}

	add_settings_section('woocommerce_barclaycardcw', 'Barclaycard Basics', 'woocommerce_barclaycardcw_section_callback', 'woocommerce-barclaycardcw');
	add_settings_field('woocommerce_barclaycardcw_operation_mode', __("Operation Mode", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_operation_mode', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_operation_mode');
	
	add_settings_field('woocommerce_barclaycardcw_pspid', __("Live PSPID", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_pspid', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_pspid');
	
	add_settings_field('woocommerce_barclaycardcw_test_pspid', __("Test PSPID", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_test_pspid', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_test_pspid');
	
	add_settings_field('woocommerce_barclaycardcw_live_sha_passphrase_in', __("SHA-IN Passphrase", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_live_sha_passphrase_in', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_live_sha_passphrase_in');
	
	add_settings_field('woocommerce_barclaycardcw_live_sha_passphrase_out', __("SHA-OUT Passphrase", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_live_sha_passphrase_out', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_live_sha_passphrase_out');
	
	add_settings_field('woocommerce_barclaycardcw_test_sha_passphrase_in', __("Test Account SHA-IN Passphrase", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_test_sha_passphrase_in', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_test_sha_passphrase_in');
	
	add_settings_field('woocommerce_barclaycardcw_test_sha_passphrase_out', __("Test Account SHA-OUT Passphrase", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_test_sha_passphrase_out', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_test_sha_passphrase_out');
	
	add_settings_field('woocommerce_barclaycardcw_hash_method', __("Hash calculation method", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_hash_method', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_hash_method');
	
	add_settings_field('woocommerce_barclaycardcw_order_id_schema', __("Order prefix", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_order_id_schema', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_order_id_schema');
	
	add_settings_field('woocommerce_barclaycardcw_order_description_schema', __("Order Description", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_order_description_schema', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_order_description_schema');
	
	add_settings_field('woocommerce_barclaycardcw_template', __("Dynamic Template", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_template', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_template');
	
	add_settings_field('woocommerce_barclaycardcw_template_url', __("Template URL for own template", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_template_url', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_template_url');
	
	add_settings_field('woocommerce_barclaycardcw_mobile_template_method', __("Mobile Template", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_mobile_template_method', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_mobile_template_method');
	
	add_settings_field('woocommerce_barclaycardcw_mobile_template_url', __("Mobile Template URL", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_mobile_template_url', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_mobile_template_url');
	
	add_settings_field('woocommerce_barclaycardcw_shop_id', __("Shop ID", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_shop_id', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_shop_id');
	
	add_settings_field('woocommerce_barclaycardcw_api_user_id', __("API Username", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_api_user_id', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_api_user_id');
	
	add_settings_field('woocommerce_barclaycardcw_api_password', __("API Password", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_api_password', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_api_password');
	
	add_settings_field('woocommerce_barclaycardcw_alias_usage_message', __("Intended purpose of alias", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_alias_usage_message', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_alias_usage_message');
	
	add_settings_field('woocommerce_barclaycardcw_transaction_updates', __("Transaction Updates", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_transaction_updates', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_transaction_updates');
	
	add_settings_field('woocommerce_barclaycardcw_review_input_form', __("Review Input Form", 'woocommerce_barclaycardcw'), 'woocommerce_barclaycardcw_option_callback_review_input_form', 'woocommerce-barclaycardcw', 'woocommerce_barclaycardcw');
	register_setting('woocommerce-barclaycardcw', 'woocommerce_barclaycardcw_review_input_form');
	
	
}
add_action('admin_init', 'woocommerce_barclaycardcw_admin_init');


function woocommerce_barclaycardcw_section_callback() {

}



function woocommerce_barclaycardcw_option_callback_operation_mode() {
	echo '<select name="woocommerce_barclaycardcw_operation_mode">';
		echo '<option value="test"';
		 if (get_option('woocommerce_barclaycardcw_operation_mode', "test") == "test"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Test Mode", 'woocommerce_barclaycardcw'). '</option>';
	echo '<option value="live"';
		 if (get_option('woocommerce_barclaycardcw_operation_mode', "test") == "live"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Live Mode", 'woocommerce_barclaycardcw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("If the test mode is selected the test PSPID is used and the test SHA passphrases.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_pspid() {
	echo '<input type="text" name="woocommerce_barclaycardcw_pspid" value="' . htmlspecialchars(get_option('woocommerce_barclaycardcw_pspid', ""),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("The PSPID as given by the Barclaycard.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_test_pspid() {
	echo '<input type="text" name="woocommerce_barclaycardcw_test_pspid" value="' . htmlspecialchars(get_option('woocommerce_barclaycardcw_test_pspid', ""),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("The test PSPID as given by the Barclaycard.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_live_sha_passphrase_in() {
	echo '<input type="text" name="woocommerce_barclaycardcw_live_sha_passphrase_in" value="' . htmlspecialchars(get_option('woocommerce_barclaycardcw_live_sha_passphrase_in', ""),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Enter the live SHA-IN passphrase. This value must be identical to the one in the back-end of Barclaycard.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_live_sha_passphrase_out() {
	echo '<input type="text" name="woocommerce_barclaycardcw_live_sha_passphrase_out" value="' . htmlspecialchars(get_option('woocommerce_barclaycardcw_live_sha_passphrase_out', ""),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Enter the live SHA-OUT passphrase. This value must be identical to the one in the back-end of Barclaycard.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_test_sha_passphrase_in() {
	echo '<input type="text" name="woocommerce_barclaycardcw_test_sha_passphrase_in" value="' . htmlspecialchars(get_option('woocommerce_barclaycardcw_test_sha_passphrase_in', ""),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Enter the test SHA-IN passphrase. This value must be identical to the one in the back-end of Barclaycard.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_test_sha_passphrase_out() {
	echo '<input type="text" name="woocommerce_barclaycardcw_test_sha_passphrase_out" value="' . htmlspecialchars(get_option('woocommerce_barclaycardcw_test_sha_passphrase_out', ""),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Enter the test SHA-OUT passphrase. This value must be identical to the one in the back-end of Barclaycard.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_hash_method() {
	echo '<select name="woocommerce_barclaycardcw_hash_method">';
		echo '<option value="sha1"';
		 if (get_option('woocommerce_barclaycardcw_hash_method', "sha512") == "sha1"){
			echo ' selected="selected" ';
		}
	echo '>' . __("SHA-1", 'woocommerce_barclaycardcw'). '</option>';
	echo '<option value="sha256"';
		 if (get_option('woocommerce_barclaycardcw_hash_method', "sha512") == "sha256"){
			echo ' selected="selected" ';
		}
	echo '>' . __("SHA-256", 'woocommerce_barclaycardcw'). '</option>';
	echo '<option value="sha512"';
		 if (get_option('woocommerce_barclaycardcw_hash_method', "sha512") == "sha512"){
			echo ' selected="selected" ';
		}
	echo '>' . __("SHA-512", 'woocommerce_barclaycardcw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("Select the hash calculation method to use. This value must correspond with the selected value in the back-end of Barclaycard.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_order_id_schema() {
	echo '<input type="text" name="woocommerce_barclaycardcw_order_id_schema" value="' . htmlspecialchars(get_option('woocommerce_barclaycardcw_order_id_schema', "order_{id}"),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Here you can insert an order prefix. The prefix allows you to change the order number that is transmitted to Barclaycard. The prefix must contain the tag {id}. It will then be replaced by the order number (e.g. name_{id}).", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_order_description_schema() {
	echo '<input type="text" name="woocommerce_barclaycardcw_order_description_schema" value="' . htmlspecialchars(get_option('woocommerce_barclaycardcw_order_description_schema', "Order {id}"),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("This parameter is sometimes transmitted to the acquirer (depending on the acquirer), in order to be shown on the account statements of the merchant or the customer. The prefix can contain the tag {id}. It will then be replaced by the order number (e.g. name {id}). (Payment Page only)", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_template() {
	echo '<select name="woocommerce_barclaycardcw_template">';
		echo '<option value="default"';
		 if (get_option('woocommerce_barclaycardcw_template', "default") == "default"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Use shop template", 'woocommerce_barclaycardcw'). '</option>';
	echo '<option value="custom"';
		 if (get_option('woocommerce_barclaycardcw_template', "default") == "custom"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Use own template", 'woocommerce_barclaycardcw'). '</option>';
	echo '<option value="none"';
		 if (get_option('woocommerce_barclaycardcw_template', "default") == "none"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Don't change the layout of the payment page", 'woocommerce_barclaycardcw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("With the Dynamic Template you can design the layout of the payment page yourself. For the option 'Own template' the URL to the template file must be entered into the following box.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_template_url() {
	echo '<input type="text" name="woocommerce_barclaycardcw_template_url" value="' . htmlspecialchars(get_option('woocommerce_barclaycardcw_template_url', ""),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("The URL indicated here is rendered as Template. For this you must select option 'Use own template'. The URL must point to an HTML page that contains the string '\$\$\$PAYMENT ZONE\$\$\$'. This part of the HTML file is replaced with the form for the credit card input.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_mobile_template_method() {
	echo '<select name="woocommerce_barclaycardcw_mobile_template_method">';
		echo '<option value="default"';
		 if (get_option('woocommerce_barclaycardcw_mobile_template_method', "default") == "default"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Use standard template (as selected above)", 'woocommerce_barclaycardcw'). '</option>';
	echo '<option value="custom"';
		 if (get_option('woocommerce_barclaycardcw_mobile_template_method', "default") == "custom"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Use own template", 'woocommerce_barclaycardcw'). '</option>';
	echo '<option value="customweb"';
		 if (get_option('woocommerce_barclaycardcw_mobile_template_method', "default") == "customweb"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Use customweb hosted template", 'woocommerce_barclaycardcw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("With this option you can control which template is used for mobile devices. Per default the template for desktops is used. You can either use a own template (provided through the field below) or the customweb hosted template.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_mobile_template_url() {
	echo '<input type="text" name="woocommerce_barclaycardcw_mobile_template_url" value="' . htmlspecialchars(get_option('woocommerce_barclaycardcw_mobile_template_url', ""),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("The URL indicated here is used to rendered the template for mobile devices. This field is only considered when the option mobile template is set to 'Use own template'.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_shop_id() {
	echo '<input type="text" name="woocommerce_barclaycardcw_shop_id" value="' . htmlspecialchars(get_option('woocommerce_barclaycardcw_shop_id', ""),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Here you can define a Shop ID. This is only necessary if you wish to operate several shops with one PSPID. In order to use this module, an additional module is required.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_api_user_id() {
	echo '<input type="text" name="woocommerce_barclaycardcw_api_user_id" value="' . htmlspecialchars(get_option('woocommerce_barclaycardcw_api_user_id', ""),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("You can create an API username in the back-end of Barclaycard. The API user is necessary for the direct communication between the shop and the service of Barclaycard.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_api_password() {
	echo '<input type="text" name="woocommerce_barclaycardcw_api_password" value="' . htmlspecialchars(get_option('woocommerce_barclaycardcw_api_password', ""),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Password for the API user.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_alias_usage_message() {
	echo '<textarea name="woocommerce_barclaycardcw_alias_usage_message">' . get_option('woocommerce_barclaycardcw_alias_usage_message', "") . '</textarea>';
	
	echo '<br />';
	echo __("If the Alias Manager is used, the intended purpose is shown to the customer on the payment page. Through this the customer knows why his data is saved.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_transaction_updates() {
	echo '<select name="woocommerce_barclaycardcw_transaction_updates">';
		echo '<option value="active"';
		 if (get_option('woocommerce_barclaycardcw_transaction_updates', "inactive") == "active"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Active", 'woocommerce_barclaycardcw'). '</option>';
	echo '<option value="inactive"';
		 if (get_option('woocommerce_barclaycardcw_transaction_updates', "inactive") == "inactive"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Inactive", 'woocommerce_barclaycardcw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("When the store is not available (network outage, server failure or any other outage), when the feedback of Barclaycard is sent, then the transaction state is not updated. Hence no order confirmation e-mail is sent and the order is not in the paid state. By activating the transaction update, such transactions can be authorized later over direct link. To use this feature the update service must be activated and the API username and the API password must be set.", 'woocommerce_barclaycardcw');
}

function woocommerce_barclaycardcw_option_callback_review_input_form() {
	echo '<select name="woocommerce_barclaycardcw_review_input_form">';
		echo '<option value="active"';
		 if (get_option('woocommerce_barclaycardcw_review_input_form', "active") == "active"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Activate input form in review pane.", 'woocommerce_barclaycardcw'). '</option>';
	echo '<option value="deactivate"';
		 if (get_option('woocommerce_barclaycardcw_review_input_form', "active") == "deactivate"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Deactivate input form in review pane.", 'woocommerce_barclaycardcw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("Should the input form for credit card data rendered in the review pane? To work the user must have JavaScript activated. In case the browser does not support JavaScript a fallback is provided. This feature is not supported by all payment methods.", 'woocommerce_barclaycardcw');
}

