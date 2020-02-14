<?php
class OP_Pickup{
    public  $days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    public function __construct()
    {
        add_action( 'wp_ajax_op_save_pickup', array($this,'save_pickup') );

        add_action('op_add_order_after', array($this,'save_order'),10,2);

        add_filter('op_api_result',array($this,'get_slots'),10,2);
        
        add_filter('op_get_login_cashdrawer_data',array($this,'custom_pos_discount_permission'),10,1);
    }
    public function init(){
        add_action( 'admin_menu', array($this,'pos_admin_menu'),10 );
    }
    function custom_pos_discount_permission($session_response_data){
    
        $session_response_data['setting']['pos_allow_pickup'] = 'yes';
        $session_response_data['setting']['pos_allow_timeslot'] = 'yes';
        return $session_response_data;
    
    }
    
    public function pos_admin_menu(){

        $page = add_submenu_page( 'openpos-dasboard', __( 'Store Open Time', 'openpos-pickup' ),  __( 'Open Time', 'openpos-pickup' ) , 'manage_woocommerce', 'op-pickup', array( $this, 'pickup_page' ) );

        add_action( 'admin_print_styles-'. $page, array( $this, 'admin_enqueue' ) );
    }
    public function admin_enqueue(){
        wp_enqueue_style('oopenpos-pickup.timepicker', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css');
        wp_enqueue_script('openpos-pickup.timepicker', '//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js',array('jquery'));
    }
    public function pickup_page(){
        require(OPENPOS_PICKUP_DIR.'templates/pickup.php');
    }
    public function save_pickup(){
        $params = isset($_REQUEST['slot']) ? $_REQUEST['slot'] : array();
        $slots = array();
        foreach($params as $day_id => $slot)
        {
            $slot_from = $slot['slot_from'];
            $slot_to = $slot['slot_to'];
            $slot_max = $slot['slot_max'];
            foreach($slot_from as $s_id => $s)
            {
                if(!$slot_to[$s_id] || !$s)
                {
                    continue;
                }
                $tmp = array(
                    'slot_from' => $s,
                    'slot_to' => $slot_to[$s_id],
                    'slot_max' => isset($slot_max[$s_id]) ? $slot_max[$s_id] : 0,
                );
                $slots[$day_id][] = $tmp;
            }
        }
        update_option('_op_pickup_times',$slots);
        $result = array();
        $result['status'] = 1;
        echo json_encode($result);exit;
    }
    public function get_pickup_times(){
        $slots = get_option('_op_pickup_times',array());
        return $slots;
    }
    public function getSlotByDateNum($date_num){ // date_num = 0-6
        $slots = $this->get_pickup_times();
        return isset($slots[$date_num]) ? $slots[$date_num] : array();
    }
    public function countPickedSlot($slot){
        $args = array(
            'post_type'        => 'shop_order',
            'posts_per_page'   => -1,
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key' => '_op_pickup_time',
                    'value' => $slot,
                    'compare' => '=',
                )
            )
         );
         $query = new WP_Query($args);
         
         return $query->found_posts;
    }
    public function get_slots($result,$action){
        if($action == 'get-time-slots')
        {
            
            $time_stamp_request = trim($_REQUEST['time_stamp']);
            $time_stamp_obj = json_decode(stripslashes($time_stamp_request),true);
            $time_stamp = $time_stamp_obj['time'];
            $date = isset($time_stamp_obj['date']) ? $time_stamp_obj['date'] : '';
            
            if($time_stamp)
            {
                $unix_time_stamp = round($time_stamp / 1000) + 12 * 60 * 60;
                $date = date('Y-m-d',$unix_time_stamp);
                $day = date('w',$unix_time_stamp);
            }else{
                $tmp = explode('T',$date);
                if(count($tmp) == 2)
                {
                    $date = $tmp[0];
                }
                $date .= ' 00:00:00'; 
               
                $day = date('w',strtotime($date));
                
            }
            

            
            $slots = $this->getSlotByDateNum($day);
            $data = array();
            foreach($slots as $s)
            {
                $from = trim($s['slot_from']);
                $to = trim($s['slot_to']);
                $slot_max = trim($s['slot_max']);
                $label = $from.'-'.$to;
                $value = $date.'@'.$label;
                $allow = 'yes';
                
                if($slot_max > 0)
                {
                     $count =  $this->countPickedSlot($value);
                     if($slot_max - $count <= 0)
                     {
                        $allow = 'no';
                     }
                }
               
                $tmp = array(
                    'label' => $label,
                    'value' => $value,
                    'allow' => $allow
                );
                $data[] = $tmp;
            }
            $result['status'] = 1;
            $result['data'] = $data;
        }
        
        return $result;
    }
    public function save_order($order,$order_data)
    {
        if(isset($order_data['order']))
        {
            $pickup_time = isset($order_data['pickup_time']) ? $order_data['pickup_time'] : '';
            $order_id = $order->get_id();
            
            if($pickup_time)
            {
                update_post_meta($order_id,'_op_pickup_time',$pickup_time);
                $tmp = explode('@',$pickup_time);
                if(count($tmp) == 2)
                {
                    $date = $tmp[0];
                    $slots = $tmp[1];
                    $tmp_slot = explode('-',$slots);
                    if(isset($tmp_slot[0]))
                    {
                        $date_str_from = $date.' '.$tmp_slot[0].':00';
                        update_post_meta($order_id,'_op_pickup_time_from',strtotime($date_str_from));
                    }
                    if(isset($tmp_slot[0]))
                    {
                        $date_str_to = $date.' '.$tmp_slot[1].':00';
                        update_post_meta($order_id,'_op_pickup_time_to',strtotime($date_str_to));
                    }
                    
                }
                

            }

        }
        
    }

}