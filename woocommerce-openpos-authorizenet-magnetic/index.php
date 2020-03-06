<?php
/*
Plugin Name: Woocommerce + openpos + Authorize.net + Magnetic Card
Plugin URI: http://openswatch.com
Description: Scan creditcard via reader , process via authorize.net
Author: anhvnit@gmail.com
Author URI: http://openswatch.com/
Version: 1.3
WC requires at least: 2.6
Text Domain: openpos-authorize-net-magnetic
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


define('OPENPOS_AUTHORIZE_NET_MAGNETIC_DIR',plugin_dir_path(__FILE__));
define('OPENPOS_AUTHORIZE_NET_MAGNETIC_URL',plugins_url('woocommerce-openpos-authorizenet-magnetic'));

require(OPENPOS_AUTHORIZE_NET_MAGNETIC_DIR.'vendor/autoload.php');
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

if(!function_exists('authorize_net_magnetic_op_addition_payment_methods'))
{
    function authorize_net_magnetic_op_addition_payment_methods($payment_options){


        $payment_options['op_authorize_net_magnetic'] = array(
            'code' => 'op_authorize_net_magnetic',
            'admin_title' => __('Authorize.net Magnetic Card','openpos'),
            'frontend_title' => __('Authorize.net Scan','openpos'),
            'description' => 'Use Magnetic Card Reader to scan customer cc process via authorize.net'
        );

        return $payment_options;
    }
}
add_filter('op_addition_payment_methods','authorize_net_magnetic_op_addition_payment_methods',10,1);


if(!function_exists('authorize_net_magnetic_op_addition_general_setting'))
{
    function authorize_net_magnetic_op_addition_general_setting($addition_general_setting){
        global $OPENPOS_SETTING;
        $allow_payment_methods = $OPENPOS_SETTING->get_option('payment_methods','openpos_payment');
        if(isset($allow_payment_methods['op_authorize_net_magnetic']))
        {
           
            $addition_general_setting[] =     array(
                'name'    => 'magnetic_authorize_login_id',
                'label'   => __( 'Authorize.Net API Login ID', 'openpos' ),
                'desc'    => 'use for Authorize.net Magnetic Card reader',
                'type'    => 'text',
                'default' => ''
            );

            $addition_general_setting[] =     array(
                'name'    => 'magnetic_authorize_transaction_key',
                'label'   => __( 'Authorize.Net Transaction Key', 'openpos' ),
                'desc'    => 'use for Authorize.net Magnetic Card reader',
                'type'    => 'password',
                'default' => ''
            );

            $addition_general_setting[] =     array(
                'name'    => 'magnetic_authorize_mode',
                'label'   => __( 'Authorize.Net Mode', 'openpos' ),
                'desc'    => 'use for Authorize.net Magnetic Card reader',
                'type'    => 'select',
                'default' => 'sandbox',
                'options' =>  array(
                    'production' => __( 'Production', 'openpos' ),
                    'sandbox'  => __( 'Sandbox', 'openpos' ),
                )
            );
        }
        return $addition_general_setting;
    }
}

add_filter('op_addition_general_setting','authorize_net_magnetic_op_addition_general_setting',10,1);

if(!function_exists('authorize_net_magnetic_op_login_format_payment_data'))
{
    function authorize_net_magnetic_op_login_format_payment_data($payment_method_data,$methods){
        if($payment_method_data['code'] == 'op_authorize_net_magnetic')
        {
            $payment_method_data['type'] = 'terminal';
            $payment_method_data['online_type'] = 'terminal';
        }
        return $payment_method_data;
    }
}

add_filter('op_login_format_payment_data','authorize_net_magnetic_op_login_format_payment_data',20,2);





add_filter('openpos_pos_header_style','authorize_net_magnetic_pos_header',20,1);
function authorize_net_magnetic_pos_header($handles){
    
    $handles[] = 'openpos.magnetic.authorize.styles';
   
    return $handles;
}
add_filter('openpos_pos_footer_js','authorize_net_magnetic_pos_footer',20,1);
function authorize_net_magnetic_pos_footer($handles){
    $handles[] = 'openpos.magnetic.authorize.base.js';
    $handles[] = 'openpos.magnetic.authorize.js';
    return $handles;
}

function authorize_net_magnetic_registerScripts(){
    global $OPENPOS_SETTING;
    if($OPENPOS_SETTING)
    {
       
            wp_enqueue_style( 'openpos.magnetic.authorize.styles', OPENPOS_AUTHORIZE_NET_MAGNETIC_URL.'/assets/css/authorize.css');
            wp_enqueue_script('openpos.magnetic.authorize.base.js',   OPENPOS_AUTHORIZE_NET_MAGNETIC_URL.'/assets/js/jquery.cardswipe.min.js',array('jquery'),null);
            wp_add_inline_script('openpos.magnetic.authorize.base.js',"
                        
                        
                       
                       
                          
            ");
            wp_enqueue_script('openpos.magnetic.authorize.js',  OPENPOS_AUTHORIZE_NET_MAGNETIC_URL.'/assets/js/authorize.js',array('jquery','openpos.magnetic.authorize.base.js'));
        
    }
    
   
}

add_action( 'init', 'authorize_net_magnetic_registerScripts' ,10 );




function authorize_net_magnetic_chargeCreditCard($order_number,$card_info,$amount,$customer = array())
{
    global $OPENPOS_SETTING;
    $authorize_login_id = $OPENPOS_SETTING->get_option('magnetic_authorize_login_id','openpos_payment');
    $authorize_transaction_key = $OPENPOS_SETTING->get_option('magnetic_authorize_transaction_key','openpos_payment');
    $authorize_mode = $OPENPOS_SETTING->get_option('magnetic_authorize_mode','openpos_payment');
    /* Create a merchantAuthenticationType object with authentication details
       retrieved from the constants file */
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName($authorize_login_id);
    $merchantAuthentication->setTransactionKey($authorize_transaction_key);

    // Set the transaction's refId
    $refId = 'ref' . time();
    // Create the payment data for a credit card
    $creditCard = new AnetAPI\CreditCardType();
    $creditCard->setCardNumber($card_info['cc_number']);
    $cc_exp_month = $card_info['cc_exp_month'];
    $cc_exp_year = $card_info['cc_exp_year'];
    if(strlen($cc_exp_year) == 2){
        $cc_exp_year = '20'.$cc_exp_year;
    }
    $creditCard->setExpirationDate(implode('-',array($cc_exp_year,$cc_exp_month)));
    $creditCard->setCardCode($card_info['cc_cvv']);
    // Add the payment data to a paymentType object
    $paymentOne = new AnetAPI\PaymentType();
    $paymentOne->setCreditCard($creditCard);
    // Create order information
    $order = new AnetAPI\OrderType();
    $order->setInvoiceNumber($order_number);
    $order->setDescription("Paid Via OpenPOS");

    // Set the customer's Bill To address
    $customerAddress = new AnetAPI\CustomerAddressType();
    $customerAddress->setFirstName($customer['firstname']);
    $customerAddress->setLastName($customer['lastname']);

    if(!empty($customer))
    {

        //$customerAddress->setCompany("Souveniropolis");
        if(isset($customer['address']))
        {
            $customerAddress->setAddress($customer['address']);
        }

        if(isset($customer['city']))
        {
            $customerAddress->setCity($customer['city']);
        }
        if(isset($customer['state']))
        {
            $customerAddress->setState($customer['state']);
        }
        if(isset($customer['zip']))
        {
            $customerAddress->setZip($customer['zip']);
        }
        if(isset($customer['country']))
        {
            $customerAddress->setCountry($customer['country']);
        }

        // Add some merchant defined fields. These fields won't be stored with the transaction,
    }

    // but will be echoed back in the response.
    $merchantDefinedField1 = new AnetAPI\UserFieldType();
    $merchantDefinedField1->setName("Source");
    $merchantDefinedField1->setValue("OpenPOS");
    // Create a TransactionRequestType object and add the previous objects to it
    $transactionRequestType = new AnetAPI\TransactionRequestType();

    $transactionRequestType->setTransactionType("authCaptureTransaction");

    $transactionRequestType->setAmount($amount);
    $transactionRequestType->setOrder($order);
    $transactionRequestType->setPayment($paymentOne);
    $transactionRequestType->setBillTo($customerAddress);
    //$transactionRequestType->setCustomer($customerData);
    //$transactionRequestType->addToTransactionSettings($duplicateWindowSetting);
    $transactionRequestType->addToUserFields($merchantDefinedField1);
    // Assemble the complete transaction request
    $request = new AnetAPI\CreateTransactionRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setRefId($refId);
    $request->setTransactionRequest($transactionRequestType);
    // Create the controller and get the response
    $controller = new AnetController\CreateTransactionController($request);
    if($authorize_mode == 'sandbox')
    {
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    }else{
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
    }

    $result = array(
        'status' => 0,
        'error_code' => 0,
        'message' => 'No response returned',
        'auth_code' => '',
        'transaction_code' => '',
        'transaction_id' => '',
    );
    if ($response != null) {
        // Check to see if the API request was successfully received and acted upon
        if ($response->getMessages()->getResultCode() == "Ok") {
            // Since the API request was successful, look for a transaction response
            // and parse it to display the results of authorizing the card
            $tresponse = $response->getTransactionResponse();

            if ($tresponse != null && $tresponse->getMessages() != null) {
                $result['status'] =  1;
                $result['transaction_id'] =  $tresponse->getTransId();
                $result['transaction_code'] =  $tresponse->getResponseCode();
                $result['error_code'] =  $tresponse->getMessages()[0]->getCode() ;
                $result['auth_code'] = $tresponse->getAuthCode();
                $result['message'] =  $tresponse->getMessages()[0]->getDescription() ;
            } else {
                $result['status'] =  0;
                if ($tresponse->getErrors() != null) {
                    $result['error_code'] =  $tresponse->getErrors()[0]->getErrorCode() ;
                    $result['message'] = $tresponse->getErrors()[0]->getErrorText() ;
                }
            }
            // Or, print errors if the API request wasn't successful
        } else {
            $result['status'] =  0;
            $result['message'] =  "Transaction Failed";
            $tresponse = $response->getTransactionResponse();

            if ($tresponse != null && $tresponse->getErrors() != null) {
                $result['error_code'] = $tresponse->getErrors()[0]->getErrorCode();
                $result['message'] = $tresponse->getErrors()[0]->getErrorText();
            } else {
                $result['error_code'] = $response->getMessages()->getMessage()[0]->getCode() ;
                $result['message'] = $response->getMessages()->getMessage()[0]->getText();
            }
        }
    }
    return $result;
}


if(!function_exists('op_payment_cc_order_op_authorize_net_magnetic'))
{
    function op_payment_cc_order_op_authorize_net_magnetic($result){
        $result = array(
            'status' => 0,
            'message' => ''
        );
        try{
            // ini_set('display_errors', 1);
            // ini_set('display_startup_errors', 1);
            // error_reporting(E_ALL);
            $order_parse_data = json_decode(stripslashes($_REQUEST['order']),true);
            $payment_parse_data = json_decode(stripslashes($_REQUEST['data']),true);
    
            $card_info =  array(
                'cc_holder' => $payment_parse_data['cc_name'],
                'cc_number' => $payment_parse_data['cc_num'],
                'cc_exp_month' => $payment_parse_data['cc_exp_month'],
                'cc_exp_year' => $payment_parse_data['cc_exp_year'],
                'cc_cvv' => $payment_parse_data['cc_cvv'],
            );
            $payment_amount = isset($payment_parse_data['amount']) ? (float)$payment_parse_data['amount'] : 0;
            $holder_name = $card_info['cc_holder'];
            $customer = array();
            if($holder_name)
            {
                $name = trim($holder_name);
                $tmp = explode(' ',$name);
                $firstname = $tmp[0];
                $lastname = trim(substr($name,(strlen($firstname))));
                $customer['firstname'] = $firstname;
                $customer['lastname'] = $lastname;
            }else{
                throw new Exception('Please enter customer name');
            }
           
            $order_id = $order_parse_data['id'];
            $charge_result = authorize_net_magnetic_chargeCreditCard($order_parse_data['order_number'],$card_info,$payment_amount,$customer);
    
            $result['status'] = $charge_result['status'];
            $result['message'] = $charge_result['message'];
            $result['data']['ref'] = $charge_result['transaction_id'];
            $result['data']['amount'] = $payment_amount;
            $result['data']['order_id'] = $order_id;
        }catch(Exception $e){
            $result['status'] = 0;
            $result['message'] = $e->getMessage();
        }
       
        echo json_encode($result);
        exit;
    }
}
add_action( 'wp_ajax_nopriv_op_authorize_net_magnetic', 'op_payment_cc_order_op_authorize_net_magnetic');
add_action( 'wp_ajax_op_authorize_net_magnetic', 'op_payment_cc_order_op_authorize_net_magnetic' );