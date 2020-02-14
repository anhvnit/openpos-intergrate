<?php
/*
Plugin Name: Woocommerce OpenPos Pending Payment 
Plugin URI: http://openswatch.com
Description: Woocommerce OpenPos Pending Payment 
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.1
WC requires at least: 2.6
WC tested up to: 3.8.1
Text Domain: openpos-pending-payment
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if(!function_exists('pending_payment_op_login_format_payment_data'))
{
    function pending_payment_op_login_format_payment_data($payment_methods){
        $payment_method_data = array(
            'code' => 'pending_payment',
            'name' => 'Pending Payment',
            'type' => 'online',
            'hasRef' => true,
            'partial' => false,
            'description' => 'Pending Payment',
            'online_type' => 'external',
            'offline_transaction' => 'no',
            'offline_order' => 'no'
        );
        $payment_methods[] = $payment_method_data;
        return $payment_methods;
    }
}
add_filter('op_pos_payment_method_list','pending_payment_op_login_format_payment_data',10,1);



