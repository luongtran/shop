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

require_once 'Customweb/Form/Control/IEditableControl.php';
require_once 'Customweb/Form/Control/Abstract.php';


/**
 * @author Thomas Hunziker
 */
abstract class Customweb_Form_Control_AbstractEditable extends Customweb_Form_Control_Abstract implements Customweb_Form_Control_IEditableControl {
	
	public function getFormDataValue(array $formData) {
		$value = $formData;
		foreach ($this->getControlNameAsArray() as $name) {
			if (!isset($value[$name])) {
				return null;
			}
			$value = $value[$name];
		}
		return $value;
	}
	
}