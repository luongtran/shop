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

get_header(); 

echo '<div id="main-content" class="main-content">';
echo '<div id="primary" class="content-area">';
echo '<div id="content" class="site-content" role="main">';
echo '<div class="woocommerce">';
?>
		<h1><?php echo __('Your Payment', 'woocommerce_barclaycardcw');?></h1>
		<p>
			<?php echo __('The payment could not be processed. However, it seems that the payment was successful nevertheless. Your order was created and can be seen on the order confirmation page. Please contact us to find out more about the status of the order.', 'woocommerce_barclaycardcw');?>
		</p>
		<p>
			<a href="<?php echo $successUrl; ?>" class="button"><?php print __('Continue to your the order confirmation', 'woocommerce_barclaycardcw'); ?></a>
		</p>

<?php
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>'; 
get_sidebar();
get_footer(); 

