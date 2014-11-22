<?php
/**
 * Login Form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php wc_print_notices(); ?>

<?php do_action( 'woocommerce_before_customer_login_form' ); ?>



<div class="col2-set clearfix" id="customer_login">
        <?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>
                
	<div class="col-2 col-sm-6 no-padding">
            <div id="my-account-login">
                <h2><?php _e( 'Login', 'woocommerce' ); ?></h2>
                <p>Already have an account? Please sign in below.<br/>&nbsp;</p>
		<form method="post" class="login">
			<?php do_action( 'woocommerce_login_form_start' ); ?>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label for="username"><?php _e( 'Username/email', 'woocommerce' ); ?> <span class="required">*</span></label>
                            </div>
                            <div class="col-sm-8">
                                <?php 
                                    $old_value = '';
                                    if(isset($_POST['username']) && isset($_POST['login'])){
                                        $old_value = $_POST['username'];
                                    }elseif(isset($_POST['email']) && isset($_POST['login'])){
                                        $old_value = $_POST['email'];
                                    }
                                ?>
                                <input type="text" class="input-text site-input  form-control" name="username" id="username" value="<?php echo $old_value; ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                               <label for="password"><?php _e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
                            </div>
                            <div class="col-sm-8">
                               <input class="input-text site-input form-control" type="password" name="password" id="password" />
                            </div>
                        </div>
			<?php do_action( 'woocommerce_login_form' ); ?>
                        <?php do_action( 'wordpress_social_login' ); ?> 
			<p class="form-row">
				<?php wp_nonce_field( 'woocommerce-login' ); ?>
				<input type="submit" class="button btn site-btn small pull-right" name="login" value="<?php _e( 'Sign In', 'woocommerce' ); ?>" /> 
				<label for="rememberme" class="inline">
					<input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember me', 'woocommerce' ); ?>
				</label>
			</p>
			<p class="lost_password">
				<a href="<?php echo esc_url( wc_lostpassword_url() ); ?>"><?php _e( 'Lost your password?', 'woocommerce' ); ?></a>
			</p>

			<?php do_action( 'woocommerce_login_form_end' ); ?>
		</form>
            </div>
	</div>
        <?php endif;?>
	<div class="col-1 col-sm-6 no-padding">
           
             <div id="my-account-register">
                <h2><?php _e( 'Register', 'woocommerce' ); ?></h2>
                <p>
                    Want to fascinate someone? Need a stylish bottle? Treating yourself to a little scented luxury?
                </p>
		<form method="post" class="register">
			<?php do_action( 'woocommerce_register_form_start' ); ?>
			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
                    <?php 
                        $old_username = '';$old_email = '';
                        if(isset($_POST['username']) && isset($_POST['register'])){
                            $old_username = $_POST['username'];
                        }
                        if(isset($_POST['email']) && isset($_POST['register'])){
                            $old_email = $_POST['email'];
                        }
                    ?>
                    <div class="row form-group">
                        <div class="col-sm-4">
                            <label for="reg_username"><?php _e( 'Username', 'woocommerce' ); ?> <span class="required">*</span></label>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="input-text site-input form-control" name="username" id="reg_username" value="<?php echo $old_username ?>" />
                        </div>
                    </div>
			<?php endif; ?>
                    <div class="row form-group">
                        <div class="col-sm-4">
                            <label for="reg_email"><?php _e( 'Email address', 'woocommerce' ); ?> <span class="required">*</span></label>
                        </div>
                        <div class="col-sm-8">
                            <input type="email" class="input-text site-input form-control" name="email" id="reg_email" value="<?php if($old_email){echo $old_email;}elseif(!empty($_GET['email'])) echo esc_attr($_GET['email']) ?>" />
                        </div>
                    </div>
                    <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label for="reg_password"><?php _e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="password" class="input-text site-input form-control" name="password" id="reg_password" />
                            </div>
                        </div>
                    <?php endif; ?>

			<!-- Spam Trap -->
			<div style="<?php echo ( ( is_rtl() ) ? 'right' : 'left' ); ?>: -999em; position: absolute;"><label for="trap"><?php _e( 'Anti-spam', 'woocommerce' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" /></div>

			<?php do_action( 'woocommerce_register_form' ); ?>
			<?php do_action( 'register_form' ); ?>

			<p class="form-row">
				<?php wp_nonce_field( 'woocommerce-register', 'register' ); ?>
				<input type="submit" class="button btn site-btn site-btn-2 pull-right small" name="register" value="<?php _e( 'Open Account', 'woocommerce' ); ?>" />
			</p>

			<?php do_action( 'woocommerce_register_form_end' ); ?>

		</form>
            </div>
	</div>


</div>
<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
