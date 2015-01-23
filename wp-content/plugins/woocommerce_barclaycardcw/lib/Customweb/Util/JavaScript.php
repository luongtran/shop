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

require_once 'Customweb/Util/Rand.php';

/**
 * This Util provides some functionality to generate code to use jQuery.
 * 
 * @author Thomas Hunzik
 *
 */
final class Customweb_Util_JavaScript {

	private function __construct() {
		
	}
	
	/**
	 * This method returns a JavaScript snipped which loads jQuery library. The process checks if the given 
	 * version is already present, then the library is not loaded again. In any case the jQuery is linked to
	 * the given variable name. Which allows the loading in "noConflict" mode. This makes it more easy to 
	 * load jQuery in multiple versions and no conflicts with other libraries should occur.
	 * 
	 * When the library is loaded the callback function is called. The callback function is either a named
	 * function or better an anonymous function. The anonymous function must be formulated as following:
	 *    <code>
	 *    function() { 
	 *       alert("this code is executed");
	 *    }
	 *    </code>
	 * 
	 * @param string $versionNumber The version number to be used.
	 * @param string $jqueryVariableName The variable name into which jQuery will be loaded into.
	 * @param string $onLoadCallbackFunction The callback function, which is called when jQuery is loaded.
	 * @return string The resulting JavaScript code to perform the load process.
	 */
	public static function getLoadJQueryCode($versionNumber, $jqueryVariableName, $onLoadCallbackFunction) {
		
		$functionInvocationCode = $onLoadCallbackFunction . '();';
		$onLoadCallbackFunction = trim($onLoadCallbackFunction);
		if (strpos($onLoadCallbackFunction, 'function') === 0) {
			$functionInvocationCode = '(' . $onLoadCallbackFunction . ')();';
		}
		
		$globalJQueryVariableName = 'cwJquery' . md5($_SERVER['SERVER_NAME']) . str_replace('.', '_', $versionNumber);
		
		$js = '';
		$callbackAfterLoadFunctionName = 'a' . Customweb_Util_Rand::getRandomString(30);
		$js .= 'var ' . $jqueryVariableName . ' = "";';
		$js .= 'var ' . $callbackAfterLoadFunctionName . ' = function() { ' . $jqueryVariableName . ' = jQuery.noConflict( true ); window.' . $globalJQueryVariableName . ' = ' . $jqueryVariableName .'; ';
		
		$js .= $jqueryVariableName . '( document ).ready(function() { ';
		$js .= ' ' . $functionInvocationCode . ' ';
		$js .= ' }); };'. "\n";
		
		$js .= '{ function r(f){/in/.test(document.readyState)?setTimeout("r("+f+")",9):f()} r(function() {';
		$js .= 'if (typeof window.' . $globalJQueryVariableName . ' !== "undefined") {' . "\n";
			$js .= $jqueryVariableName . ' = window.' . $globalJQueryVariableName . ';';
			$js .= $jqueryVariableName . '( document ).ready(function() { ';
			$js .= ' ' . $functionInvocationCode . ' ';
			$js .= ' });'. "\n";
		$js .= '} else if (typeof jQuery === "undefined" || jQuery.fn.jquery !== \'' . $versionNumber . '\') {' . "\n";
			$js .= 'var script_tag = document.createElement(\'script\');' . "\n";
			$js .= 'script_tag.setAttribute("type", "text/javascript");' . "\n";
			$js .= 'script_tag.setAttribute("src", "//ajax.googleapis.com/ajax/libs/jquery/' . $versionNumber . '/jquery.min.js")' . "\n";
			$js .= 'script_tag.onload = ' . $callbackAfterLoadFunctionName . ';' . "\n";
			$js .= 'script_tag.onreadystatechange = function() { // IE hack' . "\n";
				$js .= 'if (this.readyState == \'complete\' || this.readyState == \'loaded\') {' . "\n";
					 $js .= $callbackAfterLoadFunctionName . "();\n";
				$js .= '}' . "\n";
			$js .= '}' . "\n";
			$js .= 'document.getElementsByTagName("head")[0].appendChild(script_tag);' . "\n";
		$js .= '} else {' . "\n";
			$js .= $jqueryVariableName . ' = jQuery;';
			$js .= $jqueryVariableName . '( document ).ready(function() { ';
			$js .= ' ' . $functionInvocationCode . ' ';
			$js .= ' });'. "\n";
		$js .= '}}); }' . "\n";
		
		return $js;
	}
	
	/**
	 * This method converts a given PHP array to JavaScript object.
	 * 
	 * @param array $array
	 * @return string
	 */
	public static function toJavaScript(array $array) {
		$elements = array();
		foreach ($array as $key => $value) {
			$element = $key . ':';
			if (is_array($value)) {
				$element .= self::toJavaScript($value);
			}
			else {
				if (is_object($value) && !method_exists($value, '__toString')) {
					throw new Exception(Customweb_I18n_Translation::__("Could not convert object of type '!type' to string.", array('!type' => get_class($value))));
				}
				$element .= '"' . str_replace('"', '\"', $value) . '"';
			}
			$elements[] = $element;
		}
		$output = '{' . implode(', ', $elements) . '}';
		
		return $output;
	}
	
}