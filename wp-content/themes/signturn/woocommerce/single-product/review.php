<?php
/**
 * Review Comments Template
 *
 * Closing li is left out on purpose!
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
?>
<li <?php comment_class('col-sm-6'); ?> id="li-comment-<?php comment_ID() ?>">

	<div id="comment-<?php comment_ID(); ?>" class="row">
            <div class="col-sm-4">
                <strong itemprop="author"><?php comment_author(); ?></strong>
            </div>
            <div class="col-sm-8 meta-rate-time">
                <time itemprop="datePublished" datetime="<?php echo get_comment_date( 'c' ); ?>"><?php echo get_comment_date( __( get_option( 'date_format' ), 'woocommerce' ) ); ?></time> 
                <?php if ( $rating && get_option( 'woocommerce_enable_review_rating' ) == 'yes' ) : ?>
                   |  <div class="reviews-item-ratied" data-score="<?php echo $rating ?>"></div>
                <?php endif; ?>
            </div>
            <div class="col-sm-12" style="margin-top:25px">
                <?php comment_text(); ?>
            </div>
	</div>
</li>
