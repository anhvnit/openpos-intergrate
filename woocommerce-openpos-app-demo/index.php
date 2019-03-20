<?php
/*
Plugin Name: Woocommerce OpenPos External App Demo
Plugin URI: http://openswatch.com
Description: A Sample App for OpenPOS + Woocommerce.
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: openpos-app
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

define('OPENPOS_APP_DIR',plugin_dir_path(__FILE__));
define('OPENPOS_APP_URL',plugins_url('woocommerce-openpos-app-demo'));


if(!class_exists('OP_App_Abstract'))
{
        $openpos_dir = dirname(rtrim(OPENPOS_APP_DIR , '/') ).'/woocommerce-openpos';
        require_once $openpos_dir.'/lib/abtract-op-app.php';
}

require_once OPENPOS_APP_DIR.'/my-op-app.php';

$myApp = new MyOpApp();
