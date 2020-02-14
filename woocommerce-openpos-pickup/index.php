<?php
/*
Plugin Name: Woocommerce - Openpos - Pickup
Plugin URI: http://openswatch.com
Description: Woocommerce - Openpos - Pickup
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.2
WC requires at least: 2.6
Text Domain: woocommerce-openpos-pickup
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
define('OPENPOS_PICKUP_DIR',plugin_dir_path(__FILE__));
define('OPENPOS_PICKUP_URL',plugins_url('woocommerce-openpos-pickup'));
require_once( OPENPOS_PICKUP_DIR.'includes/pickup.php' );

$tmp = new OP_Pickup();
$tmp->init();