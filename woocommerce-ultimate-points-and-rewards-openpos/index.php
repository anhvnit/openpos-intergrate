<?php
/*
Plugin Name: Woocommerce - Openpos -  WooCommerce Ultimate Points And Rewards
Plugin URI: http://openswatch.com
Description: Woocommerce - Openpos -  WooCommerce Ultimate Points And Rewards
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: woocommerce-ultimate-points-and-rewards-openpos
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if(!function_exists('op_wc_ultimate_points_rewards_user_data'))
{
    function op_wc_ultimate_points_rewards_user_data($user_data){
        global $wc_points_rewards;

        $customer_id = $user_data['id'];

        // Show balance
        $mwb_wpr_custom_points_on_cart = get_option("mwb_wpr_custom_points_on_cart",0);
        if($mwb_wpr_custom_points_on_cart == 1) {

            $mwb_wpr_cart_points_rate = get_option("mwb_wpr_cart_points_rate", 1);
            $mwb_wpr_cart_price_rate = get_option("mwb_wpr_cart_price_rate", 1);

            $points_balance = (int)get_user_meta($customer_id, 'mwb_wpr_points', true);

            $ubalance = $points_balance;
            $user_data['point'] = $ubalance;
            $user_data['balance'] = $points_balance;
            $user_data['point_rate'] = ($mwb_wpr_cart_price_rate / $mwb_wpr_cart_points_rate);
            $user_data['point_setting'] = array();
        }
        return $user_data;
    }
}

add_filter('op_customer_data','op_wc_ultimate_points_rewards_user_data',10,1);


if(!function_exists('op_wc_ultimate_points_rewards_add_order_before'))
{
    function op_wc_ultimate_points_rewards_add_order_after($order,$order_data){

        $point_discount = isset($order_data['point_discount']) ? $order_data['point_discount'] : array();
        $tmp = new MWB_WPR_Front_End();
        if(!empty($point_discount)  && $point_discount['point'] > 0 && $order->get_user_id())
        {
            $title = __('Redeem '.$point_discount['point'].'  points for a '.strip_tags(wc_price($point_discount['point_money'])).' discount','openpos');

            $discount_code = $title;

            $discount_amount = $point_discount['point_money'];

            if($discount_amount)
            {
                $discount_amount = 0 - $discount_amount;
                $point_item = new WC_Order_Item_Fee();

                $point_item->set_name($discount_code);
                $point_item->set_tax_status('non-taxable');

                $point_item->set_taxes([]);

                $point_item->set_amount($discount_amount);
                $point_item->set_total($discount_amount);

                $order->add_item($point_item);

            }
            $order_id = $order->get_id();
            $order_user_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->user_id : $order->get_user_id();
            update_post_meta( $order->get_id(), '_wc_points_logged_redemption', array( 'points' => $point_discount['point'], 'amount' => $discount_amount, 'discount_code' => '' ) );

            $total_points = (int)get_user_meta($order->get_user_id(),'mwb_wpr_points',true);
            update_user_meta($order->get_user_id(),'mwb_wpr_points',(int)($total_points - $point_discount['point']));
            $tmp->generate_points_log($order_user_id, (0 - $point_discount['point']));
            //WC_Points_Rewards_Manager::decrease_points( $order_user_id, $point_discount['point'], 'order-redeem', array( 'discount_code' => '', 'discount_amount' => $discount_amount ), $order_id );
            update_post_meta( $order_id, '_wc_points_redeemed', $point_discount['point'] );
        }

        //$tmp->mwb_wpr_woocommerce_order_status_changed($order->get_id(),'pending','completed');
        if($order_id = $order->get_id() && $user_id = $order->get_user_id())
        {
            $base_order = $order;
            $earn_point = 0;
            $items = $base_order->get_items();

            $general_settings = get_option('mwb_wpr_settings_gallery',true);
            foreach($items as $item)
            {
                $item_data = $item->get_data();
                $product_id = $item_data['product_id'];
                $variation_id = $item_data['variation_id'];
                $item_meta = array();
                if($product_id > 0 || $variation_id > 0)
                {
                    $check_enable = get_post_meta($product_id, 'mwb_product_points_enable', 'no');
                    $quantity = $item_data['quantity'];
                    $mwb_wpr_set_preferences = isset($general_settings['mwb_wpr_set_preferences']) ? $general_settings['mwb_wpr_set_preferences'] : 'to_both';

                    if($check_enable == 'yes')
                    {
                        if( $mwb_wpr_set_preferences == 'to_assign_point' || $mwb_wpr_set_preferences == 'to_both' )
                        {
                            if(isset($variation_id) && !empty($variation_id) && $variation_id > 0){
                                $get_product_points = get_post_meta($variation_id, 'mwb_wpr_variable_points', 1);
                                $item_meta['mwb_wpm_points'] = (int)$get_product_points*(int)$quantity;
                            }else{
                                $get_product_points = get_post_meta($product_id, 'mwb_points_product_value', 1);
                                $item_meta['mwb_wpm_points'] = (int)$get_product_points*(int)$quantity;
                            }
                        }

                    }

                }
                if(isset($item_meta['mwb_wpm_points']))
                {
                    $earn_point += $item_meta['mwb_wpm_points'];
                }
            }

            if($earn_point > 0)
            {
                $total_points = (int)get_user_meta($user_id,'mwb_wpr_points',true);
                update_user_meta($order->get_user_id(),'mwb_wpr_points',(int)($total_points + $earn_point));
            }


        }



    }
}
add_action('op_add_order_after', 'op_wc_ultimate_points_rewards_add_order_after',10,2);