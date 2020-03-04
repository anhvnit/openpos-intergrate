<?php
class OP_Product_View_App extends OP_App_Abstract implements OP_App {
    public $key = 'openpos_app_product_view'; // unique
    public $name = 'View Product';
    public $thumb = OPENPOS_APP_PRODUCT_VIEW_URL.'/assets/images/app.png';
    public function render()
    {
        $session = $this->get_session();
        require_once OPENPOS_APP_PRODUCT_VIEW_DIR.'/view/view.php';
    }

}