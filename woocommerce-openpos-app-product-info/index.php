<?php
/*
Plugin Name: Woocommerce + openpos + Display Product Info App
Plugin URI: http://openswatch.com
Description: Display Product Info App
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.1
WC requires at least: 2.6
Text Domain: openpos-product-info
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
define('OPENPOS_APP_PRODUCT_VIEW_DIR',plugin_dir_path(__FILE__));
define('OPENPOS_APP_PRODUCT_VIEW_URL',plugins_url('woocommerce-openpos-app-product-info'));


if(!class_exists('OP_App_Abstract'))
{
        $openpos_dir = dirname(rtrim(OPENPOS_APP_PRODUCT_VIEW_DIR , '/') ).'/woocommerce-openpos';
        require_once $openpos_dir.'/lib/abtract-op-app.php';
}


add_action( 'wp_ajax_nopriv_op_custom_product_view_search', 'op_product_view_search');
add_action( 'wp_ajax_op_custom_product_view_search','op_product_view_search' );

require_once OPENPOS_APP_PRODUCT_VIEW_DIR.'/product-view-app.php';

$myApp = new OP_Product_View_App();

function op_product_view_search(){
    $keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';
    if($keyword)
    {
        global $op_woo;
        $data_store = new OP_WC_Product_Data_Store_CPT();//WC_Data_Store::load( 'product' );
     
        $result_number = 10;
        $ids        = $data_store->search_products( $keyword, '', false, false, $result_number );
        $products        = array();
        foreach ( $ids as $product_id ) {
            if($product_id)
            {
                    $product_post = get_post($product_id);
                    if($product_post)
                    {
                        $tmp_product = $op_woo->get_product_formatted_data($product_post,0);
                        if($tmp_product)
                        {
                            $products[] = $tmp_product;
                        }
                        
                    }
            }
        }
        if(empty($products))
        {
            echo 'No product found with term :"'.$keyword.'"';
        }else{
            foreach($products as $product){
                    $product_id = $product['id'];
                    $_product = wc_get_product($product_id);
                ?>
                <div class="row result-item">
                            <div class="col-md-4 col-sm-4">
                                <img alt="140x140" class="img-rounded" style="width: 140px; height: 140px;" src="<?php echo esc_url($product['image']); ?>" data-holder-rendered="true">
                            </div>
                            <div class="col-md-8 col-sm-8">
                                <h4><?php echo $product['name'];?></h4>
                                <div class="product-info-details">
                                <dl class="dl-horizontal">
                                    <dt>Price</dt>
                                    <dd><?php echo $_product->get_price_html(); ?></dd>
                                </dl>
                                <dl class="dl-horizontal">
                                    <dt>Barcode</dt>
                                    <dd><?php echo $_product->get_sku(); ?></dd>
                                </dl>
                                <dl class="dl-horizontal">
                                    <dt>SKU</dt>
                                    <dd><?php echo $product['barcode']; ?></dd>
                                </dl>
                                <dl class="dl-horizontal">
                                    <dt>Category</dt>
                                    <dd><?php echo $_product->get_categories( ', ' ); ?></dd>
                                </dl>
                                <dl class="dl-horizontal">
                                    <dt>Tag</dt>
                                    <dd><?php echo $_product->get_tags( ', ' ); ?></dd>
                                </dl>

                                <dl class="dl-horizontal">
                                    <dt>Custom Meta Data</dt>
                                    <dd> <?php echo esc_html( get_post_meta($product_id, 'custom_code', true) ); ?></dd>
                                </dl>
                                </div>
                            </div>
                </div>
                <?php
            }
           
        }
    }else{
        echo 'Please enter keyword to search';
    }
    
    exit;
}