<?php
/**
 * Created by PhpStorm.
 * User: anhvnit
 * Date: 3/20/19
 * Time: 11:51
 */
class MyOpApp extends OP_App_Abstract implements OP_App {
    public $key = 'demo_app_key'; // unique
    public $name = 'Demo Open Pos App';
    public $thumb = OPENPOS_APP_URL.'app.png';
    public function render()
    {
        
        header('X-Frame-Options: allow-from *');
        global $in_pos_app;
        $in_pos_app = true;
        $session = $this->get_session();
        $email = $session['email'];
        $session_id = $session['session'];
        $user = wp_signon( array('user_login' => $email,'user_password' => $session_id) );
        require_once OPENPOS_APP_DIR.'/view/view.php';
    }

}