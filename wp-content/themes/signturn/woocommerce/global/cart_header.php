<?php 
    $cart  = WC()->cart->get_cart();
    function get_product_category_by_id( $category_id ) {
        $term = get_term_by( 'id', $category_id, 'product_cat', 'ARRAY_A' );
        return $term['name'];
    }
?>
<div id="header-cart" class="header-box">
    <a class="header-box-close" href="javascript:void(0)">&Chi;</a>
    <h3>Your Bag</h3>
    <?php 
        if(count($cart)):
    ?>
    <table class="table">
        <?php foreach ($cart as $cart_item_key => $cart_item): 
            $_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
            $term_list = wp_get_post_terms($product_id,'product_cat',array('fields'=>'ids'));
            $cat_id = (int)$term_list[0];
            $cat_link = get_term_link ($cat_id, 'product_cat');
        ?>
        <tr>
            <td class="alignleft">
                <?php
                    $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                ?>
                <?php        
                    if ( ! $_product->is_visible() ){
                        echo $thumbnail;
                    }else {
                        printf( '<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail );
                    }
                ?>
            </td>
            <td class="cart-text v-mid alignleft">
                <h4><?php  printf( '<a href="%s">%s</a>', $_product->get_permalink(), $_product->get_title() ) ?></h4>
                <p>Collection : <a href="<?php echo $cat_link ?>"><?php echo get_product_category_by_id($cat_id) ?></a></p>
            </td>
            <td class="v-mid"><?php echo $cart_item['quantity'];?></td>
            <td  class="alignleft v-mid">
                <?php
                    echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                ?>
            </td>
        </tr>
        <?php endforeach;?>
    </table>
    <table class="table" style="margin-bottom: 0">
        <tr>
            <td class="alignleft" style="width: 33%">IN YOUR BAG</td>
            <td class="aligncenter" style="width: 33%"><?php echo sprintf(_n('%d Item', '%d Items', WC()->cart->cart_contents_count, 'esell'), WC()->cart->cart_contents_count);?></td>
            <td class="alignright" style="width: 33%"><a href="<?php echo $woocommerce->cart->get_cart_url(); ?>">VIEW BAG</a></td>
        </tr>
        <tr>
            <td class="alignleft" >SUBTOTAL</td>
            <td class="aligncenter"></td>
            <td class="alignright"><?php wc_cart_totals_subtotal_html(); ?></td>
        </tr>
        <tr>
            <td colspan="3">
                <a  class="btn site-btn" href="<?php echo get_permalink( get_page_by_path( 'gift' )) ?>">Checkout</a>
            </td>
        </tr>
    </table>
    <?php else: ?>
    <h4 class="aligncenter">YOUR BAG IS EMPTY</h4>
    <?php endif;?>
</div>