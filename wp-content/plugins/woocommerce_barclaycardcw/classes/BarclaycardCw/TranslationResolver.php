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

library_load_class_by_name('Customweb_I18n_ITranslationResolver');
library_load_class_by_name('Customweb_I18n_Translation');

class BarclaycardCw_TranslationResolver implements Customweb_I18n_ITranslationResolver {
	public function getTranslation($string) {
		$rs = __($string, 'woocommerce_barclaycardcw');
		if ($rs === $string) {
			return null;
		}
		else {
			return $rs;
		}
	}
}

// Replace the default resolver           	  				   		
Customweb_I18n_Translation::getInstance()->addResolver(new BarclaycardCw_TranslationResolver());
