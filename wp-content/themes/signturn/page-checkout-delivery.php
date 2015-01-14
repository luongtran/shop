<?php 
 if(!is_user_logged_in()){
//     wp_redirect(get_permalink( get_page_by_path( 'checkout-account' ) ) );
//    exit();
 }
?>
<?php
    get_header();

    // action hook for placing content above #container
    thematic_abovecontainer();
?>
    <div id="main-content" class="page-gift page-cart">
        <?php get_step_winzar(4);?>
         <?php 
           $checkout_error = array(); 
           if(isset($_SESSION['checkout_errors'])){
               $checkout_error = $_SESSION['checkout_errors'];
               unset($_SESSION['checkout_errors']);
           }
        ?>
        <?php wc_print_notices(); ?>
       
        <h1>Your Shopping Bag</h1>
        <form method="POST">
        <div id="checkout-delivery" class="clearfix">
            <div class="col-sm-6 no-padding">
                <div id="checkout-delivery-builling">
                    <h2>Billing Address</h2>
                        <?php 
                         $customer_id = get_current_user_id();
                        ?>
                    <p>
                        This is where we will deliver your receipt <br/>
                        <small><span>(*)</span> denotes required fields </small>
                    </p>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Title: <span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <select  name="billing_title" class="form-control">
                                    <?php $billing_title = isset($_POST['billing_title']) ? $_POST['billing_title'] :  get_user_meta( $customer_id, 'billing_title', true )  ?>
                                    <option value="Mr"  <?php if($billing_title==='Mr') echo 'selected="selected"'; ?>>Mr</option>
                                    <option value="Mrs"  <?php if($billing_title==='Mrs') echo 'selected="selected"'; ?>>Mrs</option>
                                    <option value="Ms"  <?php if($billing_title==='Ms') echo 'selected="selected"'; ?>>Ms</option>
                                    <option value="Sir"  <?php if($billing_title==='Sir') echo 'selected="selected"'; ?>>Sir</option>
                                    <option value="Dr"  <?php if($billing_title==='Dr') echo 'selected="selected"'; ?>>Dr</option>
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>First Name:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_first_name" value="<?php if(isset($_POST['billing_first_name']))echo $_POST['billing_first_name'];else  echo  get_user_meta( $customer_id, 'billing_first_name', true ) ?>" class="form-control <?php if(in_array('billing_first_name', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Last Name:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_last_name" value="<?php if(isset($_POST['billing_last_name']))echo $_POST['billing_last_name'];else   echo  get_user_meta( $customer_id, 'billing_last_name', true ) ?>" class="form-control <?php if(in_array('billing_last_name', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Contact Number:</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_phone" value="<?php if(isset($_POST['billing_phone']))echo $_POST['billing_phone'];else    echo  get_user_meta( $customer_id, 'billing_phone', true ) ?>"  class="form-control <?php if(in_array('billing_phone', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Email: <span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_email" value="<?php if(isset($_POST['billing_email']))echo $_POST['billing_email'];else    echo  get_user_meta( $customer_id, 'billing_email', true ) ?>"  class="form-control <?php if(in_array('billing_email', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Address<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_address_1"  value="<?php if(isset($_POST['billing_address_1']))echo $_POST['billing_address_1'];else    echo  get_user_meta( $customer_id, 'billing_address_1', true ) ?>"  class="form-control <?php if(in_array('billing_address_1', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-8 col-sm-offset-4">
                                <input type="text" name="billing_address_2"  value="<?php if(isset($_POST['billing_address_2']))echo $_POST['billing_address_2'];else    echo  get_user_meta( $customer_id, 'billing_address_2', true ) ?>"  class="form-control <?php if(in_array('billing_address_2', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>City:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_city" value="<?php if(isset($_POST['billing_city']))echo $_POST['billing_city'];else    echo  get_user_meta( $customer_id, 'billing_city', true ) ?>"  class="form-control <?php if(in_array('billing_city', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <?php 
                            $us_status = WC()->countries->get_states('US');
                            $choosen_state = isset($_POST['billing_state']) ? $_POST['billing_state'] : '';
                            
                            $billing_country = isset($_POST['billing_country']) ? 
                                    $_POST['billing_country'] : get_user_meta( $customer_id, 'billing_country', true );
                            //die($billing_country);
                        ?>
                        <div id="billing-country-wrapper" class="row form-group" <?php if($billing_country !=='US') echo 'style="display:none"'  ?>>
                            <div class="col-sm-4">
                                <label>State: </label>
                            </div>
                            <div class="col-sm-8">
                                <select id="select-billing-state" class="form-control" name="billing_state">
                                    <option disabled="disabled" value="" <?php if(!$billing_country) echo 'selected="selected"'?> >Choose your state</option>
                                    <?php
                                        foreach($us_status as $key => $name):
                                    ?>
                                    <option <?php if($choosen_state===$key) echo 'selected="selected"' ?> value="<?php echo $key?>">
                                        <?php echo $name ?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Postcode:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="billing_postcode" value="<?php if(isset($_POST['billing_postcode']))echo $_POST['billing_postcode'];else    echo  get_user_meta( $customer_id, 'billing_postcode', true ) ?>"  class="form-control <?php if(in_array('billing_postcode', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <?php 
                                $countries = WC()->countries->get_shipping_countries();
                                $args = ['class'=>' ','id'=>' ','label_class'=>'','label'=>' Country'];
                                if(isset($checkout_error['billing_country'])){
                                    $args['class'] = ' error ';
                                }
                                $field =  '<select id="select-billing-country" class="country_to_state country_select form-control" name="billing_country" >'
						. '<option value="">'.__( 'Select a country&hellip;', 'woocommerce' ) .'</option>';
				foreach ( $countries as $ckey => $cvalue ){
                                    //echo "current $ckey vs ".get_user_meta( $customer_id, 'billing_country', true );
                                    $selected = esc_attr( $ckey ) == $billing_country ?  'selected="selected"' : '';
                                    $field .= '<option '.$selected.'  value="' . esc_attr( $ckey ) . '" '.selected( $value, $ckey, false ) .'>'.__( $cvalue, 'woocommerce' ) .'</option>';
                                }
				$field .= '</select>';
                            ?>
                            <div class="col-sm-4">
                                <label>Country:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <?php echo $field;?>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label class="delivery-instructions">Delivery Instructions:</label>
                            </div>
                            <div class="col-sm-8">
                                <textarea class="form-control"  name="delivery_instructions"><?php if(isset($_POST['delivery_instructions']))echo $_POST['delivery_instructions'];else   echo  get_user_meta( $customer_id, 'delivery_instructions', true ) ?></textarea>
                            </div>
                        </div>
                        <p class="radio-wrapper">
                                <input name="ship_to_different_address" checked="checked" value="0" type="radio" /> 
                                <label> Use my billing address as my delivery address</label>
                        </p>
                        <p class="radio-wrapper">
                                <input name="ship_to_different_address"  type="radio" value="1" /> 
                                <label>Enter a different delivery address</label>
                        </p>
                </div>
            </div>
            <div class="col-sm-6 no-padding">
                <div id="checkout-delivery-delivery">
                    <h2>Shipping Address</h2>
                    <div id="custom-delivery-address" style="display: none">
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Title: <span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                 <select name="shipping_title" class="form-control">
                                    <?php $shipping_title = isset($_POST['shipping_title']) ? $_POST['shipping_title'] :  get_user_meta( $customer_id, 'shipping_title', true )  ?>
                                    <option value="Mr"  <?php if($shipping_title==='Mr') echo 'selected="selected"'; ?>>Mr</option>
                                    <option value="Mrs"  <?php if($shipping_title==='Mrs') echo 'selected="selected"'; ?>>Mrs</option>
                                    <option value="Ms"  <?php if($shipping_title==='Ms') echo 'selected="selected"'; ?>>Ms</option>
                                    <option value="Sir"  <?php if($shipping_title==='Sir') echo 'selected="selected"'; ?>>Sir</option>
                                    <option value="Dr"  <?php if($shipping_title==='Dr') echo 'selected="selected"'; ?>>Dr</option>
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>First Name:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="shipping_first_name" value="<?php if(isset($_POST['shipping_first_name']))echo $_POST['shipping_first_name'];else  echo  get_user_meta( $customer_id, 'shipping_first_name', true ) ?>" class="form-control <?php if(in_array('shipping_first_name', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Last Name:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="shipping_last_name" value="<?php if(isset($_POST['shipping_last_name']))echo $_POST['shipping_last_name'];else  echo  get_user_meta( $customer_id, 'shipping_last_name', true ) ?>" class="form-control <?php if(in_array('shipping_last_name', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Contact Number:</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="shipping_phone" value="<?php if(isset($_POST['shipping_phone']))echo $_POST['shipping_phone'];else    echo  get_user_meta( $customer_id, 'shipping_phone', true ) ?>"  class="form-control <?php if(in_array('shipping_phone', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Address<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="shipping_address_1"  value="<?php if(isset($_POST['shipping_address_1']))echo $_POST['shipping_address_1'];else  echo  get_user_meta( $customer_id, 'shipping_address_1', true ) ?>"  class="form-control <?php if(in_array('shipping_address_1', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-8 col-sm-offset-4">
                                <input type="text" name="shipping_address_2"  value="<?php if(isset($_POST['shipping_address_2']))echo $_POST['shipping_address_2'];else  echo  get_user_meta( $customer_id, 'shipping_address_2', true ) ?>"  class="form-control <?php if(in_array('shipping_address_2', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>City:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="shipping_city" value="<?php  if(isset($_POST['shipping_city']))echo $_POST['shipping_city'];else echo  get_user_meta( $customer_id, 'shipping_city', true ) ?>"  class="form-control <?php if(in_array('shipping_city', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                         <?php 
                            $choosen_shipping_state = isset($_POST['shipping_state']) ? $_POST['shipping_state'] : '';
                            
                            $shipping_country = isset($_POST['shipping_country']) ? 
                                    $_POST['shipping_country'] : get_user_meta( $customer_id, 'shipping_country', true );
                        ?>
                        <div id="shipping-country-wrapper" class="row form-group" <?php if($shipping_country !=='US') echo 'style="display:none"'  ?>>
                            <div class="col-sm-4">
                                <label>State: </label>
                            </div>
                            <div class="col-sm-8">
                                <select id="select-shipping-state" class="form-control" name="shipping_state">
                                    <option disabled="disabled" value="" selected="selected" >Choose your state</option>
                                    <?php
                                        foreach($us_status as $key => $name):
                                    ?>
                                    <option <?php if($choosen_shipping_state===$key) echo 'selected="selected"' ?> value="<?php echo $key?>">
                                        <?php echo $name ?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label>Postcode:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="shipping_postcode" value="<?php if(isset($_POST['shipping_postcode']))echo $_POST['shipping_postcode'];else  echo  get_user_meta( $customer_id, 'shipping_postcode', true ) ?>"  class="form-control <?php if(in_array('shipping_postcode', $checkout_error)) echo "error" ?>" />
                            </div>
                        </div>
                        <div class="row form-group">
                            <?php 
                                $countries = WC()->countries->get_shipping_countries();
                                $args = ['class'=>' ','id'=>' ','label_class'=>'','label'=>' Country'];
                                 if(isset($checkout_error['shipping_country'])){
                                    $args['class'] = ' error ';
                                }
                                $field =  '<select id="select-shipping-country" class="country_to_state country_select form-control" name="shipping_country" >'
						. '<option value="">'.__( 'Select a country&hellip;', 'woocommerce' ) .'</option>';
                                
				foreach ( $countries as $ckey => $cvalue ){
                                        $selected = esc_attr( $ckey ) == $shipping_country ?  'selected="selected"' : '';
                                       //echo "current $ckey vs ".get_user_meta( $customer_id, 'shipping_country', true );
					$field .= '<option '.$selected.'  value="' . esc_attr( $ckey ) . '" '.selected( $value, $ckey, false ) .'>'.__( $cvalue, 'woocommerce' ) .'</option>';
                                 }
				$field .= '</select>';
                            ?>
                            <div class="col-sm-4">
                                <label>Country:<span>(*)</span></label>
                            </div>
                            <div class="col-sm-8">
                                <?php echo $field;?>
                            </div>
                        </div>
                    </div>
                    <p id="same-billing-adress">
                        This is where we will deliver your purchase. The billing address and shipping are the same
                    </p>
                </div>
            </div>
            
            <div class="a-center col-sm-12">
                <input name="checkout_delivery" class="delivery btn" type="submit" value="<?php _e( 'Complete Payment', 'woocommerce' ); ?>" />
            </div>
        </div>
        </form>
    </div><!-- #content -->
    
<?php function change_delivery_address_option_scripts(){ ?>
    <script>
        $('.radio-wrapper label').on('click',function(){
            $(this).parent().find('input').trigger('click');
        });
        $('input[name="ship_to_different_address"]').val([0]);
        $('input[name="ship_to_different_address"]').on('click',function(){
            if($(this).val()==0){
                $('#custom-delivery-address').hide();
                $('#same-billing-adress').show();
            }else{
                $('#custom-delivery-address').show();
                $('#same-billing-adress').hide();
            }
        });
        //$('#select-billing-country').val('<?php echo $billing_country ?>');
        $('#select-billing-country').change(function(){
            var country   = $(this).val();
            if(country==='US'){
                $('#billing-country-wrapper').show();
            }else{
                $('#billing-country-wrapper').hide();
                 $('#select-billing-state').val('');
            }
        });
        //$('#select-shipping-country').val('<?php echo $shipping_country ?>');
        $('#select-shipping-country').change(function(){
            var country   = $(this).val();
            if(country==='US'){
                $('#shipping-country-wrapper').show();
            }else{
                $('#shipping-country-wrapper').hide();
                 $('#select-shipping-state').val('');
            }
        });
    </script>
<?php } 
 add_action('thematic_after','change_delivery_address_option_scripts');
?>
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
