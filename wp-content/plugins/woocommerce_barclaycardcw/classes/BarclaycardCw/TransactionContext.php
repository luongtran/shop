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

library_load_class_by_name('Customweb_Payment_Authorization_PaymentPage_ITransactionContext');
library_load_class_by_name('Customweb_Payment_Authorization_Hidden_ITransactionContext');
library_load_class_by_name('Customweb_Payment_Authorization_Server_ITransactionContext');
library_load_class_by_name('Customweb_Payment_Authorization_Iframe_ITransactionContext');
library_load_class_by_name('Customweb_Payment_Authorization_Widget_ITransactionContext');
library_load_class_by_name('Customweb_Payment_Authorization_DefaultInvoiceItem');
library_load_class_by_name('Customweb_Payment_Authorization_Ajax_ITransactionContext');

BarclaycardCwUtil::includeClass('BarclaycardCw_Entity_PaymentCustomerContext');
BarclaycardCwUtil::includeClass('BarclaycardCw_PaymentCustomerContext');
BarclaycardCwUtil::includeClass('BarclaycardCw_OrderContext');

class BarclaycardCw_TransactionContext implements Customweb_Payment_Authorization_PaymentPage_ITransactionContext,
Customweb_Payment_Authorization_Hidden_ITransactionContext, Customweb_Payment_Authorization_Server_ITransactionContext,
Customweb_Payment_Authorization_Iframe_ITransactionContext, Customweb_Payment_Authorization_Ajax_ITransactionContext,
Customweb_Payment_Authorization_Widget_ITransactionContext
{
	protected $capturingMode;
	protected $aliasTransactionId = NULL;
	protected $paymentCustomerContext = null;
	protected $orderContext;
	protected $databaseTransactionId = NULL;
	protected $userId = NULL;
	protected $notificationUrl = null;
	protected $successUrl = null;
	protected $failedUrl = null;
	protected $ifameBreakoutUrl = null;

	private $databaseTransaction = NULL;

	public function __construct(BarclaycardCw_Transaction $transaction, $order, $paymentMethod, $aliasTransactionId = NULL) {
		if (isset($order->customer_user)) {
			$this->userId = $order->customer_user;
		}
		else {
			$this->userId = $order->user_id;
		}
		$userId = intval($this->userId);

		$this->orderContext = new BarclaycardCw_OrderContext($order, $paymentMethod, $this->userId);
		$aliasTransactionIdCleaned = null;
		if ($paymentMethod->isAliasManagerActive() && $userId > 0) {
			if ($aliasTransactionId === NULL || $aliasTransactionId === 'new') {
				$aliasTransactionIdCleaned = 'new';
			}
			else {
				$aliasTransactionIdCleaned = intval($aliasTransactionId);
			}
		}
		$this->aliasTransactionId = $aliasTransactionIdCleaned;

		$this->paymentCustomerContext = BarclaycardCwUtil::getPaymentCustomerContext($this->userId);

		$this->databaseTransaction = $transaction;
		$this->databaseTransactionId = $transaction->getTransactionId();
		$this->notificationUrl = BarclaycardCwUtil::getPluginUrl('notification.php');
		$this->successUrl = BarclaycardCwUtil::getPluginUrl('success.php');
		$this->failedUrl = BarclaycardCwUtil::getPluginUrl('payment.php');
		$this->ifameBreakoutUrl = BarclaycardCwUtil::getPluginUrl('iframe_breakout.php');
	}

	/**
	 * @return BarclaycardCw_Transaction
	 */
	public function getDatabaseTransaction() {
		if ($this->databaseTransaction === NULL) {
			$this->databaseTransaction = BarclaycardCwUtil::getTransactionById($this->databaseTransactionId);
		}

		return $this->databaseTransaction;
	}

	public function getOrderId() {
		return $this->getDatabaseTransaction()->getOrderId();
	}
	
	public function isOrderIdUnique() {
		// TODO: Check if we may return true. The error handling may lead to a strange behavior when we use 'true'.
		return false;
	}

	public function __sleep() {
		return array('capturingMode', 'aliasTransactionId', 'paymentCustomerContext', 'orderContext', 'databaseTransactionId', 'userId', 'notificationUrl', 'successUrl', 'failedUrl', 'ifameBreakoutUrl');
	}

	public function getOrderContext() {
		return $this->orderContext;
	}

	public function getTransactionId() {
		return $this->getDatabaseTransaction()->getTransactionId();
	}

	public function getCapturingMode()
	{
		return null;
	}

	public function createRecurringAlias() {
		if ($this->getOrderContext()->isSubscription()) {
			return true;
		}
		else {
			return false;
		}
	}

	public function getAlias() {
		if ($this->aliasTransactionId === 'new') {
			return 'new';
		}

		if ($this->aliasTransactionId !== null && !empty($this->aliasTransactionId)) {
			$transcation = BarclaycardCwUtil::getTransactionById($this->aliasTransactionId);
			if ($transcation !== null && $transcation->getTransactionObject() !== null && $transcation->getCustomerId() == $this->userId) {
				return $transcation->getTransactionObject();
			}
		}

		return null;
	}

	public function getCustomParameters() {
		$params = array(
			'cw_transaction_id' => $this->getDatabaseTransaction()->getTransactionExternalId(),
		);

		$params = apply_filters('barclaycardcw_custom_parameters', $params);
		return $params;
	}

	public function getSuccessUrl() {
		return $this->successUrl;
	}

	public function getFailedUrl() {
		return $this->failedUrl;
	}

	public function getPaymentCustomerContext() {
		return $this->paymentCustomerContext;
	}

	public function getNotificationUrl() {
		return $this->notificationUrl;
	}

	public function getIframeBreakOutUrl() {
		return $this->ifameBreakoutUrl;
	}


	public function getJavaScriptSuccessCallbackFunction() {
		return '
		function (redirectUrl) {
			window.location = redirectUrl
		}';
	}

	public function getJavaScriptFailedCallbackFunction() {
		return '
		function (redirectUrl) {
			window.location = redirectUrl
		}';
	}


}