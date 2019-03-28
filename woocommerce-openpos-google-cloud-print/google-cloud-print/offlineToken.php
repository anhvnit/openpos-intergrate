<?php
    $base_dir = dirname(dirname(dirname(dirname(__DIR__))));
    require_once ($base_dir.'/wp-load.php');
    if (isset($_GET['offlinetoken']))
    {
        update_option('_op_google_print_offlinetoken',$_GET['offlinetoken']) ;
    }
    ob_start();
    header("Location: ".admin_url('admin.php?page=op-google-print'));
?>