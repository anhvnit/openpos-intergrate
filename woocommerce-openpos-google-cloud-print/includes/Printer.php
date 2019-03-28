<?php
/**
 * Created by PhpStorm.
 * User: anhvnit
 * Date: 1/15/19
 * Time: 14:03
 */
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class OP_Google_Printer{

    public function __construct()
    {

        add_action( 'wp_ajax_op_google_print_setting', array($this,'save_setting') );

        add_action( 'op_register_form_end', array($this,'op_register_form_end') ,10,3);
        add_action( 'op_register_save_after', array($this,'op_register_save_after') ,10,3);


        //end
    }
    public function init(){
        add_action( 'admin_menu', array($this,'pos_admin_menu'),10 );
    }

    public function pos_admin_menu(){

        $page = add_submenu_page( 'openpos-dasboard', __( 'Google Cloud Print', 'woo-book-price' ),  __( 'Google Cloud Print', 'woo-book-price' ) , 'manage_woocommerce', 'op-google-print', array( $this, 'setting_page' ) );

        add_action( 'admin_print_styles-'. $page, array( $this, 'admin_enqueue' ) );
    }


    public function admin_enqueue(){

        wp_enqueue_style('op-printer-bootstrap', OPENPOS_GOOGLE_PRINT_URL.'/assets/css/bootstrap.css');

        wp_enqueue_style('openpos-printer.admin', OPENPOS_GOOGLE_PRINT_URL.'/assets/css/admin.css',array('op-printer-bootstrap'));

        wp_enqueue_script('openpos-printer.admin.bootstrap', OPENPOS_GOOGLE_PRINT_URL.'/assets/js/bootstrap.js',array('jquery'));

    }
    public function setting_page(){
        $setting = $this->get_setting();
        $printers = $this->getPrinters();
        require(OPENPOS_GOOGLE_PRINT_DIR.'templates/setting.php');
    }

    public function save_setting(){
        $setting = isset($_REQUEST['_op_google_cloud_print']) ? $_REQUEST['_op_google_cloud_print'] : array();
        update_option( '_op_google_cloud_print', json_encode($setting) );
        $result = array(
            'status' => '1',
            'message' => 'Your setting has been saved.'
        );
        echo json_encode($result);
        exit;
    }

    public function get_setting(){
        $default = array(
            'active' => 0,
            'default_printer' => ''
        );
        $setting_json = get_option('_op_google_cloud_print',json_encode($default));
        $setting = json_decode($setting_json,ARRAY_A);
        $setting['redirect_uri'] = esc_url(OPENPOS_GOOGLE_PRINT_URL.'/google-cloud-print/oAuthRedirect.php');
        $setting['accessToken'] = get_option('_op_google_print_accessToken','');
        return $setting;
    }

    public function pos_print(){
        $result = array('message' => '','status' => 0);
        $setting = $this->get_setting();

        if(isset($setting['active']) && $setting['active'])
        {
            if($_FILES){
                foreach($_FILES as $file_key => $file)
                {
                    if($file_key == 'file_html')
                    {
                        $html = file_get_contents($file["tmp_name"]);
                        file_put_contents(rtrim(OPENPOS_GOOGLE_PRINT_DIR,'/').'/files/'.time().'_receipt.html',$html);

                    }else{

                        $type = $file['type'];
                        if($type == 'image/png')
                        {
                            $target_file = rtrim(OPENPOS_GOOGLE_PRINT_DIR,'/').'/files/'.time().'_'.$file["name"];
                            $image_base64 = file_get_contents($file["tmp_name"]);

                            if (file_put_contents($target_file,$image_base64)) {
                                $result['message'] =  "The file ". basename( $file["name"]). " has been uploaded.";
                                $result['status'] = 1;
                            } else {
                                $result['message'] = "Sorry, there was an error uploading your file.";
                            }
                        }

                    }


                }
            }
        }else{
            $result['message'] = "Sorry, the printer has been turn off!";
        }

        echo json_encode($result);exit;
    }

    public function getPrinters(){
        global $_op_gcp;

        $list = array();

        $setting = $this->get_setting();
        if(isset($setting['accessToken']) && $setting['accessToken'] != '')
        {
            $_op_gcp->setAuthToken($setting['accessToken']);
            $printers = $_op_gcp->getPrinters();
            foreach($printers as $printer)
            {
                $key = $printer['id'];
                $name = implode('-',array($printer['name'],$printer['connectionStatus']));
                $printer['name'] = $name;
                $list[$key] = $printer;
            }
        }

        return $list;
    }
    public function op_register_save_after($id,$params,$op_register){
        if($id)
        {
            $printer = isset($params['_op_register_google_cloud_printer']) ? $params['_op_register_google_cloud_printer'] : '';
            update_post_meta($id,'_op_register_google_cloud_printer',$printer);
        }
    }
    public function op_register_form_end($default,$warehouses,$cashiers){
        $printers = $this->getPrinters();
        $current_printer = '';

        if($default['id'] > 0)
        {
            $current_printer = get_post_meta($default['id'],'_op_register_star_cloudprnt',true);
        }
        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __( 'Printer', 'openpos' ); ?></label>
            <div class="col-sm-10">
                <select class="form-control" name="_op_register_google_cloud_printer">
                    <option  value=""><?php echo __('Use Default Printer','openpos'); ?></option>
                    <?php foreach($printers as $key => $printer):  ?>
                        <option value="<?php echo $key; ?>"  <?php echo $current_printer == $key ? 'selected': ''; ?>><?php echo $printer['name']; ?><?php echo isset($printer['ClientType']) ? ' - '.$printer['ClientType'] : ''; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php
    }


}