<?php
// This page is used to show subscription messages to users along the various
// subscription and unsubscription steps.
//
// This page is used ONLY IF, on main configutation, you have NOT set a specific
// WordPress page to be used to show messages and when there are no alternative
// URLs specified on single messages.
//
// To create an alternative to this file, just copy the page-alternative.php on
//
//   wp-content/extensions/newsletter/subscription/page.php
//
// and modify that copy.

include '../../../../wp-load.php';

$module = NewsletterSubscription::instance();
$user = $module->get_user_from_request();
$message_key = $module->get_message_key_from_request();
$message = $newsletter->replace($module->options[$message_key . '_text'], $user);
$message .= $module->options[$message_key . '_tracking'];
$alert = stripslashes($_REQUEST['alert']);

// Force the UTF-8 charset
header('Content-Type: text/html;charset=UTF-8');

if (is_file(WP_CONTENT_DIR . '/extensions/newsletter/subscription/page.php')) {
    include WP_CONTENT_DIR . '/extensions/newsletter/subscription/page.php';
    die();
}
?>
<html>
    <head>
        <style type="text/css">
            body {
                font-family: verdana;
                background-color: #ddd;
                font-size: 12px;
            }
            #container {
                border: 1px solid #aaa;
                border-radius: 5px;
                background-color: #fff;
                margin: 40px auto;
                width: 600px;
                padding: 20px
            }
            h1 {
                font-size: 24px;
                font-weight: normal;
                border-bottom: 1px solid #aaa;
                margin-top: 0;
            }
            h2 {
                font-size: 20px;
            }
            th, td {
                font-size: 12px;
            }
            th {
                padding-right: 10px;
                text-align: right;
                vertical-align: middle;
                font-weight: normal;
            }
            
        </style>
        <style>
#facebookG{
width:32px}

.facebook_blockG{
background-color:#FFFFFF;
border:1px solid #000000;
float:left;
height:23px;
margin-left:1px;
width:6px;
opacity:0.1;
-moz-animation-name:bounceG;
-moz-animation-duration:1.3s;
-moz-animation-iteration-count:infinite;
-moz-animation-direction:linear;
-moz-transform:scale(0.7);
-webkit-animation-name:bounceG;
-webkit-animation-duration:1.3s;
-webkit-animation-iteration-count:infinite;
-webkit-animation-direction:linear;
-webkit-transform:scale(0.7);
-ms-animation-name:bounceG;
-ms-animation-duration:1.3s;
-ms-animation-iteration-count:infinite;
-ms-animation-direction:linear;
-ms-transform:scale(0.7);
-o-animation-name:bounceG;
-o-animation-duration:1.3s;
-o-animation-iteration-count:infinite;
-o-animation-direction:linear;
-o-transform:scale(0.7);
animation-name:bounceG;
animation-duration:1.3s;
animation-iteration-count:infinite;
animation-direction:linear;
transform:scale(0.7);
}

#blockG_1{
-moz-animation-delay:0.39s;
-webkit-animation-delay:0.39s;
-ms-animation-delay:0.39s;
-o-animation-delay:0.39s;
animation-delay:0.39s;
}

#blockG_2{
-moz-animation-delay:0.52s;
-webkit-animation-delay:0.52s;
-ms-animation-delay:0.52s;
-o-animation-delay:0.52s;
animation-delay:0.52s;
}

#blockG_3{
-moz-animation-delay:0.65s;
-webkit-animation-delay:0.65s;
-ms-animation-delay:0.65s;
-o-animation-delay:0.65s;
animation-delay:0.65s;
}

@-moz-keyframes bounceG{
0%{
-moz-transform:scale(1.2);
opacity:1}

100%{
-moz-transform:scale(0.7);
opacity:0.1}

}

@-webkit-keyframes bounceG{
0%{
-webkit-transform:scale(1.2);
opacity:1}

100%{
-webkit-transform:scale(0.7);
opacity:0.1}

}

@-ms-keyframes bounceG{
0%{
-ms-transform:scale(1.2);
opacity:1}

100%{
-ms-transform:scale(0.7);
opacity:0.1}

}

@-o-keyframes bounceG{
0%{
-o-transform:scale(1.2);
opacity:1}

100%{
-o-transform:scale(0.7);
opacity:0.1}

}

@keyframes bounceG{
0%{
transform:scale(1.2);
opacity:1}

100%{
transform:scale(0.7);
opacity:0.1}

}

</style>
  <script type="text/javascript" src="<?=TEMPLATE_URL?>/js/jquery-2.0.3.min.js"></script>
    </head>

    <body>
        <?php if (!empty($alert)) { ?>
        <script>
            alert("<?php echo addslashes(strip_tags($alert)); ?>");
        </script>
        <?php } ?>
        <div id="container">
            <h1><?php echo get_option('blogname'); ?></h1>
            <?php echo $message; ?>
        </div>
        <?php if(isset($_GET['nm']) && $_GET['nm'] === 'confirmation') :?>
        <p style="text-align: center">You will be redirected to home page in a moment</p>
        <div style="width: 100%;text-align: center">
            <div id="facebookG" style="width: 32px;margin:auto">
                <div id="blockG_1" class="facebook_blockG">
                </div>
                <div id="blockG_2" class="facebook_blockG">
                </div>
                <div id="blockG_3" class="facebook_blockG">
                </div>
             </div>
            <script type="text/javascript">
                 $.wait = function( callback, seconds){
                    return window.setTimeout( callback, seconds * 1000 );
                 };
                 $.wait(function(){
                    //window.location = "<?php echo home_url() ?>";
                },3);
            </script>
         
        </div>
        <?php endif;?>
        
    </body>
</html>