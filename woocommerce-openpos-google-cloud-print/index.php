<?php
/*
Plugin Name: Woocommerce OpenPos - Google Cloud Printer
Plugin URI: http://openswatch.com
Description: Woocommerce OpenPos - Google Cloud Printer
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: openpos-google-print
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

define('OPENPOS_GOOGLE_PRINT_DIR',plugin_dir_path(__FILE__));
define('OPENPOS_GOOGLE_PRINT_URL',plugins_url('woocommerce-openpos-google-cloud-print'));

//require(OPENPOS_CLOUDPRNT_DIR.'vendor/autoload.php');
require_once OPENPOS_GOOGLE_PRINT_DIR.'google-cloud-print/GoogleCloudPrint.php';
require_once( OPENPOS_GOOGLE_PRINT_DIR.'includes/Printer.php' );
global $_op_google_printer;
global $_op_gcp;
$_op_gcp = new GoogleCloudPrint();
$_op_google_printer = new OP_Google_Printer();
$_op_google_printer->init();
if(!function_exists('custom_op_get_login_cashdrawer_data_google_print'))
{
    function custom_op_get_login_cashdrawer_data_google_print($session_response_data){
        global $_op_google_printer;
        $setting = $_op_google_printer->get_setting();
        if(isset($setting['active']) && $setting['active'])
        {
            $session_response_data['setting']['pos_cloud_print'] = array(
                'url' =>  OPENPOS_GOOGLE_PRINT_URL.'/pos.php'
            );
        }
        return $session_response_data;
    }
}
add_filter('op_get_login_cashdrawer_data','custom_op_get_login_cashdrawer_data_google_print',11,1);

