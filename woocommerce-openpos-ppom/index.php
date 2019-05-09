<?php
/*
Plugin Name: Woocommerce + openpos + N-Media WooCommerce PPOM
Plugin URI: http://openswatch.com
Description: Woocommerce + openpos + N-Media WooCommerce PPOM
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: openpos-ppom
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
if(!function_exists('PPOM_wc_product_addons'))
{
    function PPOM_wc_product_addons($response_data,$_product){
        $product_id = $_product->ID;
        $product = wc_get_product($_product->ID);
        $ppom		= new PPOM_Meta( $product_id );

        if( $ppom->is_exists ) {
            $ppom		= new PPOM_Meta( $product_id );
            $existing_fields = $ppom->fields;
            if($existing_fields)
            {

                foreach ($existing_fields as $field)
                {
                    $field_type = $field['type'];
                    $option = array();
                    $required = ($field['required'] && $field['required'] == 'on') ? true : false;
                    $title = isset($field['title']) ? $field['title'] : '';
                    if(!$title)
                    {
                        $title = isset($field['label']) ? $field['label'] : '';
                    }
                    switch ($field_type)
                    {
                        case 'text':
                            $option = array(
                                'label' => $title,
                                'option_id' => isset($field['data_name']) ? $field['data_name']: esc_attr($field['label']),
                                'type' => $field_type,
                                'require' => $required,
                                'options' => array()
                            );
                            break;
                        case 'select':
                            $option = array(
                                'label' => $title,
                                'option_id' => isset($field['data_name']) ? $field['data_name']: esc_attr($field['label']),
                                'type' => $field_type,
                                'require' => $required,
                                'options' => array()
                            );
                            $options = $field['options'];
                            foreach($options as $o)
                            {
                                $tmp = array(
                                    'value_id' => $o['id'],
                                    'label' => $o['option'],
                                    'cost' => $o['price'],
                                );
                                $option['options'][] = $tmp;
                            }
                            break;
                        case 'radio':
                            $option = array(
                                'label' => $title,
                                'option_id' => isset($field['data_name']) ? $field['data_name']: esc_attr($field['label']),
                                'type' => $field_type,
                                'require' => $required,
                                'options' => array()
                            );
                            $options = $field['options'];
                            foreach($options as $o)
                            {
                                $tmp = array(
                                    'value_id' => $o['id'],
                                    'label' => $o['option'],
                                    'cost' => $o['price'],
                                );
                                $option['options'][] = $tmp;
                            }
                            break;
                        case 'checkbox':
                            $option = array(
                                'label' => $title,
                                'option_id' => isset($field['data_name']) ? $field['data_name']: esc_attr($field['label']),
                                'type' => $field_type,
                                'require' => $required,
                                'options' => array()
                            );
                            $options = $field['options'];
                            foreach($options as $o)
                            {
                                $tmp = array(
                                    'value_id' => $o['id'],
                                    'label' => $o['option'],
                                    'cost' => $o['price'],
                                );
                                $option['options'][] = $tmp;
                            }
                            break;
                    }
                    if(!empty($option))
                    {
                        $response_data['options'][] = $option;
                    }

                }
            }
        }
        return $response_data;
    }
}
add_filter('op_product_data','PPOM_wc_product_addons',10,2);