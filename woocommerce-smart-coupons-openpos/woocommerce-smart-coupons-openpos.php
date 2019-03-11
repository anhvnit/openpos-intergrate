<?php
/*
Plugin Name: Woocommerce Smart Coupon integrate with Openpos
Plugin URI: http://openswatch.com
Description: Allow change Price for product on POS panel
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: openpos-smart-coupon
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if(!function_exists('op_smart_coupon_add_order_coupon_after'))
{
    function op_smart_coupon_add_order_coupon_after($order,$order_data,$discount_code,$discount_code_amount)
    {
        update_post_meta( $order->get_id(), 'smart_coupons_contribution', array($discount_code => $discount_code_amount) );
    }
}

add_action('op_add_order_coupon_after','op_smart_coupon_add_order_coupon_after',10,4);
if(!function_exists('op_smart_coupon_check_coupon_apply_coupon'))
{
    function op_smart_coupon_check_coupon_apply_coupon($amount,$coupon,$validate,$current)
    {
        /**
         * Apply a discount to all items using a coupon.
         *
         * @since  3.2.0
         * @param  WC_Coupon $coupon Coupon object being applied to the items.
         * @param  bool      $validate Set to false to skip coupon validation.
         * @return bool|WP_Error True if applied or WP_Error instance in failure.
         */
        $validate = true;
        $coupon_type = $coupon->get_discount_type();

        if($coupon_type == 'smart_coupon')
        {


            if($validate)
            {
                    $amount = $coupon->get_amount();
                    $amount = $amount * 100;

            }else{
                throw new Exception(__('Your coupon code is invalid'));

            }
        }



        return $amount;
    }
}

add_filter('op_check_coupon_apply_coupon','op_smart_coupon_check_coupon_apply_coupon',10,4);