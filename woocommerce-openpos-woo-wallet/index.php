<?php
/*
Plugin Name: Woocommerce - Openpos -  TeraWallet
Plugin URI: http://openswatch.com
Description: Woocommerce - Openpos -  TeraWallet
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.2
WC requires at least: 2.6
Text Domain: woocommerce-openpos-woo-wallet
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

//define('OPENPOS_PHYSICAL_OUTLET_ID',plugin_dir_path(__FILE__));
if(!function_exists('op_wc_terawallet_user_data'))
{
    function op_wc_terawallet_user_data($user_data){
        $woo_wallet = $GLOBALS['woo_wallet'];

        $customer_id = $user_data['id'];

        // Show balance
        $balance = $woo_wallet->wallet->get_wallet_balance( $customer_id, 'edit');
        
        $user_data['summary_html'] = ' Credit Balance: <span style="color:red;font-weight:bold;"><b>'. $balance.'</b></span>';
        return $user_data;
    }
}

add_filter('op_customer_data','op_wc_terawallet_user_data',10,1);


if(!function_exists('terawallet_op_login_format_payment_data'))
{
    function terawallet_op_login_format_payment_data($payment_method_data,$methods){
        $formatted_payment = $payment_method_data;
        $code = 'wallet';

        if( in_array($formatted_payment['code'],array($code)))
        {
            $formatted_payment['type'] = 'online';
            $formatted_payment['partial'] = 'yes';
            $formatted_payment['online_type'] = 'direct';
        }
        return $formatted_payment;
    }
}
add_filter('op_login_format_payment_data','terawallet_op_login_format_payment_data',10,2);


function terawallet_op_payment_order_payment_method($payment_method,$order_parse_data,$amount,$payment_data){
   
    foreach($payment_method as $key => $method)
    {
        if($method['code'] == 'wallet')
        {
            $_method = $method;
            $customer_id = isset($order_parse_data['customer']) && isset($order_parse_data['customer']['id']) ? $order_parse_data['customer']['id'] : 0;
            if($customer_id )
            {
                $woo_wallet = $GLOBALS['woo_wallet'];
                $balance = $woo_wallet->wallet->get_wallet_balance( $customer_id, 'edit');
                $order_id = $order_parse_data['order_id'];
                
                if($balance >= $amount && $order_id)
                {
                    $order = wc_get_order($order_id );
                    $wallet_response = woo_wallet()->wallet->debit( $customer_id, $amount,  'For order payment #'.$order_id , $order);
                    $_method['ref'] = $wallet_response;
                    
                }else{
                    $_method['ref'] = '';
                }
                $payment_method[$key] = $_method;
                
            }
        }
    }
    return $payment_method;
}
add_filter('op_payment_order_payment_method','terawallet_op_payment_order_payment_method',10,4);
function terawallet_op_payment_order_result($result,$order_parse_data,$amount,$payment_data,$payment_method){
    foreach($payment_method as $key => $method)
    {
        if($method['code'] == 'wallet')
        {

            $customer_id = isset($order_parse_data['customer']) && isset($order_parse_data['customer']['id']) ? $order_parse_data['customer']['id'] : 0;
            if($customer_id )
            {
                if($method['ref'])
                {
                    $result['status'] = 1;
                }else{
                    $result['status'] = 0;
                    $result['message'] = 'Not enough credit to checkout';
                }
            }else{
                $result['status'] = 0;
                    $result['message'] = 'Please add customer to checkout with wallet';
            }
            
        }
    }
    return $result;

}
add_filter('op_payment_order_result','terawallet_op_payment_order_result',10,5);


//filter : op_payment_order_result
// action : op_completed_payment_order_after