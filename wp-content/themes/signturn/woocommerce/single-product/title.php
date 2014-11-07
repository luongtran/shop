<?php
/**
 * Single Product title
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $product;
?>
<h1 itemprop="name" class="product_title entry-title"><?php the_title(); ?> | <?php echo $product->get_price_html(); ?></h1>
<div>
    <img class="img-responsive" src="<?php echo TEMPLATE_URL ?>/images/delivery-image.png" alt="delivery image" />
</div>