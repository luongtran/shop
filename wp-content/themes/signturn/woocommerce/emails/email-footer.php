<?php
/**
 * Email Footer
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Load colours
$base = get_option( 'woocommerce_email_base_color' );

$base_lighter_40 = wc_hex_lighter( $base, 40 );

// For gmail compatibility, including CSS styles in head/body are stripped out therefore styles need to be inline. These variables contain rules which are added to the template inline.
$template_footer = "
	border-top:0;
	-webkit-border-radius:6px;
";

$credit = "
	border:0;
	color: $base_lighter_40;
	font-family: Arial;
	font-size:12px;
	line-height:125%;
	text-align:center;
";
?>
															</div>
														</td>
                                                    </tr>
                                                </table>
                                                <!-- End Content -->
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Body -->
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
            	<tr>
                	<td align="center" valign="top">
                        <table border="0" cellpadding="10" cellspacing="0" width="800" id="template_footer" style="<?php echo $template_footer; ?>">
                            <tr>
                                <td style="text-align: center">
                                        <a style="margin-right:10px" href=""><img src="http://www.mmvta.com/wp-content/uploads/2014/07/facebook-icon-small.png" width="30px" /></a>
                                        <a href=""><img src="http://www.mmvta.com/wp-content/uploads/2014/07/facebook-icon-small.png" width="30px" /></a>
                                </td>
                            </tr>
                            <tr>
                                    <td style="text-align: center">
                                            <a href="http://signaturefragrances.co.uk" style="font-size:18px;color:#777" >www.signaturefragrances.co.uk</a>
                                    </td>
                            </tr>
                            <tr>
                                <td valign="top">
                                    <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                            <td colspan="2" valign="middle" id="credit" style="<?php echo $credit; ?>">
                                                    <?php echo wpautop( wp_kses_post( wptexturize( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) ); ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>