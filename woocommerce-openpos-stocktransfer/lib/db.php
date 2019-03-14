<?php
if(!class_exists('OP_Transfer_Db'))
{
    class OP_Transfer_Db{

        public static function install(){
            global $_has_openpos;

            global $wpdb;

            if(!$_has_openpos)
            {
                wp_die( __( 'Sorry, Please install <a  target="_blank" href="https://codecanyon.net/item/openpos-a-complete-pos-plugins-for-woocomerce/22613341">OpenPOS - WooCommerce Point Of Sale(POS)</a> before active this plugin.','openpos' ) );
            }

            $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}openpos_stock_transfers` (
              `transfer_id` int(11) NOT NULL AUTO_INCREMENT,
              `title` varchar(255) NOT NULL,
              `note` text NOT NULL,
              `from_warehouse_id` int(11) NOT NULL,
              `to_warehouse_id` int(11) NOT NULL,
              `staffs` text NOT NULL,
              `transfer_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
              `transfer_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
              `received_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
              `received_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
              `transfer_status` int(11) NOT NULL DEFAULT '0',
              `created_by` int(11) NOT NULL DEFAULT '0',
              `received_by` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`transfer_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

            $wpdb->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}openpos_stock_transfer_products` (
              `transfer_product_id` int(11) NOT NULL AUTO_INCREMENT,
              `transfer_id` int(11) NOT NULL,
              `product_id` int(11) NOT NULL,
              `product_barcode` varchar(255) NOT NULL,
              `product_sku` varchar(255) NOT NULL DEFAULT '',
              `product_qty` decimal(16,2) NOT NULL,
              `product_qty_before_send` decimal(16,2) NOT NULL,
              PRIMARY KEY (`transfer_product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

            $wpdb->query($sql);
        }

        public function save_draft_transfer($from_warehouse_id = 0){
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . "openpos_stock_transfers",
                array(
                    'title' => 'temp',
                    'from_warehouse_id' => $from_warehouse_id
                )
            );
            return $wpdb->insert_id;

        }
        public function save_transfer($data){
            global $wpdb;
            if(isset($data['transfer_id']) && $data['transfer_id'] > 0 )
            {
                $wpdb->replace(
                    $wpdb->prefix . "openpos_stock_transfers",
                    array(
                        'transfer_id' => (int)$data['transfer_id'],
                        'title' => esc_textarea($data['title']),
                        'from_warehouse_id' => $data['from_warehouse_id'],
                        'to_warehouse_id' => $data['to_warehouse_id'],
                        'staffs' => $data['staffs'],
                        'created_by' => $data['created_by'],
                        'transfer_status' => (int)$data['status'],
                        'note' => esc_textarea($data['note']),
                    )
                );
                return $data['transfer_id'];
            }else{
                $wpdb->insert(
                    $wpdb->prefix . "openpos_stock_transfers",
                    array(
                        'transfer_id' => (int)$data['transfer_id'],
                        'title' => esc_textarea($data['title']),
                        'from_warehouse_id' => $data['from_warehouse_id'],
                        'to_warehouse_id' => $data['to_warehouse_id'],
                        'staffs' => $data['staffs'],
                        'created_by' => $data['created_by'],
                        'transfer_status' => (int)$data['status'],
                        'note' => esc_textarea($data['note']),
                    )
                );
                return $wpdb->insert_id;
            }
        }
        public function save_transfer_items($data){
            global $wpdb;
            $wpdb->delete( $wpdb->prefix . "openpos_stock_transfer_products", array( 'transfer_id' => $data['transfer_id'], 'product_id' => $data['product_id'] ) );

            $wpdb->insert(
                $wpdb->prefix . "openpos_stock_transfer_products",
                array(
                    'transfer_id' => $data['transfer_id'],
                    'product_id' => $data['product_id'],
                    'product_qty' => $data['product_qty'],
                    'product_barcode' => $data['product_barcode'],
                    'product_sku' => $data['product_sku']
                )
            );
            return $wpdb->insert_id;

        }

        public function update_transfer_item_qty($item_id,$qty){
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . "openpos_stock_transfer_products",
                array(
                    'product_qty' => $qty,	// string
                ),
                array( 'transfer_product_id' => (int)$item_id )
            );

        }
        public function update_transfer_item_before_qty($item_id,$qty){
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . "openpos_stock_transfer_products",
                array(
                    'product_qty_before_send' => $qty,	// string
                ),
                array( 'transfer_product_id' => (int)$item_id )
            );

        }

        public function delete_transfer_item($item_id)
        {
            global $wpdb;
            $wpdb->delete( $wpdb->prefix . "openpos_stock_transfer_products", array( 'transfer_product_id' => (int)$item_id ) );
        }

        public function decline_transfer($transfer_id){
            global $wpdb;
            $current_user_id = get_current_user_id();
            $wpdb->update(
                $wpdb->prefix . "openpos_stock_transfers",
                array(
                    'transfer_status' => 4,
                    'received_by' => $current_user_id
                ),
                array( 'transfer_id' => (int)$transfer_id )
            );
        }
        public function receive_transfer($transfer_id){
            global $wpdb;
            $current_user_id = get_current_user_id();
            $wpdb->update(
                $wpdb->prefix . "openpos_stock_transfers",
                array(
                    'transfer_status' => 3,
                    'received_by' => $current_user_id
                ),
                array( 'transfer_id' => (int)$transfer_id )
            );
        }
        public function send_transfer($transfer_id){
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . "openpos_stock_transfers",
                array(
                    'transfer_status' => 2
                ),
                array( 'transfer_id' => (int)$transfer_id )
            );
        }
        public function delete_transfer($transfer_id)
        {
            global $wpdb;
            $wpdb->delete( $wpdb->prefix . "openpos_stock_transfers", array( 'transfer_id' => (int)$transfer_id ) );
            $wpdb->delete( $wpdb->prefix . "openpos_stock_transfer_products", array( 'transfer_id' => (int)$transfer_id ) );
        }

        public function getAllAllowTransferProducts($transfer_id,$exclude_zero = true){
            global $wpdb;
            $sql = "SELECT * FROM ".$wpdb->prefix."openpos_stock_transfer_products WHERE transfer_id = ".(int)$transfer_id;
            if($exclude_zero)
            {
                $sql .= ' AND product_qty <> 0 ';
            }
            $sql .= ' ORDER BY product_qty DESC';

            $rows = $wpdb->get_results( $sql,ARRAY_A );
            return $rows;

        }
        public function getTransferProducts($params){
            global $wpdb;
            $sql_count = "SELECT COUNT(*)  FROM ".$wpdb->prefix."openpos_stock_transfer_products WHERE transfer_id = ".(int)$params['transfer_id'];


            $sql = "SELECT * FROM ".$wpdb->prefix."openpos_stock_transfer_products WHERE transfer_id = ".(int)$params['transfer_id'];


            $sql .= ' ORDER BY product_id '.$params['order'];

            $sql .= " LIMIT ".$params['start'].','.$params['per_page'];
            $total = $wpdb->get_var($sql_count);
            $rows = $wpdb->get_results( $sql,ARRAY_A );
            return array('total' => $total,'rows' => $rows);
        }

        public function getTransfer($transfer_id){
            global $wpdb;
            $sql = "SELECT * FROM ".$wpdb->prefix."openpos_stock_transfers WHERE transfer_id = ".intval($transfer_id);
            $row = $wpdb->get_row($sql,ARRAY_A);
            $row['staffs'] = $row['staffs'] ? json_decode($row['staffs']) : array();
            return $row;
        }
        public function getTransfers($params){
            global $wpdb;
            $sql_count = "SELECT COUNT(*)  FROM ".$wpdb->prefix."openpos_stock_transfers WHERE transfer_status > 0 ";


            $sql = "SELECT * FROM ".$wpdb->prefix."openpos_stock_transfers WHERE transfer_status > 0 ";
            if($params['term'])
            {
                $sql .= ' AND title LIKE "%'.$params['term'].'%"';
            }
            $sql .= ' ORDER BY transfer_id '.$params['order'];
            $sql .= " LIMIT ".$params['start'].','.$params['per_page'];
            $total = $wpdb->get_var($sql_count);
            $rows = $wpdb->get_results( $sql,ARRAY_A );
            return array('total' => $total,'rows' => $rows);
        }

        public function getTotalQtyByTransfer($transfer_id){
            global $wpdb;
            $sql = "SELECT SUM(product_qty) FROM ".$wpdb->prefix."openpos_stock_transfer_products WHERE transfer_id = ".(int)$transfer_id;
            $total = $wpdb->get_var($sql);
            return 1 * $total;
        }
        public function getStatus(){
            return array(
                0 => 'Temp',
                1 => 'Draft',
                2 => 'Pending Receive',
                3 => 'Received',
                4 => 'Declined'
            );
        }

        public function allowEdit($transfer_id){
            $transfer = $this->getTransfer($transfer_id);
            $current_user_id = get_current_user_id();
            if($transfer['transfer_status'] < 3 && $transfer['created_by'] == $current_user_id)
            {
                return  true;
            }
            return false;
        }

        public function allowReceive($transfer_id){
            $transfer = $this->getTransfer($transfer_id);
            $current_user_id = get_current_user_id();
            $staffs = $transfer['staffs'];
            if($transfer['transfer_status'] == 2 && in_array($current_user_id,$staffs))
            {
                return  true;
            }
            return false;
        }
    }

}
?>