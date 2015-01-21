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
class BarclaycardCw_Adapter_IframeAdapter extends BarclaycardCw_Adapter_AbstractAdapter
{
	
	public function getPaymentAdapterInterfaceName() {
		return 'Customweb_Payment_Authorization_Iframe_IAdapter';
	}
	
	/**
	 * @return Customweb_Payment_Authorization_Iframe_IAdapter
	 */
	public function getInterfaceAdapter() {
		return parent::getInterfaceAdapter();
	}
	
	public function getCheckoutFormVaiables(BarclaycardCw_Transaction $dbTransaction, $failedTransaction) {
		$transaction = $dbTransaction->getTransactionObject();
		$embeddedIframe = false;
		$visibleFormFields = array();
		$iframe_url = NULL;
		$formData = array();
		$iframeHeight = 1000;
		if (isset($_POST['iframeSubmit'])) {
			$embeddedIframe = true;
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
			$embeddedIframe = true;
		}
		
		if ($embeddedIframe) {
			$iframe_url = $this->getInterfaceAdapter()->getIframeUrl($transaction, $formData);
			$iframeHeight = $this->getInterfaceAdapter()->getIframeHeight($transaction, $formData);
		}
		BarclaycardCwUtil::getEntityManager()->persist($dbTransaction);
		
		$html = '';
		if ($visibleFormFields !== null && count($visibleFormFields) > 0) {
			$renderer = new Customweb_Form_Renderer();
			$renderer->setCssClassPrefix('barclaycardcw-');
			$html = $renderer->renderElements($visibleFormFields);
		}
		
		// TODO: May be use a different URL
		$formActionUrl = BarclaycardCwUtil::getPluginUrl("iframe.php", array('cw_transaction_id' => $dbTransaction->getTransactionExternalId()));
		
		$errorMessage = null;
		if($transaction->isAuthorizationFailed()) {
			$allErrorMessages =$transaction->getErrorMessages();
			$errorMessage = end($allErrorMessages);
		}
		
		return array(
			'error_message' => $errorMessage,
			'iframe_url' => $iframe_url,
			'form_target_url' => $formActionUrl,
			'visible_fields' => $html,
			'template_file' => 'payment_confirmation_iframe',
			'iframe_height' => $iframeHeight,
			'hidden_fields' => array('iframeSubmit' => 1),
		);
		
	}
	public function getReviewFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction) {
		require_once 'Customweb/Licensing/BarclaycardCw/License.php';
		$arguments = array(
			'aliasTransaction' => $aliasTransaction,
 			'orderContext' => $orderContext,
 		);
		return Customweb_Licensing_BarclaycardCw_License::run('82pna6i2v18tdi6s', $this, $arguments);
	}

	public function call_ffo9iarfqme3id4a() {
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