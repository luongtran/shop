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

<h2><?php print __('Redirection', 'woocommerce_barclaycardcw'); ?>: <?php print $paymentMethodName; ?></h2>
	
<form action="<?php print $form_target_url; ?>" method="POST" name="process_form">
	
	<?php foreach ($hidden_fields as $field_name => $field_value): ?>
		<?php if (is_array($field_value)): ?>
			<?php foreach ($field_value as $value): ?>
				<input type="hidden" name="<?php print $field_name; ?>[]" value="<?php print $value; ?>" />
			<?php endforeach; ?>
		<?php else: ?>
			<input type="hidden" name="<?php print $field_name; ?>" value="<?php print $field_value; ?>" />
		<?php endif; ?>
	<?php endforeach; ?>

	<input class="button" type="submit" name="continue_button" value="<?php print __('Continue', 'woocommerce_barclaycardcw'); ?>" />

</form>
<script type="text/javascript"> 
jQuery(document).ready(function() {
	document.process_form.submit(); 
});
</script>
<?php 
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
get_sidebar();
get_footer();