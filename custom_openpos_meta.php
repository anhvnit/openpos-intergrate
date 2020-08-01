<?php
/*
Plugin Name: Woocommerce OpenPos YITH Product Barcode
Plugin URI: http://openswatch.com
Description: Woocommerce OpenPos YITH Product Barcode
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: openpos-pricebook
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

//_ywbc_barcode_display_value
function custom_op_barcode_key_setting($fields){
    $fields['_wepos_barcode'] = "_ywbc_barcode_display_value";
    return $fields;
}
add_filter( 'op_barcode_key_setting', 'custom_op_barcode_key_setting' ,10,1);

function custom_op_product_barcode($barcode,$productId){
    
    $barcode_obj = is_numeric ( $productId ) ? new YITH_Barcode( $productId ) : $productId;
    	if ( $barcode_obj instanceof YITH_Barcode ) {
    	    if ( $barcode_obj->exists () ) {
    	        $barcode     = $barcode_obj->get_display_value ();
    	    }
    	}
   
    return $barcode;
}
add_filter('op_product_barcode','custom_op_product_barcode',30,2);