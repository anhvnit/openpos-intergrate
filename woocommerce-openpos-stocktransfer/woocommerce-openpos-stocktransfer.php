<?php
/*
Plugin Name: Woocommerce OpenPos Stock Transfer Management
Plugin URI: http://openswatch.com
Description: Stock transfer between outlet in OpenPOS plugin
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: openpos-stransfer
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

define('OPENPOS_TRANSFER_DIR',plugin_dir_path(__FILE__));
define('OPENPOS_TRANSFER_URL',plugins_url('woocommerce-openpos-stocktransfer'));

require(OPENPOS_TRANSFER_DIR.'vendor/autoload.php');

require_once( OPENPOS_TRANSFER_DIR.'lib/db.php' );
require_once( OPENPOS_TRANSFER_DIR.'includes/Transfer.php' );
register_activation_hook( __FILE__, array( 'OP_Transfer_Db', 'install' ) );

if(!function_exists('is_plugin_active'))
{
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

global $_has_openpos;
$_has_openpos = false;
if(is_plugin_active( 'woocommerce-openpos/woocommerce-openpos.php' ))
{
    $_has_openpos = true;
}

$_op_transfer = new OP_Transfer();
$_op_transfer->init();


?>