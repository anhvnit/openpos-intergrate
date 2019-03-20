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
    public $thumb = OPENPOS_APP_URL.'/app.png';
    public function render()
    {
        $session = $this->get_session();
        require_once OPENPOS_APP_DIR.'/view/view.php';
    }

}