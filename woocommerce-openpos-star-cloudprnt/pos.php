<?php
$base_dir = dirname(dirname(dirname(__DIR__)));
require_once ($base_dir.'/wp-load.php');
$result = array('message' => '','status' => 0);
$receipt_html = isset($_REQUEST['html_str']) ? $_REQUEST['html_str'] : '';
$cashdrawer_id = isset($_REQUEST['cashdrawer_id']) ? $_REQUEST['cashdrawer_id'] : 0;
global $_op_printer;
$setting = $_op_printer->get_setting();
$mac = $setting['default_printer'];
if(isset($setting['active']) && $setting['active'] && $mac)
{
    if($cashdrawer_id)
    {
        $register_mac = get_post_meta($cashdrawer_id,'_op_register_star_cloudprnt',true);
        if($register_mac)
        {
            $mac  = $register_mac;
        }
    }
    if($_FILES){
        foreach($_FILES as $file_key => $file)
        {
            if($file_key == 'file_html')
            {
                // if you want use print html receipt
            }else{

                $type = $file['type'];
                if($type == 'image/png')
                {
                    $target_file = rtrim(OPENPOS_CLOUDPRNT_DIR,'/').'/files/'.time().'.png';//.$file["name"];
                    $image_base64 = file_get_contents($file["tmp_name"]);

                    if (file_put_contents($target_file,$image_base64)) {
                        star_cloudprnt_queue_add_print_job($mac,$target_file,1);

                        $result['message'] =  "The file ". basename( $file["name"]). " has been uploaded.";
                        $result['status'] = 1;
                    } else {
                        $result['message'] = "Sorry, there was an error uploading your file.";
                    }
                }

            }


        }
    }
}

echo json_encode($result);exit;