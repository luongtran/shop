<?php
/*
 * Some variables are already defined:
 *
 * - $theme_options An array with all theme options
 * - $theme_url Is the absolute URL to the theme folder used to reference images
 * - $theme_subject Will be the email subject if set by this theme
 *
 */

global $newsletter, $post;

$color = $theme_options['theme_color'];
if (empty($color))
    $color = '#0088cc';

if (isset($theme_options['theme_posts'])) {
    $filters = array();

    if (empty($theme_options['theme_max_posts']))
        $filters['showposts'] = 10;
    else
        $filters['showposts'] = (int) $theme_options['theme_max_posts'];

    if (!empty($theme_options['theme_categories'])) {
        $filters['category__in'] = $theme_options['theme_categories'];
    }

    if (!empty($theme_options['theme_tags'])) {
        $filters['tag'] = $theme_options['theme_tags'];
    }

    if (!empty($theme_options['theme_post_types'])) {
        $filters['post_type'] = $theme_options['theme_post_types'];
    }

    $posts = get_posts($filters);
}
?><!DOCTYPE html>
<html>
    <head>
        <style type="text/css">
            .ReadMsgBody {
                width: 100%;
            }
            .ExternalClass {
                width: 100%; background-color:#e7e8e9 !important;
            }
            .yshortcuts {color: #2979be;}
            body {
                background-color: #e7e8e9;}

        </style>
    </head>
    <body style="background-color:#fff;">
        <br>
        <br>
        <table border="0" cellspacing="0" cellpadding="1" width="550" align="center">
            <tbody>
                <tr>
                    <td style="background-color: #fff;" width="550" valign="top">
                        <table border="0" cellspacing="0" cellpadding="0" width="100%">
                            <tbody>
                                <tr>
                                    <td valign="top" style="color: #f4f4f4; font-size: 12px; padding: 7px;text-align: center">
                                        To ensure you receive you Signature Fragrances™ emails, please add <a href="mailto:info@signaturefragrances.co.uk">info@signaturefragrances.co.uk</a> to your address book
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: center">                                            
                                        Signature Fragrances <br/>
                                        London  <img width="100" src="http://signaturefragrances.co.uk/testshop/wp-content/themes/signturn/images/logo.png" />
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
                                        <?php include WP_PLUGIN_DIR . '/newsletter/emails/themes/default/social.php'; ?>
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
                                                    <a href="<?php echo $theme_options['theme_facebook'] ?>"><img src="<?php echo $social_icon_url ?>/facebook.png"><br>Facebook</a>
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
    </body>
</html>