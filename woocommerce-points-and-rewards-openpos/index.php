<?php
/*
Plugin Name: WooCommerce Points and Rewards For OpenPOS
Plugin URI: http://openswatch.com
Description: WooCommerce Points and Rewards For OpenPOS
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: openpos-dev
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
if(!function_exists('op_wc_points_rewards_user_data'))
{
    function op_wc_points_rewards_user_data($user_data){
        global $wc_points_rewards;

        $customer_id = $user_data['id'];

        // Show balance
        $points_balance = WC_Points_Rewards_Manager::get_users_points( $customer_id );
        $max_discount = get_option( 'wc_points_rewards_cart_max_discount' );
        list( $points, $monetary_value ) = explode( ':', get_option( 'wc_points_rewards_redeem_points_ratio', '' ) );
        $ubalance = $points_balance;
        $user_data['point'] = $ubalance;
        $user_data['balance'] = $points_balance;
        $user_data['point_rate'] = ( $points / $monetary_value );
        $user_data['point_setting'] = array();
        return $user_data;
    }
}

add_filter('op_customer_data','op_wc_points_rewards_user_data',10,1);

if(!function_exists('op_wc_points_rewards_add_order_before'))
{
    function op_wc_points_rewards_add_order_before($order,$order_data){


        $point_discount = isset($order_data['point_discount']) ? $order_data['point_discount'] : array();

        if(!empty($point_discount)  && $point_discount['point'] > 0)
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
            WC_Points_Rewards_Manager::decrease_points( $order_user_id, $point_discount['point'], 'order-redeem', array( 'discount_code' => '', 'discount_amount' => $discount_amount ), $order_id );
            update_post_meta( $order_id, '_wc_points_redeemed', $point_discount['point'] );
        }

    }
}
add_action('op_add_order_before', 'op_wc_points_rewards_add_order_before',10,2);