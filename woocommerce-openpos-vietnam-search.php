<?php
/*
Plugin Name: Woocommerce - Openpos - Tìm kiếm sản phẩm tiếng việt
Plugin URI: http://openswatch.com
Description: Woocommerce - Openpos - Tìm kiếm sản phẩm có tên Việt Nam có dấu
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: woocommerce-openpos-vnsearch
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/



//start vietnam keyword search
if(!function_exists('custom_vnsearch_op_get_login_cashdrawer_data_grid'))
{
    function custom_vnsearch_op_get_login_cashdrawer_data_grid($session_response_data){

        $session_response_data['setting']['product_search_fields'] = array('vn_keyword');
        return $session_response_data;
    }
}
add_filter('op_get_login_cashdrawer_data','custom_vnsearch_op_get_login_cashdrawer_data_grid',12,1);

if(!function_exists('custom_vnsearch_slug'))
{
    function custom_vnsearch_slug($str) {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/[^a-z0-9-\s]/', '', $str);
        $str = preg_replace('/([\s]+)/', ' ', $str);
        $str = str_replace('  ',' ',$str);
        return $str;
    }
}


if(!function_exists('custom_vnsearch_op_product_data'))
{
    function custom_vnsearch_op_product_data($product_data,$product){
        $product_name = $product_data['name'];
        if($product_name)
        {
            $product_data['vn_keyword'] = custom_vnsearch_slug($product_name);
        }
        return $product_data;
    }
}
add_filter('op_product_data','custom_vnsearch_op_product_data',10,2);

//