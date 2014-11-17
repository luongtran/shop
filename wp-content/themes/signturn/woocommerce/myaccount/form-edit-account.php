<?php
/**
 * Edit account form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php wc_print_notices(); ?>

<form action="" method="post">

	<?php do_action( 'woocommerce_edit_account_form_start' ); ?>
    <div class="row" id="edit-account-wrapper">
        <div class="col-sm-6 no-padding-right">
            <fieldset id="edit-account-text" style="">
                <legend style="margin-bottom: 10px">Account Information</legend>
                <div class="row form-group" style="margin-top:35px">
                    <div class="col-sm-4">
                        <label><?php _e( 'First name: ', 'woocommerce' ); ?></label>
                    </div>
                    <div class="col-sm-8">
                        <input id="account_first_name" value="<?php echo esc_attr( $user->first_name ); ?>" type="text" name="account_first_name" class="form-control" />
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-4">
                        <label><?php _e( 'Last name: ', 'woocommerce' ); ?></label>
                    </div>
                    <div class="col-sm-8">
                        <input id="account_last_name" value="<?php echo esc_attr( $user->last_name ); ?>" type="text" name="account_last_name" class="form-control" />
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-4">
                        <label><?php _e( 'Email address: ', 'woocommerce' ); ?></label> <span class="required">*</span>
                    </div>
                    <div class="col-sm-8">
                        <input id="account_email" value="<?php echo esc_attr( $user->user_email ); ?>" type="email" required="required" name="account_email" class="form-control" />
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="col-sm-6 no-padding-left">
            <fieldset id="edit-account-password">
                <legend style="margin-bottom: 10px"><?php _e( 'Password Change', 'woocommerce' ); ?></legend>
                <p style="font-size: 16px;font-style: italic">Leave blank to leave unchanged</p>
                <div class="row form-group">
                    <div class="col-sm-4">
                        <label for="password_current"><?php _e( 'Current Password', 'woocommerce' ); ?></label>
                    </div>
                    <div class="col-sm-8">
                        <input type="password" class="input-text form-control" name="password_current" id="password_current" />
                    </div>  
                </div>
                <div class="row form-group">
                    <div class="col-sm-4">
                        <label for="password_1"><?php _e( 'New Password ', 'woocommerce' ); ?></label>
                    </div>
                    <div class="col-sm-8">
                         <input type="password" class="input-text form-control" name="password_1" id="password_1" />
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-sm-4">
                        <label for="password_2"><?php _e( 'Confirm Password', 'woocommerce' ); ?></label>
                    </div>
                    <div class="col-sm-8">
                        <input type="password" class="input-text  form-control" name="password_2" id="password_2" />
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
	
	<div class="clear"></div>

	<?php do_action( 'woocommerce_edit_account_form' ); ?>

        <p class="a-center" style="margin-top:20px">
		<?php wp_nonce_field( 'save_account_details' ); ?>
		<input type="submit" class="button site-btn small" name="save_account_details" value="<?php _e( 'Save changes', 'woocommerce' ); ?>" />
		<input type="hidden" name="action" value="save_account_details" />
	</p>

	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
	
</form>