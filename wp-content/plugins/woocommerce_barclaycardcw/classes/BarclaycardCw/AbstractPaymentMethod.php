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
library_load_class_by_name('Customweb_Payment_Authorization_PaymentPage_IAdapter');
library_load_class_by_name('Customweb_Payment_Authorization_Recurring_IAdapter');
library_load_class_by_name('Customweb_Util_Url');

BarclaycardCwUtil::includeClass('BarclaycardCw_OrderContext');
BarclaycardCwUtil::includeClass('BarclaycardCw_PaymentMethodWrapper');
BarclaycardCwUtil::includeClass('BarclaycardCw_Transaction');
BarclaycardCwUtil::includeClass('BarclaycardCw_TransactionContext');
BarclaycardCwUtil::includeClass('BarclaycardCw_ConfigurationAdapter');
BarclaycardCwUtil::includeClass('BarclaycardCw_RecurringTransactionContext');
BarclaycardCwUtil::includeClass('BarclaycardCw_CartOrderContext');
BarclaycardCwUtil::includeClass('BarclaycardCw_PaymentGatewayProxy');

/**
 *           	  				   		
 * This class handlers the main payment interaction with the
 * BarclaycardCw server.
 */
abstract class BarclaycardCw_AbstractPaymentMethod extends BarclaycardCw_PaymentGatewayProxy implements 
		Customweb_Payment_Authorization_IPaymentMethod {
	public $class_name;
	public $id;
	public $title;
	public $chosen;
	public $has_fields = FALSE;
	public $countries;
	public $availability;
	public $enabled = 'no';
	public $icon;
	public $description;
	private $isCartTotalCalculated = FALSE;

	public function __construct(){
		$this->class_name = substr(get_class($this), 0, 39);
		
		$this->id = $this->class_name;
		$this->method_title = $this->admin_title;
		
		parent::__construct();
		
		$title = $this->getPaymentMethodConfigurationValue('title');
		if (!empty($title)) {
			$this->title = $title;
		}
		
		$this->description = $this->getPaymentMethodConfigurationValue('description');
	}

	public function getPaymentMethodName(){
		return $this->machineName;
	}

	public function getPaymentMethodDisplayName(){
		return $this->title;
	}

	public function receipt_page($order){}

	public function getBackendDescription(){
		return __('The configuration values for Barclaycard can be set under:', 'woocommerce_barclaycardcw') .
				 ' <a href="options-general.php?page=woocommerce-barclaycardcw">' .
				 __('Barclaycard Settings', 'woocommerce_barclaycardcw') . '</a>';
	}

	/**
	 * This method is called when the payment is submitted.
	 *
	 * @param int $order_id
	 */
	public function process_payment($order_id){
		global $woocommerce;
		
		$order = BarclaycardCwUtil::loadOrderObjectById($order_id);
		$orderContext = new BarclaycardCw_OrderContext($order, new BarclaycardCw_PaymentMethodWrapper($this));
		$paymentContext = BarclaycardCwUtil::getPaymentCustomerContext($orderContext->getCustomerId());
		
		$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByContext($orderContext);
		
		// Validate transaction
		$errorMessage = null;
		try {
			$adapter->validate($orderContext, $paymentContext, Customweb_Core_Http_ContextRequest::getInstance()->getParameters());
		}
		catch (Exception $e) {
			$errorMessage = $e->getMessage();
		}
		BarclaycardCwUtil::persistPaymentCustomerContext($paymentContext);
		
		if ($errorMessage !== null) {
			throw new Exception($errorMessage);
		}
		
		// Bugfix to prevent the deletion of the cart, when the user goes back to the shop.
		unset($_SESSION['order_awaiting_payment']);
		if (isset($woocommerce)) {
			unset($woocommerce->session->order_awaiting_payment);
		}
		
		// Update order
		$pendingStatus = 'barclaycardcw-pending';
		$order->update_status($pendingStatus, 
				__('The customer is now in the payment process of Barclaycard.', 'woocommerce_barclaycardcw'));
		$aliasTransactionId = $this->getCurrentSelectedAlias();
		if (is_ajax()) {
			try {
				$rs = $this->getPaymentForm($order_id, $aliasTransactionId);
				if (is_array($rs)) {
					return $rs;
				}
				echo "<script type=\"text/javascript\"> jQuery('form.checkout').replaceWith(jQuery('#barclaycardcw-payment-container')); jQuery('.woocommerce-info').remove(); jQuery('html, body').animate({ scrollTop: (jQuery('#barclaycardcw-payment-container').offset().top-150) }, '1000');</script>";
				die(0);
			}
			catch (Exception $e) {
				$this->showError($e->getMessage());
			}
		}
		else {
			
			return array(
				'result' => 'success',
				'redirect' => BarclaycardCwUtil::getPluginUrl("payment.php", 
						array(
							'order_id' => $order_id,
							'payment_method_class' => get_class($this),
							'alias_transaction_id' => $aliasTransactionId 
						)) 
			);
		}
	}

	public function isAliasManagerActive(){
		$result = false;
		
		$result = ($this->getPaymentMethodConfigurationValue('alias_manager') == 'active');
		
		return $result;
	}

	public function getCurrentSelectedAlias(){
		$aliasTransactionId = null;
		
		if (isset($_REQUEST[$this->getAliasHTMLFieldName()])) {
			$aliasTransactionId = $_REQUEST[$this->getAliasHTMLFieldName()];
		}
		else if (isset($_POST['post_data'])) {
			parse_str($_POST['post_data'], $data);
			if (isset($data[$this->getAliasHTMLFieldName()])) {
				$aliasTransactionId = $data[$this->getAliasHTMLFieldName()];
			}
		}
		
		return $aliasTransactionId;
	}

	private function showError($errorMessage){
		echo '<div class="woocommerce-error">' . $errorMessage . '</div>';
		die();
	}
	
	public function getPaymentForm($orderId, $aliasTransactionId = NULL, $failedTransactionId = NULL){
		require_once 'Customweb/Licensing/BarclaycardCw/License.php';
		$arguments = array(
			'aliasTransactionId' => $aliasTransactionId,
 			'failedTransactionId' => $failedTransactionId,
 			'orderId' => $orderId,
 		);
		return Customweb_Licensing_BarclaycardCw_License::run('f6aq97ess1itg2m6', $this, $arguments);
	}

	public function call_51i7chf824nkjcs6() {
		$arguments = func_get_args();
		$method = $arguments[0];
		$call = $arguments[1];
		$parameters = array_slice($arguments, 2);
		if ($call == 's') {
			return call_user_func_array(array(get_class($this), $method), $parameters);
		}
		else {
			return call_user_func_array(array($this, $method), $parameters);
		}
		
		
	}
	/**
	 * This method is invoked to check if the payment method is available for checkout.
	 */
	public function is_available(){
		global $woocommerce;
		
		$available = parent::is_available();
		
		if ($available !== true) {
			return false;
		}
		
		if (isset($woocommerce)) {
			if (!isset($woocommerce->cart->totalCalculatedCw)) {
				$woocommerce->cart->calculate_totals();
			}
			
			$orderTotal = $woocommerce->cart->total;
			if ($orderTotal < $this->getPaymentMethodConfigurationValue('min_total')) {
				return false;
			}
			if ($this->getPaymentMethodConfigurationValue('max_total') > 0 && $this->getPaymentMethodConfigurationValue('max_total') < $orderTotal) {
				return false;
			}
			
			$orderContext = $this->getCartOrderContext();
			if ($orderContext !== null) {
				$paymentContext = BarclaycardCwUtil::getPaymentCustomerContext($orderContext->getCustomerId());
				
				$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByContext($orderContext);
				$result = true;
				try {
					$adapter->prevalidate($orderContext, $paymentContext);
				}
				catch (Exception $e) {
					$result = false;
				}
				BarclaycardCwUtil::persistPaymentCustomerContext($paymentContext);
				return $result;
			}
		}
		return true;
	}

	/**
	 *
	 * @return BarclaycardCw_CartOrderContext
	 */
	private function getCartOrderContext(){
		if (!isset($_POST['post_data'])) {
			return null;
		}
		
		parse_str($_POST['post_data'], $data);
		
		return new BarclaycardCw_CartOrderContext($data, new BarclaycardCw_PaymentMethodWrapper($this));
	}

	public function payment_fields(){
		parent::payment_fields();
		
		
		if ($this->isAliasManagerActive()) {
			$userId = get_current_user_id();
			$aliases = BarclaycardCwUtil::getAliasTransactions($userId, $this->getPaymentMethodName());
			
			if (count($aliases) > 0) {
				$selectedAlias = $this->getCurrentSelectedAlias();
				
				echo '<div class="barclaycardcw-alias-input-box"><div class="alias-field-description">' .
						 __('You can choose a previous used card:', 'woocommerce_barclaycardcw') . '</div>';
				echo '<select name="' . $this->getAliasHTMLFieldName() . '">';
				echo '<option value="new"> -' . __('Select card', 'woocommerce_barclaycardcw') . '- </option>';
				foreach ($aliases as $aliasTransaction) {
					echo '<option value="' . $aliasTransaction->getTransactionId() . '"';
					if ($selectedAlias == $aliasTransaction->getTransactionId()) {
						echo ' selected="selected" ';
					}
					echo '>' . $aliasTransaction->getAliasForDisplay() . '</option>';
				}
				echo '</select></div>';
			}
		}
		
		

		$orderContext = $this->getCartOrderContext();
		if ($orderContext !== null) {
			$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByContext($orderContext);
			
			$aliasTransactionObject = null;
			$selectedAlias = $this->getCurrentSelectedAlias();
			
			if ($selectedAlias !== null) {
				$aliasTransaction = BarclaycardCwUtil::getTransactionById($selectedAlias);
				if ($aliasTransaction !== null && $aliasTransaction->getCustomerId() == get_current_user_id()) {
					$aliasTransactionObject = $aliasTransaction->getTransactionObject();
				}
			}
			
			$shopAdapter = BarclaycardCwUtil::getShopAdapterByPaymentAdapter($adapter);
			echo $shopAdapter->getReviewFormFields($orderContext, $aliasTransactionObject);
		}
	}
	
	
	public function getAliasHTMLFieldName(){
		return 'barclaycardcw_alias_' . $this->getPaymentMethodName();
	}
	
	

	/**
	 *
	 * @param WooComemrceOrder $order
	 * @return BarclaycardCw_Transaction
	 */
	protected function newDatabaseTransaction($order){
		$transaction = new BarclaycardCw_Transaction();
		
		if (isset($order->customer_user)) {
			$userId = $order->customer_user;
		}
		else {
			$userId = $order->user_id;
		}
		if (isset($_SESSION)) {
			unset($_SESSION['BarclaycardCw']['checkoutId']);
		}
		$transaction->setOrderId($order->id)->setCustomerId($userId)->setPaymentClass(get_class($this))->setPaymentMachineName(
				$this->getPaymentMethodName());
		
		BarclaycardCwUtil::getEntityManager()->persist($transaction);
		return $transaction;
	}

	/**
	 *
	 * @param BarclaycardCw_Transaction $transaction
	 * @param WooCommerceOrder $order
	 * @param int $aliasTransactionId
	 * @return BarclaycardCw_TransactionContext
	 */
	private function getTransactionContext(BarclaycardCw_Transaction $transaction, $order, $aliasTransactionId = NULL){
		return new BarclaycardCw_TransactionContext($transaction, $order, new BarclaycardCw_PaymentMethodWrapper($this), 
				$aliasTransactionId);
	}

	public function getAdapterFactory(){
		return BarclaycardCwUtil::getAuthorizationAdapterFactory();
	}
	
	
	public function scheduledSubscriptionPayment($amountToCharge, $order, $productId){
		global $barclaycardcw_recurring_process_failure;
		$barclaycardcw_recurring_process_failure = NULL;
		try {
			$adapter = $this->getAdapterFactory()->getAdapterByAuthorizationMethod(
					Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME);
			
			$dbTransaction = $this->newDatabaseTransaction($order);
			$transactionContext = new BarclaycardCw_RecurringTransactionContext($dbTransaction, $order, $this, $amountToCharge, $productId);
			$transaction = $adapter->createTransaction($transactionContext);
			$dbTransaction->setTransactionObject($transaction);
			BarclaycardCwUtil::getEntityManager()->persist($dbTransaction);
			$adapter->process($transaction);
			BarclaycardCwUtil::getEntityManager()->persist($dbTransaction);
			
			if (!$transaction->isAuthorized()) {
				$message = current($transaction->getErrorMessages());
				throw new Exception($message);
			}
			
			WC_Subscriptions_Manager::process_subscription_payments_on_order($order);
		}
		catch (Exception $e) {
			$errorMessage = __('Subscription Payment Failed with error:', 'woocommerce_barclaycardcw') . $e->getMessage();
			$barclaycardcw_recurring_process_failure = $errorMessage;
			$order->add_order_note($errorMessage);
			WC_Subscriptions_Manager::process_subscription_payment_failure_on_order($order, $product_id);
		}
	}
	
	

	/**
	 * This method generates a HTML form for each payment method.
	 */
	public function createMethodFormFields(){
		return array(
			'enabled' => array(
				'title' => __('Enable/Disable', 'woocommerce_barclaycardcw'),
				'type' => 'checkbox',
				'label' => sprintf(__('Enable %s', 'woocommerce_barclaycardcw'), $this->admin_title),
				'default' => 'no' 
			),
			'title' => array(
				'title' => __('Title', 'woocommerce_barclaycardcw'),
				'type' => 'text',
				'description' => __('This controls the title which the user sees during checkout.', 'woocommerce_barclaycardcw'),
				'default' => __($this->title, 'woocommerce_barclaycardcw') 
			),
			'description' => array(
				'title' => __('Description', 'woocommerce_barclaycardcw'),
				'type' => 'textarea',
				'description' => __('This controls the description which the user sees during checkout.', 'woocommerce_barclaycardcw'),
				'default' => sprintf(
						__("Pay with %s over the interface of Barclaycard.", 'woocommerce_barclaycardcw'), 
						$this->title) 
			),
			'min_total' => array(
				'title' => __('Minimal Order Total', 'woocommerce_barclaycardcw'),
				'type' => 'text',
				'description' => __(
						'Set here the minimal order total for which this payment method is available. If it is set to zero, it is always available.', 
						'woocommerce_barclaycardcw'),
				'default' => 0 
			),
			'max_total' => array(
				'title' => __('Maximal Order Total', 'woocommerce_barclaycardcw'),
				'type' => 'text',
				'description' => __(
						'Set here the maximal order total for which this payment method is available. If it is set to zero, it is always available.', 
						'woocommerce_barclaycardcw'),
				'default' => 0 
			) 
		);
	}

	protected function getOrderStatusOptions($statuses = array()){
		$terms = get_terms('shop_order_status', array(
			'hide_empty' => 0,
			'orderby' => 'id' 
		));
		
		foreach ($statuses as $k => $value) {
			$statuses[$k] = __($value, 'woocommerce_barclaycardcw');
		}
		
		foreach ($terms as $term) {
			$statuses[$term->slug] = $term->name;
		}
		return $statuses;
	}
}
