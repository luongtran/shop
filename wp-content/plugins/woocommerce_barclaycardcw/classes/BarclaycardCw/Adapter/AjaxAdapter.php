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
class BarclaycardCw_Adapter_AjaxAdapter extends BarclaycardCw_Adapter_AbstractAdapter
{
	
	public function getPaymentAdapterInterfaceName() {
		return 'Customweb_Payment_Authorization_Ajax_IAdapter';
	}
	
	/**
	 * @return Customweb_Payment_Authorization_Ajax_IAdapter
	 */
	public function getInterfaceAdapter() {
		return parent::getInterfaceAdapter();
	}
	
	public function getCheckoutFormVaiables(BarclaycardCw_Transaction $dbTransaction, $failedTransaction) {
		
		$transaction = $dbTransaction->getTransactionObject();
		$errorMessage= '';
		if($failedTransaction !== null) {
			$allErrorMessages =$failedTransaction->getErrorMessages();
			$errorMessage = current($allErrorMessages);
		}
		$ajaxScriptUrl = $this->getInterfaceAdapter()->getAjaxFileUrl($transaction);
		$callbackFunction = $this->getInterfaceAdapter()->getJavaScriptCallbackFunction($transaction);
		$visibleFormFields = $this->getInterfaceAdapter()->getVisibleFormFields(
			$transaction->getTransactionContext()->getOrderContext(), 
			$transaction->getTransactionContext()->getAlias(),
			$failedTransaction,
			$dbTransaction->getTransactionObject()->getTransactionContext()->getPaymentCustomerContext()
		);
		BarclaycardCwUtil::getEntityManager()->persist($dbTransaction);
		
		if (isset($_REQUEST['barclaycardcw-ajax-authorization'])) {
			return array(
				'result' => 'success',
				'ajaxScriptUrl' => $ajaxScriptUrl,
				'submitCallbackFunction' => $callbackFunction,
			);
		}
		
		
		$html = '';
		if ($visibleFormFields !== null && count($visibleFormFields) > 0) {
			$renderer = new Customweb_Form_Renderer();
			$renderer->setCssClassPrefix('barclaycardcw-');
			$html = $renderer->renderElements($visibleFormFields);
		}
		
		return array(
			'visible_fields' => $html,
			'ajaxScriptUrl' => $ajaxScriptUrl,
			'submitCallbackFunction' => $callbackFunction,
			'template_file' => 'payment_confirmation_ajax',
			'error_message' => $errorMessage,
		);
	}
	
	
	public function getReviewFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction) {
		require_once 'Customweb/Licensing/BarclaycardCw/License.php';
		$arguments = array(
			'aliasTransaction' => $aliasTransaction,
 			'orderContext' => $orderContext,
 		);
		return Customweb_Licensing_BarclaycardCw_License::run('mu7aj45dqu833acv', $this, $arguments);
	}

	public function call_9k73g0paojhu83db() {
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