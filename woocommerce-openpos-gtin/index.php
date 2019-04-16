<?php
/*
Plugin Name: Woocommerce - Openpos - WooCommerce Germanized
Plugin URI: http://openswatch.com
Description: Woocommerce - Openpos - WooCommerce Germanized Integrated
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: woocommerce-google-cloud-print
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
if(!function_exists('op_gtin_setting'))
{
    function op_gtin_setting($options){
        $options['_ts_gtin'] = 'GTIN';
        $options['_ts_mpn'] = 'MPN';
        return $options;
    }
}
add_filter('op_barcode_key_setting','op_gtin_setting',10,1);

if(!function_exists('op_gtin_op_product_data'))
{
    function op_gtin_op_product_data($product_data,$product){
        $gtin = get_post_meta($product_data['id'],'_ts_gtin',true);
        $mpn = get_post_meta($product_data['id'],'_ts_mpn',true);
        $product_data['gtin'] = $gtin ? $gtin : '';
        $product_data['mpn'] = $mpn ? $mpn : '';
        //end

        return $product_data;
    }
}
add_filter('op_product_data','op_gtin_op_product_data',10,2);


if(!function_exists('op_gtin_get_login_cashdrawer_data'))
{
    function op_gtin_get_login_cashdrawer_data($session_response_data){



        $session_response_data['setting']['product_search_fields'] = array(
            'gtin',
            'mpn'
        );

        return $session_response_data;
    }
}
add_filter('op_get_login_cashdrawer_data','op_gtin_get_login_cashdrawer_data',10,1);