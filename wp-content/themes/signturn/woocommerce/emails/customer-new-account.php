<?php
/**
 * Customer new account email
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>
<p style="text-align: center">To ensure you receive you Signature Fragrances™ emails, please add <a href="mailto:info@signaturefragrances.co.uk">info@signaturefragrances.co.uk</a> to your address book</p>
<h2>
    WELCOME TO THE WORLD <br/>
OF SIGNATURE FRAGRANCES LONDON™
</h2>
<p style="text-align: center">It is our pleasure to welcome you to your Signature Fragrances™ account.</p>
<p> Your Login Name: <?php echo esc_html( $user_login ) ?></p>
<?php if ( get_option( 'woocommerce_registration_generate_password' ) == 'yes' && $password_generated ) : ?>

	<p><?php printf( __( "Your password has been automatically generated: <strong>%s</strong>", 'woocommerce' ), esc_html( $user_pass ) ); ?></p>

<?php endif; ?>
<p style="text-align: center">
    Use your account to:<br/>
    Change your name, email address, password.<br/>
    Add or update your billing information and delivery address.<br/>
    Re-order from past purchases and review your order history.<br/>
</p>
<p>
    Discover the World of Signature Fragrances™. A unique journey where bottles,<br/>
    perfumes, and imagination captivate the soul.
</p>
<?php do_action( 'woocommerce_email_footer' ); ?>