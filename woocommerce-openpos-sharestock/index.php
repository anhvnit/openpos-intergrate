<?php
/*
Plugin Name: Woocommerce - Openpos -  Share stock outlet with online
Plugin URI: http://openswatch.com
Description: Woocommerce - Openpos -  Share stock outlet with online
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.2
WC requires at least: 2.6
Text Domain: woocommerce-openpos-sharestock
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

//define('OPENPOS_PHYSICAL_OUTLET_ID',plugin_dir_path(__FILE__));
define('OPENPOS_PHYSICAL_OUTLET_ID','5896'); // change 5896 to your deault outlet id at here

//woocommerce_product_is_in_stock
function custom_pos_woocommerce_product_is_in_stock($status,$product){
    global $op_warehouse;
    if ( ! is_admin() ) {
        if($status != 'instock')
        {
            $_product_id = $product->get_id();
            
            $qty = $op_warehouse->get_qty(OPENPOS_PHYSICAL_OUTLET_ID,$_product_id);
            if($qty > 0)
            {
                $status = 'instock';
            }
        }
    }
    return $status;
}
add_filter('woocommerce_product_is_in_stock','custom_pos_woocommerce_product_is_in_stock',10,2);
//woocommerce_stock_amount
//stock_quantity
function custom_pos_woocommerce_stock_amount($qty,$current){
    global $op_warehouse;
    global $op_in_op_stock_plugin;
    if ( ! is_admin() && !$op_in_op_stock_plugin ) {
       
        if($qty <= 0)
        {
            $product = $current;
            $_product_id = $product->get_id();
            
            $outlet_qty = $op_warehouse->get_qty(OPENPOS_PHYSICAL_OUTLET_ID,$_product_id);
           
            if($outlet_qty > 0)
            {
                $qty = $outlet_qty;
            }
        }
    }
    return $qty;
}

add_filter('woocommerce_product_get_stock_quantity','custom_pos_woocommerce_stock_amount',10,2);

function custom_pos_woocommerce_product_variation_get_stock_quantity($qty,$current){
    global $op_warehouse;
    global $op_in_op_stock_plugin;
    if ( ! is_admin() && !$op_in_op_stock_plugin ) {
        if($qty <= 0)
        {
            $product = $current;
            $_product_id = $product->get_id();
            $outlet_qty = $op_warehouse->get_qty(OPENPOS_PHYSICAL_OUTLET_ID,$_product_id);
            if($outlet_qty > 0)
            {
                $qty = $outlet_qty;
            }
        }
    }

    return $qty;
}
add_filter('woocommerce_product_variation_get_stock_quantity','custom_pos_woocommerce_product_variation_get_stock_quantity',10,2);


function custom_pos_woocommerce_order_item_quantity($qty,$order, $item){
    global $op_warehouse;
    global $op_in_op_stock_plugin;
    if ( ! is_admin() ) {
        $product            = $item->get_product();
        if ( ! is_a( $product, 'WC_Product' ) ) {
            $product = wc_get_product( $product );
        }
        $op_in_op_stock_plugin = true;
       
        if($product && $product->managing_stock())
        {
                $_product_id = $product->get_id();
                $physic_qty = $op_warehouse->get_qty(OPENPOS_PHYSICAL_OUTLET_ID,$_product_id);
                $product_qty = $product->get_stock_quantity();
                if($qty > $product_qty)
                {
                    $physic_qty_reduct = 1 * ($qty - $product_qty);
                    if($physic_qty >= $physic_qty_reduct)
                    {
                        $qty = $product_qty;
                        //reduct physical qty;
                        $op_warehouse->set_qty(OPENPOS_PHYSICAL_OUTLET_ID,$_product_id,($physic_qty - $physic_qty_reduct));
                        $item->add_meta_data( '_op_reduced_physical_stock', $physic_qty_reduct, true );
                        $item->save();
                    }
                }
        }
       
    }
    
    return $qty;
}

add_filter('woocommerce_order_item_quantity','custom_pos_woocommerce_order_item_quantity',10,3);

function custom_pos_woocommerce_after_order_itemmeta($item_id, $item, $product){
    $_op_reduced_physical_stock =   $item->get_meta( '_op_reduced_physical_stock');
    if($_op_reduced_physical_stock && $_op_reduced_physical_stock > 0)
    {
        echo '<p>'.__('Get Stock From Physical store: ','openpos').'<strong>'.$_op_reduced_physical_stock.'</strong></p>';
    }
}
add_action('woocommerce_after_order_itemmeta','custom_pos_woocommerce_after_order_itemmeta',10,3);
