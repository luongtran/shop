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
BarclaycardCwUtil::includeClass('BarclaycardCw_AbstractPaymentMethod');

/**
 *           	  				   		
 * This class handlers the main payment interaction with the
 * BarclaycardCw server.
 */
class BarclaycardCw_PaymentMethod extends BarclaycardCw_AbstractPaymentMethod {

	protected function getMethodSettings(){
		return array();
	}

	public function __construct(){
		$this->class_name = substr(get_class($this), 0, 39);
		
		$this->id = $this->class_name;
		$this->method_title = $this->admin_title;
		
		// Load the form fields.
		$this->form_fields = $this->createMethodFormFields();
		
		// Load the settings.
		$this->init_settings();
		
		parent::__construct();
		
		// Workaround: When some setting is stored all BarclaycardCw methods are
		// deactivated. With this check we allow the storage only in case the class
		// is called from the payment_gateways tab.
		if (stristr($_SERVER['QUERY_STRING'], 'tab=payment_gateways') || stristr($_SERVER['QUERY_STRING'], 'tab=checkout')) {
			if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '2.0.0') >= 0) {
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
					$this,
					'process_admin_options' 
				));
			}
			else {
				add_action('woocommerce_update_options', array(
					&$this,
					'process_admin_options' 
				));
			}
		}
		
		
		if ($this->getPaymentMethodConfigurationValue('enabled') == 'yes') {
			$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName(
					Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME);
			if ($adapter->isPaymentMethodSupportingRecurring($this)) {
				$this->supports = array(
					'subscriptions',
					'products',
					'subscription_cancellation',
					'subscription_reactivation',
					'subscription_suspension',
					'subscription_amount_changes',
					'subscription_date_changes',
					'product_variation' 
				);
			}
		}
		add_action('scheduled_subscription_payment_' . $this->id, array(
			&$this,
			'scheduledSubscriptionPayment' 
		), 10, 3);
		
	}

	public function getPaymentMethodConfigurationValue($key, $languageCode = null){
		$settingsArray = $this->getMethodSettings();
		
		if (isset($settingsArray[$key]) && $settingsArray[$key]['cwType'] == 'file') {
			$value = $this->settings[$key];
			if (isset($value['path']) && file_exists($value['path'])) {
				return new Customweb_Core_Stream_Input_File($value['path']);
			}
			else {
				$resolver = BarclaycardCwUtil::getAssetResolver();
				if (!empty($value)) {
					return $resolver->resolveAssetStream($value);
				}
			}
		}
		elseif (isset($settingsArray[$key]) && $settingsArray[$key]['cwType'] == 'multiselect') {
			$value = $this->settings[$key];
			if (empty($value)) {
				return array();
			}
		}
		
		return $this->settings[$key];
	}

	public function existsPaymentMethodConfigurationValue($key, $languageCode = null){
		if (isset($this->settings[$key])) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Generate the HTML output for the settings form.
	 */
	public function admin_options(){
		$output = '<h3>' . __($this->admin_title, 'woocommerce_barclaycardcw') . '</h3>';
		$output .= '<p>' . $this->getBackendDescription() . '</p>';
		
		$output .= '<table class="form-table">';
		
		echo $output;
		
		$this->generate_settings_html();
		
		echo '</table>';
	}

	function generate_select_html($key, $data){
		// We need to override this method, because we need to get
		// the order status, after we defined the form fields. The
		// terms are not accessible before.
		if (isset($data['is_order_status']) && $data['is_order_status'] == true) {
			if (isset($data['options']) && is_array($data['options'])) {
				$data['options'] = $this->getOrderStatusOptions($data['options']);
			}
			else {
				$data['options'] = $this->getOrderStatusOptions();
			}
		}
		return parent::generate_select_html($key, $data);
	}
	
	
	public function scheduledSubscriptionPayment($amountToCharge, $order, $productId){
		global $barclaycardcw_recurring_process_failure;
		$barclaycardcw_recurring_process_failure = NULL;
		try {
			$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName(
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
	
	public function process_admin_options(){
		global $woocommerce_barclaycardcw_isProcesssing;
		if ($woocommerce_barclaycardcw_isProcesssing) {
			return true;
		}
		$woocommerce_barclaycardcw_isProcesssing = true;
		return parent::process_admin_options();
	}

	public function validate_file_field($key){
		$value = $this->get_option($key);
		$settingsArray = $this->getMethodSettings();
		$setting = $settingsArray[$key];
		
		$filename = get_class($this) . '_' . $key;
		$fieldName = 'woocommerce_' . get_class($this) . '_' . $key;
		$parsedBody = Customweb_Core_Http_ContextRequest::getInstance()->getParsedBody();
		
		if (isset($parsedBody[$fieldName . '_reset']) && $parsedBody[$fieldName . '_reset'] == 'reset') {
			return $setting['default'];
		}
		
		if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] != 0) {
			return $value;
		}
		$upload_dir = wp_upload_dir();
		$name = basename($_FILES[$fieldName]['name']);
		
		$fileExtension = pathinfo($name, PATHINFO_EXTENSION);
		if (!file_exists($upload_dir['basedir'] . '/woocommerce_barclaycardcw')) {
			$oldmask = umask(0);
			mkdir($upload_dir['basedir'] . '/woocommerce_barclaycardcw', 0777, true);
			umask($oldmask);
		}
		$allowedFileExtensions = $setting['allowedFileExtensions'];
		
		if (!empty($allowedFileExtensions) && !in_array($fileExtension, $allowedFileExtensions)) {
			woocommerce_barclaycardcw_admin_show_message(
					'Only the following file extensions are allowed for setting "' . $setting['title'] . '": ' . implode(', ', $allowedFileExtensions), 
					'error');
			return $value;
		}
		$targetPath = $upload_dir['basedir'] . '/woocommerce_barclaycardcw/' . $filename . '.' . $fileExtension;
		$rs = move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath);
		if ($rs) {
			chmod($targetPath, 0777);
			return array(
				'name' => $name,
				'path' => $targetPath 
			);
		}
		else {
			woocommerce_barclaycardcw_admin_show_message('Unable to upload file for setting "' . $setting['title'] . '".', 'error');
			return $value;
		}
	}

	public function generate_file_html($key, $data){
		$field = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'title' => '',
			'disabled' => false,
			'class' => '',
			'css' => '',
			'placeholder' => '',
			'type' => 'file',
			'desc_tip' => false,
			'description' => '',
			'custom_attributes' => array() 
		);
		
		$data = wp_parse_args($data, $defaults);
		
		ob_start();
		?>
<tr valign="top">
	<th scope="row" class="titledesc"><label
		for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
					<?php echo $this->get_tooltip_html( $data ); ?>
				</th>
	<td class="forminp">
					<?php
		
		$value = $this->get_option($key);
		if (isset($value['name'])) {
			$filename = $value['name'];
		}
		else {
			
			$filename = $value;
		}
		echo __('Current File: ', 'woocommerce_barclaycardcw') . esc_attr($filename);
		?><br />
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php echo wp_kses_post( $data['title'] ); ?></span>
			</legend>
			<input class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?> />
		</fieldset> <input type="checkbox"
		name="<?php echo esc_attr( $field.'_reset' ); ?>" value="reset" /><?php echo __('Reset', 'woocommerce_barclaycardcw'); ?><br />
	</td>
</tr>
<?php
		return ob_get_clean();
	}

	protected function getOrderStatusOptions($statuses = array()){
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '2.2.0') >= 0) {
			$orderStatuses = wc_get_order_statuses();
			foreach ($statuses as $k => $value) {
				$orderStatuses[$k] = __($value, 'woocommerce_barclaycardcw');
			}
			return $orderStatuses;
		}
		else {
			return parent::getOrderStatusOptions($statuses);
		}
	}
}
