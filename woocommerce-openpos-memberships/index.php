<?php
/*
Plugin Name: Woocommerce - Openpos - WooCommerce Memberships
Plugin URI: http://openswatch.com
Description: Woocommerce - Openpos - WooCommerce Memberships Integrated
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: woocommerce-google-cloud-print
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
if(!function_exists('membership_op_customer_data'))
{
    function membership_op_customer_data($customer_data){
        $customer_id = $customer_data['id'];
        $memberships = wc_memberships_get_user_active_memberships($customer_id);
        $discounts = array();
        $cart_data = json_decode(stripslashes($_REQUEST['cart']),true);
        $grand_total = isset($cart_data['grand_total']) ? $cart_data['grand_total'] : 0;
        $items = isset($cart_data['items']) ? $cart_data['items'] : array();
        $badge = '';
        if($grand_total > 0 && !empty($items))
        {
            if($memberships && is_array($memberships) && !empty($memberships))
            {
                foreach($memberships as $membership)
                {
                    $plan = $membership->plan;
                    $rules = $plan->get_rules();
                    $product_ids = array();
                    $item_ids = array();

                    foreach($items as $key => $item)
                    {
                        $item_id = $item['id'];
                        $product_id = $item['product_id'];

                        $item_discount = $item['final_discount_amount'];
                        if($item_discount == 0 && $item['qty'] > 0)
                        {
                            $product_ids[ $key] = $product_id;
                            $item_ids[ $key] = $item_id;

                        }

                    }

                    $badge = $plan->get_name();
                    foreach($rules as $rule_key => $rule)
                    {
                        $rule_type = $rule->get_rule_type();
                        if($rule_type == 'purchasing_discount' && $rule->is_active())
                        {
                            $content_type_name = $rule->get_content_type_name();
                            $discount_amount = $rule->get_discount_amount();
                            $discount_type = $rule->get_discount_type();
                            $object_ids = $rule->get_object_ids();

                            if(empty($object_ids))
                            {
                                foreach($product_ids as $item_key => $product_id)
                                {
                                    $item_id = $item_ids[$item_key];
                                    $item_id = strval($item_id);
                                    $discounts[$item_id] = array(
                                        'item_id' => $item_id,
                                        'discount_amount' => 1 * $discount_amount,
                                        'discount_type' => ($discount_type == 'percentage' ) ? 'percent' : 'fixed'
                                    );

                                }
                            }else{
                                if($content_type_name == 'product')
                                {
                                    foreach($product_ids as $item_key => $product_id)
                                    {
                                        $item_id = $item_ids[$item_key];
                                        $item_id = strval($item_id);
                                        if(in_array($product_id,$object_ids))
                                        {
                                            $discounts[$item_id] = array(
                                                'item_id' => $item_id,
                                                'discount_amount' => 1 * $discount_amount,
                                                'discount_type' => ($discount_type == 'percentage' ) ? 'percent' : 'fixed'
                                            );
                                        }
                                    }
                                }elseif($content_type_name == 'product_cat')
                                {
                                    foreach($product_ids as $item_key => $product_id)
                                    {
                                        $item_id = $item_ids[$item_key];
                                        $item_id = strval($item_id);
                                        $product = wc_get_product($product_id);
                                        $cat_ids = $product->get_category_ids();
                                        $result = array_intersect($cat_ids, $object_ids);
                                        if(!empty($result))
                                        {
                                            $discounts[$item_id] = array(
                                                'item_id' => $item_id,
                                                'discount_amount' => 1 * $discount_amount,
                                                'discount_type' => ($discount_type == 'percentage' ) ? 'percent' : 'fixed'
                                            );
                                        }
                                    }
                                }


                            }

                        }

                    }

                }

            }
        }

        if(!empty($discounts))
        {
            $customer_data['item_discount'] =  $discounts;
        }
        if($badge != '')
        {
            $customer_data['badge'] = $badge;
        }
        return $customer_data;
    }
}
add_filter('op_customer_data','membership_op_customer_data',10,1);

