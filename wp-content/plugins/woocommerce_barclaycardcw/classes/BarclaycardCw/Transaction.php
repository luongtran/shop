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


library_load_class_by_name('Customweb_Payment_Entity_AbstractTransaction');
/**
 * This class represents a transaction.
 *
 * @author Thomas Hunziker
 *
 *
 * @Entity(tableName = 'woocommerce_barclaycardcw_transactions')
 */
class BarclaycardCw_Transaction extends Customweb_Payment_Entity_AbstractTransaction
{
	
	private $paymentClass;
	
	
	/**
	 * @Column(type = 'varchar')
	 */
	public function getPaymentClass(){
		return $this->paymentClass;
	}
	
	public function setPaymentClass($paymentClass) {
		$this->paymentClass = $paymentClass;
		return $this;
	}
	
	public function getOrder() {
		// We load the order object always fresh from the database, to make sure,
		// that no old status is shared between the different usages.
		return BarclaycardCwUtil::loadOrderObjectById($this->getOrderId());
	}
	
	
	public function onBeforeSave(Customweb_Database_Entity_IManager $entityManager) {
		$transactionObject = $this->getTransactionObject();
		// In case a order is associated with this transaction and the authorization failed, we have to cancel the order.
		if ($transactionObject !== null && $transactionObject instanceof Customweb_Payment_Authorization_ITransaction && $transactionObject->isAuthorizationFailed()) {
			$this->forceTransactionFailing();
		}
		return parent::onBeforeSave($entityManager);
	}
	

	protected function updateOrderStatus(Customweb_Database_Entity_IManager $entityManager, $orderStatus, $orderStatusSettingKey) {
		
		$order = $this->getOrder();
		$paymentMethod = BarclaycardCwUtil::getPaymentMehtodInstance($this->getPaymentClass());
		if($orderStatusSettingKey != 'status_authorized' || $paymentMethod->getPaymentMethodConfigurationValue('status_authorized') != 'use-default') {
			$order->update_status($orderStatus, __('Payment Notification', 'woocommerce_barclaycardcw'));
		}
		
	}
	
	protected function authorize(Customweb_Database_Entity_IManager $entityManager) {
		if ($this->getTransactionObject()->isAuthorized()) {
			// Ensure that the mail is send to the administrator
						if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '2.2.0') >= 0) {
				$this->getOrder()->update_status('wc-pending');
			}
			else {
				$this->getOrder()->update_status('pending');
			}
				
			// Mark the transaction as completed
			$this->getOrder()->payment_complete();
			
			
			if (class_exists('WC_Subscriptions_Order') && WC_Subscriptions_Order::order_contains_subscription($this->getOrderId())) {
				WC_Subscriptions_Manager::activate_subscriptions_for_order($this->getOrder());
			}
			
			$this->setAuthorizationStatus(Customweb_Payment_Authorization_ITransaction::AUTHORIZATION_STATUS_SUCCESSFUL);	

		}
	}

	protected function forceTransactionFailing() {
		$message = current($this->getTransactionObject()->getErrorMessages());
		$this->getOrder()->add_order_note(__('Error Message: ', 'woocommerce_barclaycardcw') . $message);
		$this->getOrder()->cancel_order();
		$this->setAuthorizationStatus(Customweb_Payment_Authorization_ITransaction::AUTHORIZATION_STATUS_FAILED);
	}
	
	protected function generateExternalTransactionId(Customweb_Database_Entity_IManager $entityManager) {
		return $this->generateExternalTransactionIdAppendOnlyWhenNeeded($entityManager);
	}

}

