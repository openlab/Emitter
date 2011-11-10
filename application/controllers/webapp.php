<?php
/**
 * WebApp.php
 *
 * @author     Aaron McGowan (www.amcgowan.ca)
 */

/**
 * @ignore
 */
defined('BASEPATH') OR exit;

/**
 * WebApp
 */
class WebApp extends AppController {
    /**
     * __construct
     * 
     * @access: public
     * @param: void
     * @return: void
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * index
     * 
     * @access: public
     * @param: void
     * @return: void
     */
    public function index() {
        $this->_view_data['load_webapp_js'] = true;
        
        $views = array('webapp/header', 'webapp/index', 'webapp/footer');
        $this->render_views($views);
    }
    
    public function about() {
        $this->render_views(array('webapp/header', 'webapp/content/about', 'webapp/footer'));
    }
    
    public function methodology() {
        $this->render_views(array('webapp/header', 'webapp/content/methodology', 'webapp/footer'));
    }
}