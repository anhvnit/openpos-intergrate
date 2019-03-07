<?php
/*
Plugin Name: Woocommerce OpenPos Mycred integrated
Plugin URI: http://openswatch.com
Description: Woocommerce OpenPos Mycred integrated.
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: openpos
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


if(!function_exists('op_mycred_user_data'))
{
    function op_mycred_user_data($user_data){
        global $mycred;

        $customer_id = $user_data['id'];

        // Show balance
        $ubalance = $mycred->get_users_cred( $customer_id );
        $user_data['point'] = $ubalance;
        $user_data['point_rate'] = 1;
        $user_data['point_setting'] = array();
        return $user_data;
    }
}

add_filter('op_customer_data','op_mycred_user_data',10,1);

if(!function_exists('op_mycred_add_order_before'))
{
    function op_mycred_add_order_before($order,$order_data){


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
        }

    }
}
add_action('op_add_order_before', 'op_mycred_add_order_before',10,2);


if(!function_exists('op_mycred_order_status_changed'))
{
    function op_mycred_order_status_changed($order_id){
        global $mycred;
        $order = new WC_Order($order_id);

        $status = $order->get_status();
        $user_id = $order->get_customer_id();
        if($status == 'completed' && $user_id)
        {
            $point_discount = get_post_meta($order_id,'_op_point_discount',true);

            if($point_discount && !empty($point_discount))
            {
                if( $point_discount['point'] > 0)
                {
                    $type   = MYCRED_DEFAULT_TYPE_KEY;
                    $redeem_points = $point_discount['point'];
                    $amount          =   0 - $redeem_points;
                     $reference = 'Order #'.$order_id;
                     $entry = $reference;
                     $data = '';
                     $mycred->add_creds(
                        $reference,
                         $user_id,
                         $amount,
                         $entry,
                         $user_id,
                        $data,
                        $type
                    );

                }
            }

        }
    }
}
add_action('woocommerce_order_status_changed','op_mycred_order_status_changed',10,1);