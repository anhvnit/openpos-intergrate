<?php
/*
Plugin Name: Woocommerce - Openpos -  Role Based Price For WooCommerce
Plugin URI: http://openswatch.com
Description: Woocommerce - Openpos -  Role Based Price For WooCommerce
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: woocommerce-role-based-price-openpos
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if(!function_exists('wc_rbp_op_customer_data'))
{
    function wc_rbp_op_customer_data($customer_data){
        global $current_user;
        global $op_woo;
        
        $customer_id = $customer_data['id'];
        wp_set_current_user( $customer_id );

        $discounts = array();
        $cart_data = isset($_REQUEST['cart']) ? json_decode(stripslashes($_REQUEST['cart']),true) : array();
        $grand_total = isset($cart_data['grand_total']) ? $cart_data['grand_total'] : 0;
        $items = isset($cart_data['items']) ? $cart_data['items'] : array();

        $user_meta = get_userdata($customer_id);

        $user_roles = $user_meta->roles;

       

        if($grand_total > 0 && !empty($items))
        {

                    $product_ids = array();
                    $product_prices = array();
                    $item_ids = array();
                    foreach($items as $key => $item)
                    {
                        $item_id = $item['id'];
                        $product_id = $item['product_id'];

                        $item_discount = $item['final_discount_amount'];
                        if($item_discount == 0 && $item['qty'] > 0)
                        {
                            $product_ids[$key] = $product_id;
                            $product_prices[$key] = $item['final_price'];
                            $item_ids[ $key] = $item_id;
                        }
                    }

                    foreach($product_ids as $key => $product_id)
                    {
                        $item_id = $item_ids[$key];
                        $product = wc_get_product($product_id);
                        $final_price_role = wc_rbp_get_product_price($product_id);
                        $tmp_prices = array();
                        foreach($user_roles as $role)
                        {
                            if(isset($final_price_role[$role])){
                                $regular_price = 0;
                                $selling_price = 0;
                                if(isset($final_price_role[$role]['regular_price']) && $final_price_role[$role]['regular_price'] > 0){
                                    $regular_price = $final_price_role[$role]['regular_price'];
                                }
                                if(isset($final_price_role[$role]['selling_price']) && $final_price_role[$role]['selling_price'] > 0){
                                    $selling_price  = $final_price_role[$role]['selling_price'];
                                }
                                if( $selling_price > 0)
                                {
                                    $tmp_prices[] = $selling_price;
                                }else{
                                    if( $regular_price > 0)
                                    {
                                        $tmp_prices[] = $regular_price;
                                    }
                                }
                                
                            }
                        }
                        if(empty($tmp_prices))
                        {
                            continue;
                        }
                        
                        $final_price = min($tmp_prices);

                        if($final_price == 0)
                        {
                            continue;
                        }
                        $tax_amount = 0;

                        if(wc_tax_enabled() )
                        {
                            $price_included_tax = wc_prices_include_tax();
                            if($price_included_tax)
                            {
                                $tax_rates = $op_woo->getTaxRates( $product->get_tax_class() );

                                if(!empty($tax_rates))
                                {
                                    $keys = array_keys($tax_rates);
                                    $rate_id = max($keys);
                                    $rate = $tax_rates[$rate_id];

                                    $tax_amount = array_sum(@WC_Tax::calc_tax( $final_price, array($rate_id => $rate), wc_prices_include_tax() ));
                                    $tax_amount = wc_round_tax_total($tax_amount);

                                }

                                $final_price -= $tax_amount;



                            }

                        }


                        $tmp_item = array(
                            'item_id' => $item_id,
                            'product_id' => $product_id,
                            'final_price' => $final_price
                        );


                        if($tax_amount > 0)
                        {
                            $tmp_item['tax_amount'] = $tax_amount;
                        }
                        $discounts[''.$item_id] = $tmp_item;
                    }
        }

        if(!empty($discounts))
        {
            $customer_data['items_price'] =  $discounts;
        }

        $current_user = null;
        wp_set_current_user( 0 );

        return $customer_data;
    }
}
add_filter('op_customer_data','wc_rbp_op_customer_data',10,1);



