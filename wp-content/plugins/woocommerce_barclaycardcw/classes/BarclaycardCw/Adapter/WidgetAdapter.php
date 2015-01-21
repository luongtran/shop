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

require_once 'AbstractAdapter.php';

/**
 * @Bean
 */
class BarclaycardCw_Adapter_WidgetAdapter extends BarclaycardCw_Adapter_AbstractAdapter
{

	public function getPaymentAdapterInterfaceName() {
		return 'Customweb_Payment_Authorization_Widget_IAdapter';
	}

	/**
	 * @return Customweb_Payment_Authorization_Widget_IAdapter
	 */
	public function getInterfaceAdapter() {
		return parent::getInterfaceAdapter();
	}

	public function getCheckoutFormVaiables(BarclaycardCw_Transaction $dbTransaction, $failedTransaction) {
		$transaction = $dbTransaction->getTransactionObject();
		$embeddedWidget = false;
		$visibleFormFields = array();
		$widgetHtml = NULL;
		$formData = array();
		if (isset($_POST['widgetSubmit'])) {
			$embeddedWidget = true;
			$formData = $_POST;
		}
		else {
			$visibleFormFields = $this->getInterfaceAdapter()->getVisibleFormFields(
				$transaction->getTransactionContext()->getOrderContext(),
				$transaction->getTransactionContext()->getAlias(),
				$failedTransaction,
				$dbTransaction->getTransactionObject()->getTransactionContext()->getPaymentCustomerContext()
			);
		}

		if (count($visibleFormFields) <= 0) {
			$embeddedWidget = true;
		}

		if ($embeddedWidget) {
			$widgetHtml = $this->getInterfaceAdapter()->getWidgetHTML($transaction, $formData);
		}
		BarclaycardCwUtil::getEntityManager()->persist($dbTransaction);

		$html = '';
		if ($visibleFormFields !== null && count($visibleFormFields) > 0) {
			$renderer = new Customweb_Form_Renderer();
			$renderer->setCssClassPrefix('barclaycardcw-');
			$html = $renderer->renderElements($visibleFormFields);
		}

		// TODO: May be use a different URL
		$formActionUrl = BarclaycardCwUtil::getPluginUrl("cw_widget.php", array('cw_transaction_id' => $dbTransaction->getTransactionExternalId()));

		$errorMessage = null;
		if($transaction->isAuthorizationFailed()) {
			$allErrorMessages =$transaction->getErrorMessages();
			$errorMessage = current($allErrorMessages);
		}
		
		return array(
			'error_message' => $errorMessage,
			'widget_html' => $widgetHtml,
			'form_target_url' => $formActionUrl,
			'visible_fields' => $html,
			'template_file' => 'payment_confirmation_widget',
			'hidden_fields' => array('widgetSubmit' => 1),
		);

	}

	
	public function getReviewFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction) {
		require_once 'Customweb/Licensing/BarclaycardCw/License.php';
		$arguments = array(
			'aliasTransaction' => $aliasTransaction,
 			'orderContext' => $orderContext,
 		);
		return Customweb_Licensing_BarclaycardCw_License::run('7pbht4djvs6h84ts', $this, $arguments);
	}

	public function call_j5dipe7o5seajmoq() {
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

}