<?php
/**
 * * You are allowed to use this API in your web application.
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

require_once 'Customweb/Core/Stream/Input/File.php';
require_once 'Customweb/Payment/IConfigurationAdapter.php';


/**
 *
 */
abstract class BarclaycardCw_AbstractConfigurationAdapter implements Customweb_Payment_IConfigurationAdapter
{
	
	protected $settingsMap=array(
		'operation_mode' => array(
			'id' => 'ogone-operation-mode-setting',
 			'machineName' => 'operation_mode',
 			'type' => 'select',
 			'label' => 'Operation Mode',
 			'description' => 'If the test mode is selected the test PSPID is used and the test SHA passphrases.',
 			'defaultValue' => 'test',
 			'allowedFileExtensions' => array(
			),
 		),
 		'pspid' => array(
			'id' => 'ogone-pspid-setting',
 			'machineName' => 'pspid',
 			'type' => 'textfield',
 			'label' => 'Live PSPID',
 			'description' => 'The PSPID as given by the Barclaycard.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'test_pspid' => array(
			'id' => 'ogone-test-pspid-setting',
 			'machineName' => 'test_pspid',
 			'type' => 'textfield',
 			'label' => 'Test PSPID',
 			'description' => 'The test PSPID as given by the Barclaycard.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'live_sha_passphrase_in' => array(
			'id' => 'ogone-live-sha-passphrase-in',
 			'machineName' => 'live_sha_passphrase_in',
 			'type' => 'textfield',
 			'label' => 'SHA-IN Passphrase',
 			'description' => 'Enter the live SHA-IN passphrase. This value must be identical to the one in the back-end of Barclaycard.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'live_sha_passphrase_out' => array(
			'id' => 'ogone-live-sha-passphrase-out',
 			'machineName' => 'live_sha_passphrase_out',
 			'type' => 'textfield',
 			'label' => 'SHA-OUT Passphrase',
 			'description' => 'Enter the live SHA-OUT passphrase. This value must be identical to the one in the back-end of Barclaycard.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'test_sha_passphrase_in' => array(
			'id' => 'ogone-test-sha-passphrase-in',
 			'machineName' => 'test_sha_passphrase_in',
 			'type' => 'textfield',
 			'label' => 'Test Account SHA-IN Passphrase',
 			'description' => 'Enter the test SHA-IN passphrase. This value must be identical to the one in the back-end of Barclaycard.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'test_sha_passphrase_out' => array(
			'id' => 'ogone-test-sha-passphrase-out',
 			'machineName' => 'test_sha_passphrase_out',
 			'type' => 'textfield',
 			'label' => 'Test Account SHA-OUT Passphrase',
 			'description' => 'Enter the test SHA-OUT passphrase. This value must be identical to the one in the back-end of Barclaycard.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'hash_method' => array(
			'id' => 'ogone-hash-calculation-method',
 			'machineName' => 'hash_method',
 			'type' => 'select',
 			'label' => 'Hash calculation method',
 			'description' => 'Select the hash calculation method to use. This value must correspond with the selected value in the back-end of Barclaycard.',
 			'defaultValue' => 'sha512',
 			'allowedFileExtensions' => array(
			),
 		),
 		'order_id_schema' => array(
			'id' => 'ogone-order-id-schema-setting',
 			'machineName' => 'order_id_schema',
 			'type' => 'textfield',
 			'label' => 'Order prefix',
 			'description' => 'Here you can insert an order prefix. The prefix allows you to change the order number that is transmitted to Barclaycard. The prefix must contain the tag {id}. It will then be replaced by the order number (e.g. name_{id}).',
 			'defaultValue' => 'order_{id}',
 			'allowedFileExtensions' => array(
			),
 		),
 		'order_description_schema' => array(
			'id' => 'ogone-order-description-schema-setting',
 			'machineName' => 'order_description_schema',
 			'type' => 'textfield',
 			'label' => 'Order Description',
 			'description' => 'This parameter is sometimes transmitted to the acquirer (depending on the acquirer), in order to be shown on the account statements of the merchant or the customer. The prefix can contain the tag {id}. It will then be replaced by the order number (e.g. name {id}). (Payment Page only)',
 			'defaultValue' => 'Order {id}',
 			'allowedFileExtensions' => array(
			),
 		),
 		'template' => array(
			'id' => 'ogone-template-method',
 			'machineName' => 'template',
 			'type' => 'select',
 			'label' => 'Dynamic Template',
 			'description' => 'With the Dynamic Template you can design the layout of the payment page yourself. For the option \'Own template\' the URL to the template file must be entered into the following box.',
 			'defaultValue' => 'default',
 			'allowedFileExtensions' => array(
			),
 		),
 		'template_url' => array(
			'id' => 'ogone-template-url',
 			'machineName' => 'template_url',
 			'type' => 'textfield',
 			'label' => 'Template URL for own template',
 			'description' => 'The URL indicated here is rendered as Template. For this you must select option \'Use own template\'. The URL must point to an HTML page that contains the string \'$$$PAYMENT ZONE$$$\'. This part of the HTML file is replaced with the form for the credit card input.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'mobile_template_method' => array(
			'id' => 'ogone-mobile-template-method-setting',
 			'machineName' => 'mobile_template_method',
 			'type' => 'select',
 			'label' => 'Mobile Template',
 			'description' => 'With this option you can control which template is used for mobile devices. Per default the template for desktops is used. You can either use a own template (provided through the field below) or the customweb hosted template.',
 			'defaultValue' => 'default',
 			'allowedFileExtensions' => array(
			),
 		),
 		'mobile_template_url' => array(
			'id' => 'ogone-mobile-template-url',
 			'machineName' => 'mobile_template_url',
 			'type' => 'textfield',
 			'label' => 'Mobile Template URL',
 			'description' => 'The URL indicated here is used to rendered the template for mobile devices. This field is only considered when the option mobile template is set to \'Use own template\'.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'shop_id' => array(
			'id' => 'ogone-shop-id',
 			'machineName' => 'shop_id',
 			'type' => 'textfield',
 			'label' => 'Shop ID',
 			'description' => 'Here you can define a Shop ID. This is only necessary if you wish to operate several shops with one PSPID. In order to use this module, an additional module is required.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'api_user_id' => array(
			'id' => 'ogone-api-username',
 			'machineName' => 'api_user_id',
 			'type' => 'textfield',
 			'label' => 'API Username',
 			'description' => 'You can create an API username in the back-end of Barclaycard. The API user is necessary for the direct communication between the shop and the service of Barclaycard.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'api_password' => array(
			'id' => 'ogone-api-password',
 			'machineName' => 'api_password',
 			'type' => 'textfield',
 			'label' => 'API Password',
 			'description' => 'Password for the API user.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'alias_usage_message' => array(
			'id' => 'ogone-alias-usage-message',
 			'machineName' => 'alias_usage_message',
 			'type' => 'multilangfield',
 			'label' => 'Intended purpose of alias',
 			'description' => 'If the Alias Manager is used, the intended purpose is shown to the customer on the payment page. Through this the customer knows why his data is saved.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'transaction_updates' => array(
			'id' => 'ogone-update-pending-transactions-setting',
 			'machineName' => 'transaction_updates',
 			'type' => 'select',
 			'label' => 'Transaction Updates',
 			'description' => 'When the store is not available (network outage, server failure or any other outage), when the feedback of Barclaycard is sent, then the transaction state is not updated. Hence no order confirmation e-mail is sent and the order is not in the paid state. By activating the transaction update, such transactions can be authorized later over direct link. To use this feature the update service must be activated and the API username and the API password must be set.',
 			'defaultValue' => 'inactive',
 			'allowedFileExtensions' => array(
			),
 		),
 		'review_input_form' => array(
			'id' => 'woocommerce-input-form-in-review-pane-setting',
 			'machineName' => 'review_input_form',
 			'type' => 'select',
 			'label' => 'Review Input Form',
 			'description' => 'Should the input form for credit card data rendered in the review pane? To work the user must have JavaScript activated. In case the browser does not support JavaScript a fallback is provided. This feature is not supported by all payment methods.',
 			'defaultValue' => 'active',
 			'allowedFileExtensions' => array(
			),
 		),
 	);

	
	/**
	 * (non-PHPdoc)
	 * @see Customweb_Payment_IConfigurationAdapter::getConfigurationValue()
	 */
	public function getConfigurationValue($key, $languageCode = null) {

		$setting = $this->settingsMap[$key];
		$value =  get_option('woocommerce_barclaycardcw_' . $key, $setting['defaultValue']);
		
		if($setting['type'] == 'file') {
			if(isset($value['path']) && file_exists($value['path'])) {
				return new Customweb_Core_Stream_Input_File($value['path']);
			}
			else {
				$resolver = BarclaycardCwUtil::getAssetResolver();
				return $resolver->resolveAssetStream($setting['defaultValue']);
			}
		}
		else if($setting['type'] == 'multiselect') {
			if(empty($value)){
				return array();
			}
		}
		return $value;
	}
		
	public function existsConfiguration($key, $languageCode = null) {
		if ($languageCode !== null) {
			$languageCode = (string)$languageCode;
		}
		$value = get_option('woocommerce_barclaycardcw_' . $key, null);
		if ($value === null) {
			return false;
		}
		else {
			return true;
		}
	}
	
	
}