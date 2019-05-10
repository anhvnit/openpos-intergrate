<?php
/*
Plugin Name: Woocommerce - Openpos -  Woocommerce Role Pricing PRO
Plugin URI: http://openswatch.com
Description: Woocommerce - Openpos -  Woocommerce Role Pricing PRO Integrated
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: woocommerce-google-cloud-print
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/



function WooRolePricing_op_woocommerce_get_price ( $price, $product ) {
    $baseprice = $price;
    $result = $baseprice;

    if ( $product->is_type( 'variation' ) ) {
        $commission = WRP_Variations_Admin::get_commission( $product, $product->variation_id );
    } else {
        $commission = WooRolePricing::get_commission( $product );
    }

    if ( $commission ) {

        $baseprice = $product->get_regular_price();

        if ( $product->get_sale_price() != $product->get_regular_price() && $product->get_sale_price() == $product->price ) {
            if ( get_option( "wrp-baseprice", "regular" )=="sale" ) {
                $baseprice = $product->get_sale_price();
            }
        }
        $product_price = $baseprice;

        $type = get_option( "wrp-method", "rate" );
        $result = 0;
        if ($type == "rate") {
            // if rate and price includes taxes
            if ( $product->is_taxable() && get_option('woocommerce_prices_include_tax') == 'yes' ) {
                $_tax       = new WC_Tax();
                $tax_rates  = $_tax->get_base_tax_rates( $product->tax_class );
                $taxes      = $_tax->calc_tax( $baseprice, $tax_rates, true );
                $product_price      = $_tax->round( $baseprice - array_sum( $taxes ) );
            }

            $result = WooRolePricing::bcmul($product_price, $commission, WOO_ROLE_PRICING_DECIMALS);

            if ( $product->is_taxable() && get_option('woocommerce_prices_include_tax') == 'yes' ) {
                $_tax       = new WC_Tax();
                $tax_rates  = $_tax->get_base_tax_rates( $product->tax_class );
                $taxes      = $_tax->calc_tax( $result, $tax_rates, false ); // important false
                $result      = $_tax->round( $result + array_sum( $taxes ) );
            }
        } else {
            if ( get_option( "wrp-haveset", "discounts" ) === 'discounts' ) {
                $result = WooRolePricing::bcsub($product_price, $commission, WOO_ROLE_PRICING_DECIMALS);
            } else {
                $result = $commission;
            }
        }
    }else{
        return 0;
    }
    return $result;
}

if(!function_exists('WooRolePricing_op_customer_data'))
{
    function WooRolePricing_op_customer_data($customer_data){
        global $current_user;
        global $op_woo;

        $customer_id = $customer_data['id'];
        wp_set_current_user( $customer_id );

        $discounts = array();
        $cart_data = json_decode(stripslashes($_REQUEST['cart']),true);
        $grand_total = isset($cart_data['grand_total']) ? $cart_data['grand_total'] : 0;
        $items = isset($cart_data['items']) ? $cart_data['items'] : array();

        if($grand_total > 0 && !empty($items))
        {

                    $product_ids = array();
                    $product_prices = array();
                    $item_ids = array();
                    foreach($items as $key => $item)
                    {
                        $item_id = $item['id'];
                        $product_id = $item['product_id'];

                        $item_discount = $item['final_discount_amount'];
                        if($item_discount == 0 && $item['qty'] > 0)
                        {
                            $product_ids[$key] = $product_id;
                            $product_prices[$key] = $item['final_price'];

                            $item_ids[ $key] = $item_id;
                        }
                    }
                    foreach($product_ids as $key => $product_id)
                    {
                        $item_id = $item_ids[$key];
                        $product = wc_get_product($product_id);
                        $final_price = WooRolePricing_op_woocommerce_get_price($product_prices[$key],$product);
                        if($final_price == 0)
                        {
                            continue;
                        }
                        $tax_amount = 0;

                        if(wc_tax_enabled() )
                        {
                            $price_included_tax = wc_prices_include_tax();
                            if($price_included_tax)
                            {
                                $tax_rates = $op_woo->getTaxRates( $product->get_tax_class() );

                                if(!empty($tax_rates))
                                {
                                    $keys = array_keys($tax_rates);
                                    $rate_id = max($keys);
                                    $rate = $tax_rates[$rate_id];

                                    $tax_amount = array_sum(@WC_Tax::calc_tax( $final_price, array($rate_id => $rate), wc_prices_include_tax() ));
                                    $tax_amount = wc_round_tax_total($tax_amount);

                                }

                                $final_price -= $tax_amount;



                            }

                        }


                        $tmp_item = array(
                            'item_id' => $item_id,
                            'product_id' => $product_id,
                            'final_price' => $final_price
                        );


                        if($tax_amount > 0)
                        {
                            $tmp_item['tax_amount'] = $tax_amount;
                        }
                        $discounts[''.$item_id] = $tmp_item;
                    }
        }

        if(!empty($discounts))
        {
            $customer_data['items_price'] =  $discounts;
        }

        $current_user = null;
        wp_set_current_user( 0 );

        return $customer_data;
    }
}
add_filter('op_customer_data','WooRolePricing_op_customer_data',10,1);

