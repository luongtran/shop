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
class BarclaycardCw_Adapter_HiddenAdapter extends BarclaycardCw_Adapter_AbstractAdapter
{
	
	public function getPaymentAdapterInterfaceName() {
		return 'Customweb_Payment_Authorization_Hidden_IAdapter';
	}
	
	/**
	 * @return Customweb_Payment_Authorization_Hidden_IAdapter
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
		$formActionUrl = $this->getInterfaceAdapter()->getFormActionUrl($transaction);
		$hiddenFields = $this->getInterfaceAdapter()->getHiddenFormFields($transaction);
		$visibleFormFields = $this->getInterfaceAdapter()->getVisibleFormFields(
			$transaction->getTransactionContext()->getOrderContext(), 
			$transaction->getTransactionContext()->getAlias(),
			$failedTransaction,
			$dbTransaction->getTransactionObject()->getTransactionContext()->getPaymentCustomerContext()
		);
		BarclaycardCwUtil::getEntityManager()->persist($dbTransaction);
		

		if (isset($_REQUEST['barclaycardcw-hidden-authorization'])) {
			return array(
				'result' => 'success',
				'hidden_form_fields' => BarclaycardCwUtil::renderHiddenFields($hiddenFields),
				'form_action_url' => $formActionUrl,
			);
		}
		
		
		$html = '';
		if ($visibleFormFields !== null && count($visibleFormFields) > 0) {
			$renderer = new Customweb_Form_Renderer();
			$renderer->setCssClassPrefix('barclaycardcw-');
			$html = $renderer->renderElements($visibleFormFields);
		}
		
		return array(
			'form_target_url' => $formActionUrl,
			'hidden_fields' => $hiddenFields,
			'visible_fields' => $html,
			'template_file' => 'payment_confirmation',
			'error_message' => $errorMessage,
		);
	}
	public function getReviewFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction) {
		require_once 'Customweb/Licensing/BarclaycardCw/License.php';
		$arguments = array(
			'aliasTransaction' => $aliasTransaction,
 			'orderContext' => $orderContext,
 		);
		return Customweb_Licensing_BarclaycardCw_License::run('u3bdat24ugc5e56j', $this, $arguments);
	}

	public function call_5lf8q873m1p8ep2d() {
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

