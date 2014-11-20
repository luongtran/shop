<?php
/*
 * To customize this file, do not edit it. Instead use the sample alternative email-alternative.php
 * and copy it on
 *
 * wp-content/extensions/newsletter/subscription/email.php
 *
 * creating the folders as needed. Then customize that file.
 *
 * Remember to keep at least the line of code
 *
 * <?php echo $message; ?>
 *
 * which prints the current email body created by Newsletter based on te current subscription
 * process step.
 */

if (is_file(WP_CONTENT_DIR . '/extensions/newsletter/subscription/email.php')) {
  include WP_CONTENT_DIR . '/extensions/newsletter/subscription/email.php';
  return;
}

?><!DOCTYPE html>
<html>
    <head>
        <style type="text/css" media="all">
            a {
                text-decoration: none;
                color: #0088cc;
            }
        </style>
    </head>
    <body style="background-color: #ddd; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; font-size: 14px; color: #666; margin: 0 auto; padding: 0;">
        <br>
        
         <table border="1" cellspacing="0" cellpadding="0" width="550" align="center">
            <tbody>
                <tr>
                    <td style="background-color: #fff;" width="550" valign="top">
                        <table border="0" cellspacing="0" cellpadding="0" width="100%">
                            <tbody>
                                <tr>
                                    <td valign="top" align="center" style="color: #f4f4f4;background: #000; font-size: 12px; padding: 7px;text-align: center">
                                        <br/>To ensure you receive you Signature Fragrances™ emails, please add <a href="mailto:info@signaturefragrances.co.uk">info@signaturefragrances.co.uk</a> to your address book
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="text-align: center;background: #000;">                                            
                                        Signature Fragrances <br/>
                                        London  <img width="100" src="<?php echo home_url() ?>/wp-content/themes/signturn/images/logo.png" /> <br/><br/>
                                    </td>
                                </tr>
                                <!-- main content here --> 
                                <tr>
                                    <td align="center" >
                                        <h1 style="margin:30px 0px;font-size: 20px">
                                            THANK YOU FOR JOINING THE WORLD OF <br/>SIGNATURE FRAGRANCES LONDON™ 
                                        </h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" >
                                        <p style="margin:30px 0px">
                                            You will be amongst the first to discover our latest news:<br/>
                                            From exciting new product launches, to exclusive gift ideas.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" >
                                        <h3 style="margin-bottom: 10px">CONNECT WITH OUR COMMUNITY </h3>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" >
                                        <p style="margin-top:20px">For news, reviews, and pictorial delights, follow us on: </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="font-size: 14px; font-family: Arial;margin-bottom:20px">
                                        <?php
                                            $social_icon_url = plugins_url().'/newsletter/emails/themes/default/images';
                                        ?>
                                        <a href="http://facebook.com/signaturefragran"><img src="<?php echo $social_icon_url ?>/facebook.png">Facebook</a>
                                        <a href="http://twitter.com/signaturefragran"><img src="<?php echo $social_icon_url ?>/twitter.png">Twitter</a>
                                        <a href="http://instagram.com/signaturefragran"><img src="<?php echo $social_icon_url ?>/instagram.png">Instagram</a>
                                    </td>
                                </tr>
                                <!-- end main content --> 
                                <tr>
                                    <td align="center" >
                                        <h3 style="margin-bottom:20px">Can We Help?</h3>
                                    </td>
                                </tr>
                                <tr>
                                    <td  align="center" style="font-size: 11px">
                                        Contact our customer service team via: <a href="mailto:sales@signaturefragrances.co.uk">sales@signaturefragrances.co.uk</a> or visit our Facebook page:
                                           <?php
                                                $social_icon_url = plugins_url('emails/themes/default/images', 'newsletter/plugin.pnp'); 
                                                if (!empty($theme_options['theme_facebook'])) { ?>
                                                <td style="text-align: center; vertical-align: top" align="center" valign="top">
                                                    <a href="http://facebook.com/signaturefragran"><img src="<?php echo $social_icon_url ?>/facebook.png"><br>Facebook</a>
                                                </td>
                                            <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td  align="center" style="font-size: 11px">
                                        <p>
                                            You are receiving this email because you signed up with Signature Fragrances London™ <br/>
                                            The Scented House Limited governs Signature Fragrances™. Registered in England and Wales No. 8801277 @ Kennington Oval, SE11 5SZ. London
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <!--
        <table align="center">
            <tr>
                <td style="font-family: Helvetica Neue, Helvetica, Arial, sans-serif; font-size: 14px; color: #666;">
                    <div style="text-align: left; max-width: 500px; border-top: 10px solid #43A4D0; border-bottom: 3px solid #43A4D0;">
                        <div style="padding: 10px 20px; color: #000; font-size: 20px; background-color: #EFEFEF; border-bottom: 1px solid #ddd">
                            <?php echo get_option('blogname'); ?>
                        </div>
                        <div style="padding: 20px; background-color: #fff; line-height: 18px">

                            <?php echo $message; ?>

                        </div>

                    </div>
                </td>
            </tr>
        </table>
        !-->
    </body>
</html>