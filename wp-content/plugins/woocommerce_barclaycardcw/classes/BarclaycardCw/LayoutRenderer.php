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

require_once 'Customweb/Mvc/Layout/IRenderer.php';



class BarclaycardCw_LayoutRenderer implements Customweb_Mvc_Layout_IRenderer{
	
	public function render(Customweb_Mvc_Layout_IRenderContext $context) {

		ob_start();
		get_header();
		$title = $context->getTitle();
		
		echo '<div id="main-content" class="main-content">
				<div id="primary" class="content-area">
				<div id="content" class="site-content" role="main">';
		
		if(!empty($title)) {
			echo '<h1>'.$title.'</h1>';
		}

		echo $context->getMainContent();

		echo '</div>
				</div>
				</div>';
		get_sidebar();
		get_footer();
		
		
		$content = ob_get_contents();
		ob_end_clean();
		
		return $content;
	}

} 