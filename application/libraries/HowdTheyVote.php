<?php
/**
 * HowdTheyVote.php
 *
 * @author     Aaron McGowan (www.amcgowan.ca)
 */

/**
 * @ignore
 */
// defined('BASEPATH') OR exit;

/**
 * HowdTheyVote
 */
class HowdTheyVote {
    /* Member variables */
    const API_URL		= 'http://howdtheyvote.ca/api.php';
    
    protected $_current_api_key = null;
    
    /**
     * __construct
     *
     * Ctor.
     * 
     * @access: 
     * @param: 
     * @return: 
     */
    public function __construct() {
		$ci = get_instance();
		if( $ci !== null ) {
			$this->_current_api_key = $ci->config->item('howdtheyvote_api_key');
		}
    }
    
    public function set_api_key($key) {
		$this->_current_api_key = $key;
    }
    
    public function get_api_key() {
		return $this->_current_api_key;
    }
    
    public function find_member_by_latlng($lat, $lng, $house_id = 1) {
		$url = $this->build_api_url(array(
			'call' => 'findmember',
			'house_id' => $house_id,
			'latitude' => $lat,
			'longitude' => $lng
		));
		
		$data = simplexml_load_string(@file_get_contents($url));
		
		if( isset($data->member) ) {
			return $data->member;
		}
		
		return false;
    }
    
    public function find_riding_by_latlng($lat, $lng, $house_id = 1) {
		$url = $this->build_api_url(array(
			'call' => 'findriding',
			'house_id' => $house_id,
			'latitude' => $lat,
			'longitude' => $lng
		));
		
		$data = simplexml_load_string(@file_get_contents($url));
		
		if( isset($data->riding) ) {
			return $data->riding;
		}
		
		return false;
    }
    
    final protected function build_api_url(array $args) {
		$args['key'] = $this->_current_api_key;
		return self::API_URL . '?' . http_build_query($args);
    }
}