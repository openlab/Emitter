<?php
/**
 * AppController.php
 *
 * @author     Aaron McGowan (www.amcgowan.ca)
 */

defined('BASEPATH') OR exit;

/**
 * AppController
 */
abstract class AppController extends Controller {
    /* Member variables */
    protected $_view_data = array();
    
    /**
     * __construct
     *
     * Ctor.
     * 
     * @access: public
     * @param: void
     * @return: void
     */
    public function __construct() {
        parent::Controller();
        
        $this->_view_data = array(
            'base_url' => base_url(),
            'load_webapp_js' => false
        );
    }
    
    /**
     * before_render
     * 
     * @access: protected
     * @param: void
     * @return: void
     */
    protected function before_render() {
        /* void */
    }
    
    /**
     * render
     * 
     * @access: protected
     * @param: string           Contains the view in which to render.
     * @return: void
     */
    protected function render_view($view) {
        $this->before_render();
        $this->load->view($view, $this->_view_data);
    }
    
    /**
     * render_views
     * 
     * @access: protected
     * @param: array
     * @return: void
     */
    protected function render_views(array $views) {
        $this->before_render();
        
        foreach( $views as $view ) {
            $this->load->view($view, $this->_view_data);
        }
    }
}