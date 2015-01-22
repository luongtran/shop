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
BarclaycardCwUtil::bootstrap();

library_load_class_by_name('Customweb_Util_Url');
library_load_class_by_name('Customweb_DependencyInjection_Bean_Provider_Editable');
library_load_class_by_name('Customweb_DependencyInjection_Bean_Provider_Annotation');
library_load_class_by_name('Customweb_DependencyInjection_Container_Default');
library_load_class_by_name('Customweb_Payment_Authorization_IAdapterFactory');
library_load_class_by_name('Customweb_Util_Html');
library_load_class_by_name('Customweb_Storage_Backend_Database');
library_load_class_by_name('Customweb_Database_Util');
library_load_class_by_name('Customweb_Cache_Backend_Memory');
library_load_class_by_name('Customweb_Database_Migration_Manager');
library_load_class_by_name('Customweb_Core_Http_ContextRequest');
library_load_class_by_name('Customweb_Payment_Authorization_DefaultPaymentCustomerContext');
library_load_class_by_name('Customweb_Asset_Resolver_Simple');
library_load_class_by_name('Customweb_Asset_Resolver_Composite');
library_load_class_by_name('Customweb_Core_Url');
library_load_class_by_name('Customweb_Payment_SettingHandler');

class BarclaycardCwUtil {

	private function __construct(){}
	private static $methods = array();
	private static $basePath = NULL;
	private static $container = null;
	private static $entityManager = null;
	private static $driver = null;
	private static $paymentCustomerContexts = array();

	/**
	 * This method loads a order.
	 *
	 * @param integer $orderId
	 * @return Order Object
	 */
	public static function loadOrderObjectById($orderId){
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '2.0.0') >= 0 && class_exists('WC_Order')) {
			return new WC_Order($orderId);
		}
		else {
			return new woocommerce_order($orderId);
		}
	}

	public static function bootstrap(){
		set_include_path(implode(PATH_SEPARATOR, array(
			get_include_path(),
			realpath(dirname(__FILE__) . '/classes/') 
		)));
		
		require_once dirname(__FILE__) . '/lib/loader.php';
	}

	public static function includeClass($class){
		//	$classFile = str_replace('BarclaycardCw_', '', $class);
		$classFile = str_replace('_', '/', $class);
		require_once self::getBasePath() . '/classes/' . $classFile . '.php';
	}

	/**
	 * This method returns the base bath to the plugin.
	 *
	 * @return string Base Path
	 */
	public static function getBasePath(){
		if (self::$basePath === NULL) {
			self::$basePath = dirname(__FILE__);
		}
		return self::$basePath;
	}

	public static function addPaymentMethods($gateways = array()){
		$methods = self::getPaymentMethods();
		foreach ($methods as $class_name) {
			$gateways[] = $class_name;
		}
		return $gateways;
	}

	public static function getPaymentMethods($includClass = true){
		if (count(self::$methods) <= 0) {
			if ($handle = opendir(self::getBasePath() . '/payment_methods')) {
				while (false !== ($file = readdir($handle))) {
					if (!is_dir(self::getBasePath() . '/' . $file) && $file !== '.' && $file !== '..' && substr($file, -4, 4) == '.php') {
						$class_name = substr($file, 0, -4);
						self::$methods[] = $class_name;
					}
				}
				closedir($handle);
			}
		}
		
		if ($includClass) {
			foreach (self::$methods as $method) {
				self::includePaymentMethod($method);
			}
		}
		return self::$methods;
	}

	public static function includePaymentMethod($methodClassName){
		$methodClassName = strip_tags($methodClassName);
		if (!class_exists($methodClassName)) {
			$methods = self::getPaymentMethods(false);
			$fileName = self::getBasePath() . '/payment_methods/' . $methodClassName . '.php';
			if (!file_exists($fileName)) {
				throw new Exception(
						"The payment method class could not be included, because it was not found. Payment Method Name: '" . $methodClassName .
								 "' File Path: " . $fileName);
			}
			require_once $fileName;
		}
	}

	/**
	 *
	 * @param string $methodClassName
	 * @return BarclaycardCw_PaymentMethod
	 */
	public static function getPaymentMehtodInstance($methodClassName){
		self::includePaymentMethod($methodClassName);
		return new $methodClassName();
	}

	public static function getPluginUrl($file, array $params = array()){
		if (isset($_REQUEST['wpml-lang'])) {
			$params['wpml-lang'] = $_REQUEST['wpml-lang'];
		}
		else if (defined('ICL_LANGUAGE_CODE')) {
			$params['wpml-lang'] = ICL_LANGUAGE_CODE;
		}
		else if(function_exists('wpml_get_current_language')) {
			$params['wpml-lang'] = wpml_get_current_language();
		}
		$url = plugins_url($file, __FILE__);
		$complete = Customweb_Util_Url::appendParameters($url, $params);
		
		return apply_filters('woocommerce_barclaycardcw_plugin_url', $complete, $url, $params);
		
	}

	public static function getResourcesUrl($path){
		return plugins_url(null, __FILE__) . '/resources/' . $path;
	}

	public static function installPlugin(){
		global $wpdb;
		$manager = new Customweb_Database_Migration_Manager(self::getDriver(), dirname(__FILE__) . '/classes/BarclaycardCw/Migration/', 
				$wpdb->prefix . 'woocommerce_barclaycardcw_schema_version');
		$manager->migrate();
	}

	public static function renderHiddenFields($fields){
		return Customweb_Util_Html::buildHiddenInputFields($fields);
	}

	public static function getTemplateFile($templateName){
		$templates = array();
		$templates[] = $templateName;
		return get_query_template('barclaycardcw', $templates);
	}

	public static function includeTemplateFile($templateName, $variables = array()){
		if (empty($templateName)) {
			throw new Exception("The given template name is empty.");
		}
		
		$templateName = 'barclaycardcw_' . $templateName;
		
		$templates = self::getTemplateFile($templateName);
		$template = apply_filters('template_include', $templates);
		extract($variables);
		if (!empty($template)) {
			require_once $template;
		}
		else {
			require_once self::getBasePath() . '/theme/' . $templateName . '.php';
		}
	}

	/**
	 * This action is executed, when the form is rendered.
	 *
	 * @param WC_Checkout $checkout
	 */
	public static function actionBeforeCheckoutBillingForm(WC_Checkout $checkout){
		self::includeClass('BarclaycardCw_ConfigurationAdapter');
		
		if (BarclaycardCw_ConfigurationAdapter::isReviewFormInputActive()) {
			$fieldsToForceUpdate = array(
				'billing_first_name',
				'billing_last_name',
				'billing_company',
				'billing_email',
				'billing_phone' 
			);
			$checkout->checkout_fields['billing'] = self::addCssClassToForceAjaxReload($checkout->checkout_fields['billing'], $fieldsToForceUpdate);
		}
	}

	/**
	 * This action is executed, when the form is rendered.
	 *
	 * @param WC_Checkout $checkout
	 */
	public static function actionBeforeCheckoutShippingForm(WC_Checkout $checkout){
		self::includeClass('BarclaycardCw_ConfigurationAdapter');
		
		if (BarclaycardCw_ConfigurationAdapter::isReviewFormInputActive()) {
			$fieldsToForceUpdate = array(
				'shipping_first_name',
				'shipping_last_name',
				'shipping_company' 
			);
			$checkout->checkout_fields['shipping'] = self::addCssClassToForceAjaxReload($checkout->checkout_fields['shipping'], $fieldsToForceUpdate);
		}
	}

	private static function addCssClassToForceAjaxReload($fields, $forceFields){
		foreach ($fields as $key => $data) {
			if (in_array($key, $forceFields)) {
				if (!in_array('address-field', $data['class'])) {
					$fields[$key]['class'][] = 'address-field';
				}
			}
		}
		
		return $fields;
	}

	public static function getBackendOperationAdapterFactory(){
		throw new Exception('Not supported anymore');
	}

	/**
	 *
	 * @return Customweb_DependencyInjection_Container_Default
	 */
	public static function createContainer(){
		self::includeClass('BarclaycardCw_LayoutRenderer');
			
		if (self::$container === null) {
			$packages = array(
			0 => 'Customweb_Barclaycard',
 			1 => 'Customweb_Payment_Authorization',
 		);
			$packages[] = 'BarclaycardCw_';
			$packages[] = 'Customweb_Mvc_Template_Php_Renderer';
			$packages[] = 'Customweb_Payment_Update_ContainerHandler';
			$packages[] = 'Customweb_Payment_TransactionHandler';
			$packages[] = 'Customweb_Payment_SettingHandler';
			$provider = new Customweb_DependencyInjection_Bean_Provider_Editable(new Customweb_DependencyInjection_Bean_Provider_Annotation($packages));
			$storage = new Customweb_Storage_Backend_Database(self::getEntityManager(), self::getDriver(), 'BarclaycardCw_Storage');
			$provider->addObject(Customweb_Core_Http_ContextRequest::getInstance())->addObject(self::getEntityManager())->addObject(self::getDriver())->addObject(
					new BarclaycardCw_LayoutRenderer())->addObject(new Customweb_Cache_Backend_Memory())->add('databaseTransactionClassName', 
					'BarclaycardCw_Transaction')->addObject(self::getAssetResolver())->addObject($storage);
			
			self::$container = new Customweb_DependencyInjection_Container_Default($provider);
		}
		
		return self::$container;
	}

	/**
	 *
	 * @return Customweb_Database_Entity_Manager
	 */
	public static function getEntityManager(){
		self::includeClass("BarclaycardCw_EntityManager");
		if (self::$entityManager === null) {
			$cache = new Customweb_Cache_Backend_Memory();
			self::$entityManager = new BarclaycardCw_EntityManager(self::getDriver(), $cache);
		}
		return self::$entityManager;
	}

	public static function getAssetResolver(){
		$simple = array();
		$simple[] = new Customweb_Asset_Resolver_Simple(self::getBasePath() . '/assets/', null, 
				array(
					'application/x-smarty',
					'application/x-twig',
					'application/x-phtml' 
				));
		$simple[] = new Customweb_Asset_Resolver_Simple(self::getBasePath() . '/assets/', plugins_url(null, __FILE__) . '/assets/');
		return new Customweb_Asset_Resolver_Composite($simple);
	}

	/**
	 *
	 * @return BarclaycardCw_Database_Driver
	 */
	public static function getDriver(){
		self::includeClass('BarclaycardCw_Database_Driver');
		if (self::$driver === null) {
			global $wpdb;
			self::$driver = new BarclaycardCw_Database_Driver($wpdb);
		}
		return self::$driver;
	}

	public static function getAuthorizationAdapterFactory(){
		$container = self::createContainer();
		$factory = $container->getBean('Customweb_Payment_Authorization_IAdapterFactory');
		
		if (!($factory instanceof Customweb_Payment_Authorization_IAdapterFactory)) {
			throw new Exception("The payment api has to provide a class which implements 'Customweb_Payment_Authorization_IAdapterFactory' as a bean.");
		}
		
		return $factory;
	}

	public static function getAuthorizationAdapter($authorizationMethodName){
		return self::getAuthorizationAdapterFactory()->getAuthorizationAdapterByName($authorizationMethodName);
	}

	public static function getAuthorizationAdapterByContext(Customweb_Payment_Authorization_IOrderContext $orderContext){
		return self::getAuthorizationAdapterFactory()->getAuthorizationAdapterByContext($orderContext);
	}

	/**
	 *
	 * @param Customweb_Payment_Authorization_IAdapter $paymentAdapter
	 * @throws Exception
	 * @return BarclaycardCw_Adapter_IAdapter
	 */
	public static function getShopAdapterByPaymentAdapter(Customweb_Payment_Authorization_IAdapter $paymentAdapter){
		$reflection = new ReflectionClass($paymentAdapter);
		$adapters = self::createContainer()->getBeansByType('BarclaycardCw_Adapter_IAdapter');
		foreach ($adapters as $adapter) {
			if ($adapter instanceof BarclaycardCw_Adapter_IAdapter) {
				$interface = $adapter->getPaymentAdapterInterfaceName();
				Customweb_Core_Util_Class::loadLibraryClassByName($interface);
				if ($reflection->implementsInterface($interface)) {
					$adapter->setInterfaceAdapter($paymentAdapter);
					return $adapter;
				}
			}
		}
		
		throw new Exception("Could not resolve to Shop adapter.");
	}

	/**
	 *
	 * @param int $customerId
	 * @return Customweb_Payment_Authorization_IPaymentCustomerContext
	 */
	public static function getPaymentCustomerContext($customerId){
		self::includeClass('BarclaycardCw_Entity_PaymentCustomerContext');
		// Handle guest context. This context is not stored.
		if ($customerId === null || $customerId == 0) {
			if (!isset(self::$paymentCustomerContexts['guestContext'])) {
				self::$paymentCustomerContexts['guestContext'] = new Customweb_Payment_Authorization_DefaultPaymentCustomerContext(array());
			}
			
			return self::$paymentCustomerContexts['guestContext'];
		}
		if (!isset(self::$paymentCustomerContexts[$customerId])) {
			$entities = self::getEntityManager()->searchByFilterName('BarclaycardCw_Entity_PaymentCustomerContext', 'loadByCustomerId', 
					array(
						'>customerId' => $customerId 
					));
			if (count($entities) > 0) {
				self::$paymentCustomerContexts[$customerId] = current($entities);
			}
			else {
				$context = new BarclaycardCw_Entity_PaymentCustomerContext();
				$context->setCustomerId($customerId);
				self::$paymentCustomerContexts[$customerId] = $context;
			}
		}
		return self::$paymentCustomerContexts[$customerId];
	}

	public static function persistPaymentCustomerContext(Customweb_Payment_Authorization_IPaymentCustomerContext $context){
		self::includeClass('BarclaycardCw_Entity_PaymentCustomerContext');
		if ($context instanceof BarclaycardCw_Entity_PaymentCustomerContext) {
			self::getEntityManager()->persist($context);
		}
	}

	/**
	 * This function has to echo the additional payment information received from the transaction object.
	 * This function has to check if the order was paid with this module.
	 *
	 * @param int $orderId woocommerce orderId
	 * @return void
	 */
	public static function thankYouPageHtml($orderId){
		$transactions = self::getTransactionsByOrderId($orderId);
		$transactionObject = null;
		foreach ($transactions as $transaction) {
			if ($transaction->getTransactionObject() !== null && $transaction->getTransactionObject()->isAuthorized()) {
				$transactionObject = $transaction->getTransactionObject();
				break;
			}
		}
		if ($transactionObject === null) {
			return;
		}
		$paymentInformation = $transactionObject->getPaymentInformation();
		if(!empty($paymentInformation)) {
			echo '<div class="woocommerce_barclaycardcw-payment-information" id="woocommerce_barclaycardcw-payment-information">';
			echo "<h2>" . __('Payment Information', 'woocommerce_barclaycardcw') . "</h2>";
			echo $transactionObject->getPaymentInformation();
			echo '</div>';
		}
	}

		/**
	 * This function has to echo the additional payment information received from the transaction object.
	 * This function has to check if the order was paid with this module.
	 * Do not send email to admin
	 *
	 * @param WC_Order $order
	 * @param boolean $sent_to_admin
	 * @param boolean $plain_text
	 * @return void
	 */
	public static function orderEmailHtml($order, $sent_to_admin, $plain_text = false){
		if ($sent_to_admin) {
			return;
		}
		$transactions = self::getTransactionsByOrderId($order->id);
		$transactionObject = null;
		foreach ($transactions as $transaction) {
			if ($transaction->getTransactionObject() !== null && $transaction->getTransactionObject()->isAuthorized()) {
				$transactionObject = $transaction->getTransactionObject();
				break;
			}
		}
		if ($transactionObject === null) {
			return;
		}
		$paymentInformation = $transactionObject->getPaymentInformation();
		if(!empty($paymentInformation)) {
			echo '<div class="woocommerce_barclaycardcw-email-payment-information" id="woocommerce_barclaycardcw-email-payment-information">';
			echo "<h2>" . __('Payment Information', 'woocommerce_barclaycardcw') . "</h2>";
			echo $transactionObject->getPaymentInformation();
			echo '</div>';
		}
	}

	/**
	 * Returns the transaction specified by the transactionId
	 *
	 * @param integer $id The transaction Id
	 * @param boolean $cache load from cache
	 * @return BarclaycardCw_Transaction The matching transactions for the given transaction id
	 */
	public static function getTransactionById($id, $cache = true){
		return self::getEntityManager()->fetch('BarclaycardCw_Transaction', $id, $cache);
	}

	/**
	 * Returns the transaction specified by the transaction number (externalId)
	 *
	 * @param integer $number The transactionNumber
	 * @param boolean $cache load from cache
	 * @return BarclaycardCw_Transaction The matching transactions for the given transactionNumber
	 */
	public static function getTransactionByTransactionNumber($number, $cache = true){
		$transactions = self::getEntityManager()->searchByFilterName('BarclaycardCw_Transaction', 'loadByExternalId', 
				array(
					'>transactionExternalId' => $number 
				), $cache);
		if (empty($transactions)) {
			throw new Exception("No transaction found, for the given transaction number: ." . $number);
		}
		return reset($transactions);
	}

	/**
	 * Return all transactions given by the order id
	 *
	 * @param integer $orderId The id of the order
	 * @param boolean $cache load from cache
	 * @return BarclaycardCw_Transaction[] The matching transactions for the given order id
	 */
	public static function getTransactionsByOrderId($orderId, $cache = true){
		class_exists('WC_Order');
		self::getPaymentMethods(true);
		return self::getEntityManager()->searchByFilterName('BarclaycardCw_Transaction', 'loadByOrderId', 
				array(
					'>orderId' => $orderId 
				), $cache);
	}

	public static function getInitialTransactionByOrderId($orderId){
		class_exists('WC_Order');
		$transactions = self::getTransactionsByOrderId($orderId);
		foreach ($transactions as $transaction) {
			if ($transaction->getTransactionObject()->isAuthorized()) {
				return $transaction;
			}
		}
		
		return NULL;
	}

	public static function getAliasTransactions($userId, $paymentMethod){
		if (empty($userId)) {
			return array();
		}
		
		$aliases = array();
		$entities = BarclaycardCwUtil::getEntityManager()->search('BarclaycardCw_Transaction', 
				'customerId LIKE >customerId AND LOWER(paymentMachineName) LIKE LOWER(>paymentMethod) AND aliasActive LIKE >active AND aliasForDisplay IS NOT NULL AND aliasForDisplay != ""', 
				'createdOn ASC', array(
					'>paymentMethod' => $paymentMethod,
					'>customerId' => $userId,
					'>active' => 'y' 
				));
		
		$knownAlias = array();
		foreach ($entities as $entity) {
			if (!in_array($entity->getAliasForDisplay(), $knownAlias) && $entity->getOrder() !== NULL) {
				$aliases[$entity->getTransactionId()] = $entity;
				$knownAlias[] = $entity->getAliasForDisplay();
			}
		}
		return $aliases;
	}

	public static function getShopOption($optionname){
		$option = get_option($optionname);
;
		return $option;
	}
}