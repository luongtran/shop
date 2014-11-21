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
                color: #A5933F;
            }
        </style>
    </head>
    <body style="background-color: #fff; font-family: Helvetica Neue, Helvetica, Arial, sans-serif; font-size: 14px; color: #666; margin: 20px auto; padding: 0;">
        <br>
        
         <table border="1" cellspacing="10" cellpadding="0" width="600" align="center">
            <tbody>
                <tr>
                    <td style="background-color: #fff;" width="600" valign="top">
                        <table border="0" cellspacing="0" cellpadding="0" width="100%">
                            <tbody>
                                
                                <tr>
                                    <td align="center" style="text-align: center;background: #000;">                                            
                                          <img style="padding:10px;" src="<?php echo home_url() ?>/wp-content/themes/signturn/images/logo.png" /> 
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo $message; ?></td>
                                </tr>   
                                <tr>
                                    <td align="center" style="font-size: 14px; font-family: Arial;margin-bottom:20px">
                                        <?php
                                            $social_icon_url = plugins_url().'/newsletter/emails/themes/default/images';
                                        ?>
                                        <p>follow us on:</p>
                                        <a href="http://facebook.com/signaturefragran"><img src="<?php echo $social_icon_url ?>/facebook.png"></a>
                                        <a href="http://twitter.com/sflondonsocial"><img src="<?php echo $social_icon_url ?>/twitter.png"></a>
                                        <a href="http://instagram.com/sflondonsocial"><img src="<?php echo $social_icon_url ?>/instagram.png"></a>
                                        <br><br>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        
    </body>
</html>