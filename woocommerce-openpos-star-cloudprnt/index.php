<?php
/*
Plugin Name: Woocommerce OpenPos - Star CloudPRNT
Plugin URI: http://openswatch.com
Description: Star CloudPRNT for OpenPOS
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.3
WC requires at least: 2.6
Text Domain: openpos-star-cloudprnt
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
// pos_kitchen_cloud_print
define('OPENPOS_CLOUDPRNT_DIR',plugin_dir_path(__FILE__));
define('OPENPOS_CLOUDPRNT_URL',plugins_url('woocommerce-openpos-star-cloudprnt'));

require(OPENPOS_CLOUDPRNT_DIR.'vendor/autoload.php');

include_once(OPENPOS_CLOUDPRNT_DIR.'cloudprnt/cloudprnt_conf.inc.php');
include_once(OPENPOS_CLOUDPRNT_DIR.'cloudprnt/printer.inc.php');
include_once(OPENPOS_CLOUDPRNT_DIR.'cloudprnt/printer_queue.inc.php');

require_once( OPENPOS_CLOUDPRNT_DIR.'includes/Printer.php' );
global $_op_printer;
$_op_printer = new OP_Printer();
$_op_printer->init();
if(!function_exists('custom_op_get_login_cashdrawer_data_cloud_print'))
{
    function custom_op_get_login_cashdrawer_data_cloud_print($session_response_data){
        global $_op_printer;
        $setting = $_op_printer->get_setting();
        if(isset($setting['active']) && $setting['active'])
        {
            $session_response_data['setting']['pos_cloud_print'] = array(
                'url' =>  OPENPOS_CLOUDPRNT_URL.'/pos.php'
            ); // comment this if turn off for cashier
            $session_response_data['setting']['pos_kitchen_cloud_print'] = array(
                'url' =>  OPENPOS_CLOUDPRNT_URL.'/pos.php'
            ); // comment this if turn off for table / desk print
            
        }
        return $session_response_data;
    }
}
add_filter('op_get_login_cashdrawer_data','custom_op_get_login_cashdrawer_data_cloud_print',11,1);

