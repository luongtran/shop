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

require_once 'Customweb/Form/Validator/Abstract.php';
require_once 'Customweb/Form/Validator/IValidator.php';

/**
 * This validator implemention provides methods to check 
 * whether the user enter some data or not.
 * 
 * @author Nico Eigenmann
 *
 */
 
class Customweb_Form_Validator_OneNotEmptyLast extends Customweb_Form_Validator_Abstract implements Customweb_Form_Validator_IValidator {


	private $jsVariableName;
	
	public function __construct($control, $errorMessage, $jsVariableName){
		parent::__construct($control, $errorMessage);
		$this->jsVariableName = $jsVariableName;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Customweb_Form_Validator_Abstract::getValidationCondition()
	 */
	public function getValidationCondition() {
		return '(element.value != "" && element.value != "none") || window.'.$this->jsVariableName.' != ""';
	}
	
}