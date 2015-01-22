<div id="barclaycardcw-payment-container">
	
	<?php if (isset($error_message) && !empty($error_message)): ?>
		<p class="payment-error woocommerce-error">
			<?php print $error_message; ?>
		</p>
	<?php else: ?>
	
	
		<?php if (empty($visible_fields)): ?>
			<iframe src="<?php echo $iframe_url; ?>"  style="height: <?php echo $iframe_height; ?>px;" class="barclaycardcw-iframe">
				
			</iframe>
		<?php else: ?>
		
			<form action="<?php echo $form_target_url; ?>" method="post" class="barclaycardcw-payment-form">
	
				<?php foreach ($hidden_fields as $field_name => $field_value): ?>
					<?php if (is_array($field_value)): ?>
						<?php foreach ($field_value as $value): ?>
							<input type="hidden" name="<?php print $field_name; ?>[]" value="<?php print $value; ?>" />
						<?php endforeach; ?>
					<?php else: ?>
						<input type="hidden" name="<?php print $field_name; ?>" value="<?php print $field_value; ?>" />
					<?php endif; ?>
				<?php endforeach; ?>
				
				<?php if (isset($visible_fields) && !empty($visible_fields)): ?>
					<fieldset>
						<h3><?php print $paymentMethod; ?></h3>
						<?php print $visible_fields; ?>
					</fieldset>
				<?php endif; ?>
							
				<input type="submit" class="button alt barclaycardcw-payment-form-confirm" name="submit" value="<?php print __("I confirm my payment", "woocommerce_barclaycardcw"); ?>" />
			</form>
		<?php endif; ?>
	<?php endif; ?>
</div>
<div id="barclaycardcw-back-to-checkout" class="barclaycardcw-back-to-checkout">
	<a href="<?php
		$option = BarclaycardCwUtil::getShopOption('woocommerce_checkout_page_id');
		echo get_permalink($option);
	
	?>" class="button"><?php print __("Change payment method", "woocommerce_barclaycardcw");?></a>
</div>
