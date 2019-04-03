<?php
/**
 * Created by PhpStorm.
 * User: anhvnit
 * Date: 1/15/19
 * Time: 14:03
 */
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class OP_Transfer{
    public $db;
    public function __construct()
    {

        $this->db = new OP_Transfer_Db();
        add_action( 'wp_ajax_op_stock_transfer_ajax_list', array($this,'ajax_transfer_list') );
        add_action( 'wp_ajax_op_stock_transfer_product_ajax_list', array($this,'ajax_transfer_product_list') );


        add_action( 'wp_ajax_op_upload_product_csv', array($this,'upload_product_csv') );
        add_action( 'wp_ajax_op_save_transfer', array($this,'save_transfer') );

        add_action( 'wp_ajax_op_download_product_csv', array($this,'download_product_csv') );
        add_action( 'wp_ajax_op_force_download_product_csv', array($this,'force_download_product_csv') );

        add_action( 'wp_ajax_op_update_transfer_product', array($this,'update_transfer_product') );
        add_action( 'wp_ajax_op_delete_transfer_product', array($this,'delete_transfer_product') );
        add_action( 'wp_ajax_op_delete_transfer', array($this,'delete_transfer') );


        add_action( 'wp_ajax_op_update_transfer_send', array($this,'send_transfer') );
        add_action( 'wp_ajax_op_update_transfer_receive', array($this,'receive_transfer') );
        add_action( 'wp_ajax_op_print_transfer', array($this,'print_transfer') );
        add_action( 'wp_ajax_op_scan_barcode', array($this,'scan_barcode') );



        //end
    }
    public function init(){
        add_action( 'admin_menu', array($this,'pos_admin_menu'),10 );
    }

    public function pos_admin_menu(){

        $page = add_submenu_page( 'openpos-dasboard', __( 'Stock Transfers', 'woo-book-price' ),  __( 'Stock Transfers', 'woo-book-price' ) , 'manage_woocommerce', 'op-stock-transfer', array( $this, 'transfer_page' ) );

        add_action( 'admin_print_styles-'. $page, array( $this, 'admin_enqueue' ) );
    }

    public function transfer_page(){
        global $op_warehouse;
        global $_has_openpos;
        global $op_woo;
        $warehouses = array();
        if($op_warehouse)
        {
            $warehouses = $op_warehouse->warehouses();

        }else{
            $warehouses[] = array(
                'id' => 0,
                'name' => __('Default Woocommerce Store','woo-book-price')
            );
        }
        $action = isset($_REQUEST['action']) ? esc_attr($_REQUEST['action']) : '';
        $current_transfer_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $current_transfer = array(
            'transfer_id' => 0,
            'title' => '',
            'note' => '',
            'from_warehouse_id' => -1,
            'to_warehouse_id' => -1,
            'staffs' => [],
            'received_by' => 0
        );
        if($current_transfer_id)
        {
            $tmp = $this->db->getTransfer($current_transfer_id);
            if(!empty($tmp))
            {
                $current_transfer = $tmp;
            }
        }

        switch ($action)
        {
            case 'new':
                $cashiers = $op_woo->get_cashiers();
                require(OPENPOS_TRANSFER_DIR.'templates/new.php');
                break;
            case 'edit':
                $cashiers = $op_woo->get_cashiers();
                $allow_receive = $this->db->allowReceive($current_transfer['transfer_id']);
                require(OPENPOS_TRANSFER_DIR.'templates/edit.php');
                break;
            case 'receive':
                $received_by = '';
                if($current_transfer['received_by'])
                {
                    $received_by_user = get_user_by('ID',$current_transfer['received_by']);
                    if($received_by_user)
                    {
                        $received_by = $received_by_user->display_name;
                    }
                }
                $cashiers = $op_woo->get_cashiers();
                $allow_receive = $this->db->allowReceive($current_transfer['transfer_id']);
                require(OPENPOS_TRANSFER_DIR.'templates/receive.php');
                break;
            default:
                require(OPENPOS_TRANSFER_DIR.'templates/transfers.php');
        }

    }
    public function admin_enqueue(){

        wp_enqueue_style('op-book-bootstrap.jquery', OPENPOS_TRANSFER_URL.'/assets/css/jquery.dataTables.css');
        wp_enqueue_style('op-book-bootstrap', OPENPOS_TRANSFER_URL.'/assets/css/bootstrap.css');
        wp_enqueue_style('op-book-bootstrap.datetime', OPENPOS_TRANSFER_URL.'/assets/css/bootstrap-datetimepicker.min.css');

        wp_enqueue_style('openpos-book.admin.datatable.bootrap', OPENPOS_TRANSFER_URL.'/assets/css/dataTables.bootstrap.css',array('op-book-bootstrap','op-book-bootstrap.jquery'));
        wp_enqueue_style('openpos-book.admin', OPENPOS_TRANSFER_URL.'/assets/css/admin.css',array('op-book-bootstrap','op-book-bootstrap.datetime'));


        wp_enqueue_script('openpos-book.admin.moment', OPENPOS_TRANSFER_URL.'/assets/js/moment.min.js',array('jquery'));
        wp_enqueue_script('openpos-book.admin.bootstrap', OPENPOS_TRANSFER_URL.'/assets/js/bootstrap.js',array('jquery'));

        wp_enqueue_script('openpos-book.admin.datables', OPENPOS_TRANSFER_URL.'/assets/js/datatables.min.js',array('jquery', 'wp-mediaelement'));
        wp_enqueue_script('openpos-book.admin.datables.jquery', OPENPOS_TRANSFER_URL.'/assets/js/jquery.dataTables.js',array('openpos-book.admin.bootstrap'));
        wp_enqueue_script('openpos-book.admin.bootstrap.datepicker', OPENPOS_TRANSFER_URL.'/assets/js/bootstrap-datetimepicker.js',array('openpos-book.admin.datables.jquery','openpos-book.admin.moment'));
        wp_enqueue_script('openpos-book.admin', OPENPOS_TRANSFER_URL.'/assets/js/admin.js',array('openpos-book.admin.datables.jquery','openpos-book.admin.bootstrap.datepicker'));


    }

    public function ajax_transfer_product_list(){
        global $_has_openpos;
        $result = array(
            "draw" => 0,
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            'data' => array()
        );
        $transfer_id = isset($_REQUEST['transfer_id']) ? intval($_REQUEST['transfer_id']) : 0;
        if($transfer_id)
        {
            $transfer = $this->db->getTransfer($transfer_id);

            $per_page = intval($_REQUEST['length']);
            $start = intval($_REQUEST['start']);
            $term = isset($_REQUEST['search']['search']) ? sanitize_text_field($_REQUEST['search']['search']): '' ;
            $order = isset($_REQUEST['order'][0]['dir']) ? esc_attr($_REQUEST['order'][0]['dir']) : 'asc';
            $params = array(
                'transfer_id' => $transfer_id,
                'per_page' => $per_page,
                'start' => $start,
                'order' => $order
            );

            $rows = $this->db->getTransferProducts($params);
            $result['draw'] = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0;
            $source = isset($_REQUEST['source']) ? esc_attr($_REQUEST['source']) : '';
            $result['recordsTotal'] = $rows['total'];
            $result['recordsFiltered'] = $rows['total'];
            $warehouse_id = $transfer['from_warehouse_id'];
            foreach($rows['rows'] as $row)
            {
                $product = wc_get_product($row['product_id']);
                if($source == 'receive')
                {
                    $tmp = array(
                        (int)$row['product_id'],
                        $row['product_barcode'],
                        $product->get_sku(),
                        $product->get_name(),
                        (1 * $row['product_qty'])
                       );
                }else{
                    $tmp = array(
                        (int)$row['product_id'],
                        $row['product_barcode'],
                        $product->get_sku(),
                        $product->get_name(),
                        $this->_getCurrentProductQty($product->get_id(),$warehouse_id),
                        '<input type="text" value="'.(1 * $row['product_qty']).'" id="row-'.$row['transfer_product_id'].'" />',
                        '<p><button type="button" class="save-row" data-id="'.$row['transfer_product_id'].'"><span class="glyphicon glyphicon-floppy-disk"></span></button>&nbsp;<button class="del-row" type="button" data-id="'.$row['transfer_product_id'].'" ><span class="glyphicon glyphicon-trash"></span></button></p>',
                    );
                }



                $result['data'][] = $tmp;
            }
        }
        echo json_encode($result);
        exit;
    }
    public function _getCurrentProductQty($product_id,$warehouse_id = 0){
        global $op_warehouse;
        return $op_warehouse->get_qty($warehouse_id,$product_id);
    }
    public function ajax_transfer_list(){
        $result = array(
            "draw" => 0,
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            'data' => array()
        );

        $per_page = intval($_REQUEST['length']);
        $start = intval($_REQUEST['start']);
        $term = isset($_REQUEST['search']['value']) ? sanitize_text_field($_REQUEST['search']['value']): '' ;
        $order = isset($_REQUEST['order'][0]['dir']) ? esc_attr($_REQUEST['order'][0]['dir']) : 'asc';
        $params = array(
            'term' => $term,
            'per_page' => $per_page,
            'start' => $start,
            'order' => $order
        );
        $rows = $this->db->getTransfers($params);
        $result['draw'] = isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0;
        $result['recordsTotal'] = $rows['total'];
        $result['recordsFiltered'] = $rows['total'];

        global $op_warehouse;
        $warehouses = array();
        if($op_warehouse)
        {
            $warehouses = $op_warehouse->warehouses();

        }else{
            $warehouses[] = array(
                'id' => 0,
                'name' => __('Default Woocommerce Store','woo-book-price')
            );
        }
        $all_statuses = $this->db->getStatus();
        foreach($rows['rows'] as $row)
        {

            $created_by = $row['created_by'];
            $user = get_user_by('id',$created_by);

            $from_warehouse_name = __('Default Woocommerce Store','woo-book-price');
            $to_warehouse_name = __('Default Woocommerce Store','woo-book-price');
            foreach($warehouses as $warehouse)
            {
                if($warehouse['id'] == $row['from_warehouse_id'])
                {
                    $from_warehouse_name = $warehouse['name'];
                }
                if($warehouse['id'] == $row['to_warehouse_id'])
                {
                    $to_warehouse_name = $warehouse['name'];
                }
            }
            $action_html = '';

            if($this->db->allowReceive($row['transfer_id']))
            {
                $action_html .= '<button class="receive-row" type="button" data-id="'.$row['transfer_id'].'"><span class="glyphicon glyphicon-download-alt"></span></button>&nbsp;';
            }
            if($this->db->allowEdit($row['transfer_id']))
            {
                $action_html .= '<button class="edit-row" type="button" data-id="'.$row['transfer_id'].'" ><span class="glyphicon glyphicon-pencil"></span></button>&nbsp;<button class="delete-row" type="button" data-id="'.$row['transfer_id'].'" ><span class="glyphicon glyphicon-trash"></span></button>&nbsp;';
            }
            if($row['transfer_status'] == 3 || $row['transfer_status'] == 4)
            {
                $action_html .= '<button class="receive-row" type="button" data-id="'.$row['transfer_id'].'"><span class="glyphicon glyphicon-list-alt"></span></button>&nbsp;';
            }
            $tmp = array(
                (int)$row['transfer_id'],
                $row['title'],
                $from_warehouse_name,
                $to_warehouse_name,
                $this->db->getTotalQtyByTransfer($row['transfer_id']),
                $row['transfer_date'],
                $user->display_name,
                $all_statuses[ $row['transfer_status'] ],
                $action_html
            );
            $result['data'][] = $tmp;
        }

        echo json_encode($result);
        exit;
    }

    public function save_transfer(){
        $current_user_id = get_current_user_id();


        $result = array(
            'status' => 0,
            'data' => array(),
            'message' => 'Unknown message'
        );
        try{
            $transfer_id = isset($_REQUEST['transfer_id'])? intval($_REQUEST['transfer_id']) : 0;
            $title = isset($_REQUEST['title'])? sanitize_text_field($_REQUEST['title']) : '';
            $note = isset($_REQUEST['note'])? sanitize_text_field($_REQUEST['note']) : '';
            $from_outlet_id = isset($_REQUEST['from_warehouse_id'])? intval($_REQUEST['from_warehouse_id']) : -1;
            $to_outlet_id = isset($_REQUEST['to_warehouse_id'])? intval($_REQUEST['to_warehouse_id']) : -1;
            $staffs = isset($_REQUEST['staffs'])? $_REQUEST['staffs'] : array();
            if(!$title)
            {
                throw new Exception(__('Please enter title','openpos'));
            }

            if($from_outlet_id < 0)
            {
                throw new Exception(__('Please choose From Outlet','openpos'));
            }

            if($to_outlet_id < 0)
            {
                throw new Exception(__('Please choose To Outlet','openpos'));
            }
            if($to_outlet_id  == $from_outlet_id)
            {
                throw new Exception(__('Please choose different To Outlet. From Outlet and To Outlet can not same','openpos'));
            }

            if(empty($staffs) || !is_array($staffs))
            {
                throw new Exception(__('Please choose staff','openpos'));
            }

            $request_data = array(
                'transfer_id' => $transfer_id,
                'title' => $title,
                'from_warehouse_id' => $from_outlet_id,
                'to_warehouse_id' => $to_outlet_id,
                'note' => $note,
                'status' => 1,
                'staffs' => json_encode($staffs),
                'created_by' => $current_user_id
            );

            $transfer_id = $this->db->save_transfer($request_data);
            if($transfer_id)
            {
                $result['status'] = 1;
                $request_data['transfer_id'] = $transfer_id;
                $result['data'] = $request_data;
            }

        }catch (Exception $e)
        {
            $result['status'] = 0;
            $result['message'] = $e->getMessage();
        }
        echo json_encode($result);
        exit;
    }
    public function upload_product_csv(){
        global $OPENPOS_CORE;
        global $_has_openpos;

        $transfer_id = isset($_REQUEST['transfer_id'])? intval($_REQUEST['transfer_id']) : 0;

        $result = array(
            'status' => 0,
            'data' => array(),
            'message' => 'Please choose file'
        );

        try{
            $from_warehouse_id = isset($_REQUEST['from_warehouse_id'])? intval($_REQUEST['from_warehouse_id']) : -1;
            if($from_warehouse_id < 0)
            {
                throw new Exception(__('Please choose from Outlet','openpos'));
            }
            if(!$transfer_id)
            {
                $transfer_id = $this->db->save_draft_transfer($from_warehouse_id);
            }
            if($transfer_id)
            {
                //
                //load();
                if(isset($_FILES['file']))
                {
                    $file = $_FILES['file'];
                    $csv = array();
                    if($file['type'])
                    {
                        $inputFileType = 'Csv';
                        try{
                            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                            $reader->setReadDataOnly(true);
                            $reader->load($file['tmp_name']);

                            $worksheetData = $reader->listWorksheetInfo($file['tmp_name']);
                            foreach ($worksheetData as $worksheet) {
                                $sheetName = $worksheet['worksheetName'];

                                $reader->setLoadSheetsOnly($sheetName);
                                $spreadsheet = $reader->load($file['tmp_name']);

                                $worksheet = $spreadsheet->getActiveSheet();
                                $csv = $worksheet->toArray();
                            }

                        }catch (Exception $e)
                        {
                            print_r($e->getMessage());
                        }

                        if(!empty($csv))
                        {
                            $labels = $csv[0];
                            array_shift($csv);
                            $id_index = 0;
                            $qty_index = -1;
                            foreach($labels as $key => $label)
                            {
                                if(strtoupper($label) == strtoupper('id'))
                                {
                                    $id_index = $key;
                                }
                                if(strtoupper($label) == strtoupper('qty'))
                                {
                                    $qty_index = $key;
                                }
                            }

                            foreach($csv as $row)
                            {
                                $product_id = $row[$id_index];
                                if(!$product_id)
                                {
                                    continue;
                                }


                                $product = wc_get_product($product_id);
                                if($product)
                                {
                                    if($qty_index < 0)
                                    {
                                        continue;
                                    }
                                    $qty = 1 * $row[$qty_index];
                                    if($qty <= 0 )
                                    {
                                        continue;
                                    }
                                    $id = $product->get_id();

                                    $row_data = array(
                                        'transfer_id' => $transfer_id,
                                        'product_id' => $id,
                                        'product_qty_before_send' => 0,
                                        'product_barcode' => $OPENPOS_CORE->getBarcode($id),
                                        'product_sku' => $product->get_sku(),
                                        'product_qty' => 1 * $qty
                                    );


                                    $this->db->save_transfer_items($row_data);
                                }


                            }
                        }

                    }

                    $result['status'] = 1;
                }
                $result['data']['transfer_id'] = $transfer_id;
            }
        }catch (Exception $e)
        {
            $result['status'] = 0;
            $result['message'] = $e->getMessage();
        }





        echo json_encode($result);
        exit;
    }
    public function force_download_product_csv(){
        $file_name = isset($_REQUEST['file']) ? sanitize_text_field($_REQUEST['file']) : '';
        if($file_name)
        {
            ob_start();
            $upload_dir = wp_upload_dir();
            $url = $upload_dir['basedir'];

            $url = rtrim($url,'/').'/'.$file_name;
            header("Content-Type: application/octet-stream");
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"".$file_name."\"");
            echo readfile($url);
        }else{
            echo 'Wrong file name';
        }

        exit;
    }
    public function download_product_csv(){

        global $_has_openpos;

        $outlet_id = isset($_REQUEST['from_warehouse_id']) ? intval($_REQUEST['from_warehouse_id']) : 0;


        $params = array('numberposts' => -1);

        $products = $this->getProducts($params);

        $result = array(
            'status' => 0,
            'data' => array(),
            'message' => ''
        );

        $orders_export_data = array();
        $orders_export_data[] = array(
            "ID",
            "PRODUCT",
            "SKU",
            "QTY",
        );

        foreach($products['posts'] as $post)
        {
            $_product = wc_get_product($post->ID);

            $tmp = array(
                $_product->get_id(),
                $_product->get_name(),
                $_product->get_sku(),
                0,

            );
            $orders_export_data[] = $tmp;
        }




        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->fromArray($orders_export_data, null, 'A1');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);


        $file_name = 'openpos-transfer-products-'.time().'.csv';
        $writer->save(ABSPATH.'wp-content/uploads/'.$file_name);
        $url = admin_url('admin-ajax.php?action=op_force_download_book_csv&file='.$file_name);
        $result['data']['export_file'] = $url;
        $result['status'] = 1;
        echo json_encode($result);
        exit;
    }
    public function scan_barcode(){
        global $OPENPOS_CORE;
        $result = array(
            'status' => 0,
            'data' => array(),
            'message' => 'Unknown message'
        );
        try{
            $barcode = sanitize_text_field($_REQUEST['barcode']);
            $from_warehouse_id = intval($_REQUEST['from_warehouse_id']);
            $transfer_id = intval($_REQUEST['transfer_id']);


            $product_id = $OPENPOS_CORE->getProductIdByBarcode($barcode);
            if(!$product_id)
            {
                throw new Exception(__('No product with barcode "'.sanitize_text_field($barcode).'"','openpos'));
            }
            if(!$transfer_id)
            {
                $transfer_id = $this->db->save_draft_transfer($from_warehouse_id);

            }
            $qty = 1;
            $product = wc_get_product($product_id);
            $row = $this->db->getRowByProductId($transfer_id,$product_id);
            if($row)
            {
                $qty += 1 * $row['product_qty'];
            }
            $data = array(
                'transfer_id' => $transfer_id,
                'product_id' => $product_id,
                'product_qty' => $qty,
                'product_barcode' => $OPENPOS_CORE->getBarcode($product_id),
                'product_sku' => $product->get_sku(),

            );
            $this->db->save_transfer_items($data);

            $result['data']['transfer_id'] = $transfer_id;
            $result['status'] = 1;
        }catch (Exception $e)
        {
            $result['status'] = 0;
            $result['message'] = $e->getMessage();
        }
        echo json_encode($result);
        exit;
    }
    public function update_transfer_product(){
        $result = array(
            'status' => 0,
            'data' => array(),
            'message' => ''
        );
        $row_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $qty = isset($_REQUEST['qty']) ? sanitize_text_field($_REQUEST['qty']) : 0;

        if($row_id)
        {
            $this->db->update_transfer_item_qty($row_id,$qty);
            $result['status'] = 1;
        }
        echo json_encode($result);
        exit;
    }
    public function delete_transfer_product(){
        $result = array(
            'status' => 0,
            'data' => array(),
            'message' => ''
        );

        $row_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        if($row_id)
        {
            $this->db->delete_transfer_item($row_id);
            $result['status'] = 1;
        }

        echo json_encode($result);
        exit;
    }
    public function delete_transfer(){
        $result = array(
            'status' => 0,
            'data' => array(),
            'message' => ''
        );

        $row_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        if($row_id)
        {
            $this->db->delete_transfer($row_id);
            $result['status'] = 1;
        }

        echo json_encode($result);
        exit;
    }

    public function receive_transfer(){
        global $op_warehouse;
        $result = array(
            'status' => 0,
            'data' => array(),
            'message' => ''
        );
        try{
            $row_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $action = isset($_REQUEST['action_type']) ? esc_attr($_REQUEST['action_type']) : '';
            if(!$row_id)
            {
                throw new Exception(__('Please choose transfer','openpos'));
            }
            if(!$this->db->allowReceive($row_id))
            {
                throw new Exception(__('You have no permission to received / decline on this transfer','openpos'));
            }
            if($row_id)
            {
                $transfer = $this->db->getTransfer($row_id);
                if(!$transfer)
                {
                    throw new Exception(__('Transfer do not exist','openpos'));
                }
                $status = $transfer['status'];
                if($action == 'decline' && $status != 3)
                {

                    $this->db->decline_transfer($row_id);
                    $result['status'] = 1;
                }

                if($action == 'receive' && $status != 4)
                {
                    $receive_products = $this->db->getAllAllowTransferProducts($row_id);
                    foreach($receive_products as $receive_product)
                    {

                        $from_warehouse_id = $transfer['from_warehouse_id'];
                        $to_warehouse_id = $transfer['to_warehouse_id'];
                        $product_id = $receive_product['product_id'];
                        $product = wc_get_product($product_id);
                        if(!$product)
                        {
                            continue;
                        }
                        $from_current_qty = $op_warehouse->get_qty($from_warehouse_id,$product_id);
                        $to_current_qty = $op_warehouse->get_qty($to_warehouse_id,$product_id);

                        $this->db->update_transfer_item_before_qty($receive_product['transfer_product_id'],$from_current_qty);

                        $adjust_qty =  1 * $receive_product['product_qty'];
                        $from_current_qty -= $adjust_qty;
                        $to_current_qty += $adjust_qty;

                        if($from_warehouse_id > 0)
                        {
                            $op_warehouse->set_qty($from_warehouse_id,$product_id,$from_current_qty);
                        }
                        if($to_warehouse_id > 0)
                        {
                            $op_warehouse->set_qty($to_warehouse_id,$product_id,$to_current_qty);
                        }

                        if($from_warehouse_id == 0)
                        {
                            $product->set_stock_quantity($from_current_qty);
                            $product->set_manage_stock(true);
                            if($from_current_qty > 0)
                            {
                                $product->set_stock_status('instock');
                            }else{
                                $product->set_stock_status('outofstock');
                            }
                        }
                        if($to_warehouse_id == 0)
                        {
                            $product->set_stock_quantity($to_current_qty);
                            $product->set_manage_stock(true);
                            if($to_current_qty > 0)
                            {
                                $product->set_stock_status('instock');
                            }else{
                                $product->set_stock_status('outofstock');
                            }
                        }
                        $product->save();



                    }
                    $this->db->receive_transfer($row_id);
                    $result['status'] = 1;
                }

            }
        }catch (Exception $e)
        {
            $result['status'] = 0;
            $result['message'] = $e->getMessage();
        }


        echo json_encode($result);
        exit;
    }
    public function send_transfer(){
        $result = array(
            'status' => 0,
            'data' => array(),
            'message' => ''
        );

        $row_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        if($row_id)
        {
            $this->db->send_transfer($row_id);
            $result['status'] = 1;
        }

        echo json_encode($result);
        exit;
    }

    public function print_transfer(){
        global $op_warehouse;
        $id = $_REQUEST['id'];

        $current_transfer = $this->db->getTransfer($id);
        if($current_transfer)
        {
            $received_by = '';
            if($current_transfer['received_by'])
            {
                $received_by_user = get_user_by('ID',$current_transfer['received_by']);
                if($received_by_user)
                {
                    $received_by = $received_by_user->display_name;
                }
            }
            $from_outlet = $op_warehouse->get($current_transfer['from_warehouse_id']);
            $to_outlet = $op_warehouse->get($current_transfer['to_warehouse_id']);
            $items = $this->db->getAllAllowTransferProducts($current_transfer['transfer_id'],false);
            require(OPENPOS_TRANSFER_DIR.'templates/print.php');
            exit;
        }

    }

    public function getProducts($args)
    {

        $ignores = $this->getAllVariableProducts();

        $args['post_type'] = array('product','product_variation');
        $args['exclude'] = $ignores;
        $args['post_status'] = 'publish';
        $args['suppress_filters'] = false;

        $defaults = array(
            'numberposts' => 5,
            'category' => 0, 'orderby' => 'date',
            'order' => 'DESC', 'include' => array(),
            'exclude' => array(), 'meta_key' => '',
            'meta_value' =>'', 'post_type' => 'product',
            'suppress_filters' => true
        );

        $r = wp_parse_args( $args, $defaults );
        if ( empty( $r['post_status'] ) )
            $r['post_status'] = ( 'attachment' == $r['post_type'] ) ? 'inherit' : 'publish';
        if ( ! empty($r['numberposts']) && empty($r['posts_per_page']) )
            $r['posts_per_page'] = $r['numberposts'];
        if ( ! empty($r['category']) )
            $r['cat'] = $r['category'];
        if ( ! empty($r['include']) ) {
            $incposts = wp_parse_id_list( $r['include'] );
            $r['posts_per_page'] = count($incposts);  // only the number of posts included
            $r['post__in'] = $incposts;
        } elseif ( ! empty($r['exclude']) )
            $r['post__not_in'] = wp_parse_id_list( $r['exclude'] );

        $r['ignore_sticky_posts'] = false;
        $get_posts = new WP_Query($r);
        return array('total'=>$get_posts->found_posts,'posts' => $get_posts->get_posts());
    }
    public function getAllVariableProducts()
    {
        $args = array(
            'posts_per_page'   => -1,
            'post_type'        => array('product_variation'),
            'post_status'      => 'publish'
        );
        $posts_array = get_posts($args);
        $result = array();
        foreach($posts_array as $post)
        {
            $parent_id =  $post->post_parent;
            if($parent_id)
            {
                $result[] = $parent_id;
            }
        }
        $arr = array_unique($result);
        $result = array_values($arr);
        return $result;
    }



    public function product_price($price,$product){
        global $_in_openpos_book;
        global $op_session_data;

        if($_in_openpos_book)
        {
            return $price;
        }
        $product_id = $product->get_id();
        if($op_session_data && isset($op_session_data['login_warehouse_id']))
        {
            $book_price = $this->getBookPrice($product_id,$op_session_data['login_warehouse_id']);

        }else{
            $book_price = $this->getBookPrice($product_id,0);
        }


        if($book_price !== false)
        {
            return $book_price;
        }
        return $price;
    }



}