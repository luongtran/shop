<?php 
    if ( is_user_logged_in() ) {
	wp_redirect(get_permalink( get_page_by_path( 'checkout-delivery' ) ) );
        exit();
    } 
?>
<?php
    get_header();

    // action hook for placing content above #container
    thematic_abovecontainer();
?>
    <div id="main-content" class="page-cart">
         <?php get_step_winzar(3);?>
        <?php wc_print_notices(); ?>
        <h1>Your Shopping Bag</h1>
        <div id="checkout-account" class="clearfix">
            <div class="col-sm-6 no-padding">
                <div id="checkout-create-account">
                    <h2>New Customers</h2>
                    <form method="POST">
                        <?php do_action( 'woocommerce_register_form_start' ); ?>
                        <p>
                            Create an account to shop online quickly and easily and take full
                            advantage of out loyalty program
                        </p>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Username:</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" value="<?php if(isset($_POST['username'])) echo  htmlspecialchars($_POST['username']); ?>" name="username" class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Email:</label>
                            </div>
                            <div class="col-sm-8">
                                <input value="<?php if(isset($_POST['email'])) echo  htmlspecialchars($_POST['email']); ?>" type="email" name="email" class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Confirm Email:</label>
                            </div>
                            <div class="col-sm-8">
                                <input value="<?php if(isset($_POST['confirm_email'])) echo htmlspecialchars($_POST['confirm_email']); ?>" type="email" name="confirm_email" class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Password:</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="password" name="password" class="form-control" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Confirm Password:</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="password" name="confirm_password" class="form-control" />
                            </div>
                        </div>
                        <div style="margin-bottom: 20px">
                            <input name="term"  type="checkbox" id="term-check" class="css-checkbox" checked="checked"/>
                            <label for="term-check"  class="css-label lite-orange-check">
                                Please tick this box to confirm you have read, understood and agree to our
                                Term & Conditions
                            </label>
                        </div>
                        <div style="margin-bottom: 10px">
                            <input name="subscribe" id="subscribe_check" type="checkbox" class="css-checkbox" />
                            <label class="css-label lite-orange-check" for="subscribe_check"> 
                                Subscribe to our newsletter. You can unsubscribe at any time
                            </label>
                        </div>
                        <div class="a-center clearfix">
                            <input type="hidden" name="_wp_http_referer" value="<?php echo get_permalink( get_page_by_path( 'checkout-delivery' ) ) ?>"/>
                            <input name="checkout_register" class="register btn" type="submit" value="<?php _e( 'Sign Up', 'woocommerce' ); ?>" />
                        </div>
                            
                        <?php do_action( 'woocommerce_register_form_end' ); ?>
                    </form>
                </div>
            </div>
            <div class="col-sm-6 no-padding">
                <div id="checkout-login">
                    <h2>Returning Customers</h2>
                    <p>
                        If you have already saved your account information, access it by logging in bellow 
                    </p>
                    <form method="POST">
                    <div class="row form-group">
                        <div class="col-sm-4">
                            <label>Email:</label>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" name="username" class="form-control" />
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-sm-4">
                            <label>Password:</label>
                        </div>
                        <div class="col-sm-8">
                            <input type="password" name="password" class="form-control" />
                        </div>
                    </div>
                    <div class="remember" style="margin-bottom: 10px">
                        <input  class="css-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> 
                        <label class="css-label lite-orange-check" for="rememberme">
                               <?php _e( 'Remember me', 'woocommerce' ); ?>
                        </label>
                    </div>
                    <div class="row">
                        <div class="lost_password col-sm-7 pull-left">
                            <a class="forgot-password" href="<?php echo esc_url( wc_lostpassword_url() ); ?>"><?php _e( 'Forgotten Password?', 'woocommerce' ); ?></a>
                        </div>
                        <div class="col-sm-5 pull-right">
                            <?php wp_nonce_field( 'woocommerce-login' ); ?>
                            <input type="hidden" name="redirect" value="/checkout-delivery" />
                            <input  type="submit" class="button login" name="login" value="<?php _e( 'Sign In', 'woocommerce' ); ?>" /> 
                        </div>
                    </div>
                    </form>
                </div>
            </div>
            <div class="col-sm-12">
                 <div id="guest-checkout">
            <label>Guest Checkout: </label> Checkout without creating an account 
            <a href="<?php echo get_permalink( get_page_by_path( 'checkout-delivery' )) ?>">Proceed To Checkout </a>
        </div>
            </div>
     
        </div>
    </div><!-- #content -->
    <?php 
        // action hook for placing content below #content
        thematic_belowcontent(); 
    ?> 
<?php 
    // action hook for placing content below #container
    thematic_belowcontainer();

    // calling footer.php
    get_footer();
?>