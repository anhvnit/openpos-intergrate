<?php
/*
Plugin Name: Woocommerce OpenPos + WooCommerce TM Extra Product Options
Plugin URI: http://openswatch.com
Description: Woocommerce OpenPos + WooCommerce TM Extra Product Options For select , text, radio, checkbox type
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.0
WC requires at least: 2.6
Text Domain: openpos-tm-addon
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

if(function_exists('EPO'))
{
    function tm_wc_product_addons($response_data,$_product){
        $product_id = $_product->ID;
        $product = wc_get_product($_product->ID);
        $epos    = TM_EPO()->get_product_tm_epos( $product_id );
        $tm_meta = array();
		
		if ( is_array( $epos ) && isset( $epos['global_ids'] ) && is_array( $epos['global_ids'] ) ) {

			foreach ( $epos['global_ids'] as $post ) {

				$id   = $post->ID;
				$type = $post->post_type;

				$meta = tc_get_post_meta( $id, 'tm_meta', TRUE );

				if ( ! empty( $meta )
				     && is_array( $meta )
				     && isset( $meta['tmfbuilder'] )
				     && is_array( $meta['tmfbuilder'] )
				) {

					$meta    = TM_EPO_HELPER()->recreate_element_ids( $meta );
					$tm_meta = array_merge_recursive( $tm_meta, $meta );

				}

			}
        }
        $types = array(
            'checkboxes',
            'textfield',
            'selectbox',
            'radiobuttons',
        );
        $result = array();
        $result_data = array();
        if(!empty($tm_meta) && isset($tm_meta['tmfbuilder']))
        {
            $tmfbuilder = $tm_meta['tmfbuilder'];
            
            foreach($tmfbuilder['element_type'] as $t)
            {
                
                switch($t)
                {
                    case 'checkboxes':
                        $result['checkboxes'][] = $t;
                        break;
                    case 'textfield':
                        $result['textfield'][] = $t;
                        break;
                    case 'selectbox':
                        $result['selectbox'][] = $t;
                        break;
                    case 'radiobuttons':
                        $result['radiobuttons'][] = $t;
                        break;
                }
            }
            foreach($types as $t)
            {
                $chekbox_meta_key = array(
                    'multiple_'.$t.'_options_value',
                    'multiple_'.$t.'_options_price',
                    'multiple_'.$t.'_options_sale_price',
                    'multiple_'.$t.'_options_price_type',
                    'multiple_'.$t.'_options_description',
                    'multiple_'.$t.'_options_url',
                    'multiple_'.$t.'_options_imagel',
                    'multiple_'.$t.'_options_imagep',
                    'multiple_'.$t.'_options_imagec',
                    'multiple_'.$t.'_options_image',
                    'multiple_'.$t.'_options_title',
                    'multiple_'.$t.'_options_color',
                    $t.'_internal_name',
                    $t.'_header_title',
                    $t.'_required',
                );
                
                foreach($chekbox_meta_key as $ckey)
                {
                    if(isset($tmfbuilder[$ckey]))
                    {
                        $result_data[$t][$ckey] = $tmfbuilder[$ckey];
                    }
                }
                
            }
            $final_result = array();
            foreach($result as $types)
            {
                foreach($types as $type_index => $type_name)
                {
                    $tmp = array(
                        'type' => $type_name
                    );
                    if(isset($result_data[$type_name]))
                    {
                            foreach($result_data[$type_name] as $result_data_key => $result_data_value)
                            {
                                if(isset($result_data_value[$type_index]))
                                {
                                    $tmp[$result_data_key] = $result_data_value[$type_index];
                                }
                            }
                    }
                    $final_result[] = format_option_data($tmp);
                }
            }
            if(!empty($final_result))
            {
                $response_data['options'] = $final_result;
                
            }
           
        }
       
        
        return $response_data;
    }
    function format_option_data($option_data){
        $option = array();
       
        $required_key  = $option_data['type'].'_required';
        $required  = $option_data[$required_key];
        $_header_title = $option_data['type'].'_header_title';
        $_internal_name = $option_data['type'].'_internal_name';
        $title  = isset($option_data[$_header_title]) ? $option_data[$_header_title] : $option_data[$_internal_name];

        $value_options = array();

        $_options_value = 'multiple_'.$option_data['type'].'_options_value';
        $_options_price = 'multiple_'.$option_data['type'].'_options_price';
        
        if(isset($option_data[$_options_value]))
        {
           
            foreach($option_data[$_options_value] as $_option_value_key => $_option_value)
            {
                if($_option_value)
                {
                    $tmp = array(
                        'id' => sanitize_title($_option_value),
                        'option' => $_option_value,
                        'price' => isset($option_data[$_options_price][$_option_value_key]) ? $option_data[$_options_price][$_option_value_key] : 0,
                    );
                    $value_options[] = $tmp;
                }
                
            }
        }
        $required = $required == 1 ? true : false;
        switch($option_data['type']){
            case 'checkboxes':
                $field_type  = 'checkbox';
                $option = array(
                    'label' => $title,
                    'option_id' => sanitize_title($title),
                    'type' => $field_type,
                    'require' => $required,
                    'options' => array()
                );
                $options =  $value_options ;
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
            case 'textfield':
                $field_type  = 'text';
                $option = array(
                    'label' => $title,
                    'option_id' => $title,
                    'type' => $field_type,
                    'require' => $required,
                    'options' => array()
                );
                break;
            case 'selectbox':
                $field_type  = 'select';
                $option = array(
                    'label' => $title,
                    'option_id' => $title,
                    'type' => $field_type,
                    'require' => $required,
                    'options' => array()
                );
                $options =  $value_options ;
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
            case 'radiobuttons':
                $field_type  = 'radio';
                $option = array(
                    'label' => $title,
                    'option_id' => $title,
                    'type' => $field_type,
                    'require' => $required,
                    'options' => array()
                );
                $options =  $value_options ;
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
        return $option;
    }
    add_filter('op_product_data','tm_wc_product_addons',10,2);
}
