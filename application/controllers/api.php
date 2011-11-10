<?php
/**
 * api.php
 *
 * @author  Aaron McGowan < me@amcgowan.ca >
 */

/**
 * @ignore
 */
defined('BASEPATH') OR exit;

/**
 * Api
 */
class Api extends AppController {
    /* Member variables */
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
        $this->render_views(array('webapp/header', 'webapp/content/developer', 'webapp/footer'));
    }
    
    /**
     * facility
     * 
     * @access: public
     * @param: int
     * @return: void
     */
    public function facility($npri_id = null) {
		$npri_id = (int) $npri_id;
		
		if( $npri_id ) {
            $this->db->select('_facility_loc_info.LATI_DEC as facility_latitude, _facility_loc_info.LONG_DEC as facility_longitude, _facility.*');
            $this->db->from('_facility_loc_info');
            $this->db->join('_facility', '_facility_loc_info.NPRI_ID = _facility.NPRI_ID');
            $this->db->where('_facility.NPRI_ID', $npri_id);
            $this->db->limit(1);
            
            $query = $this->db->get();
            
            if( $query->num_rows() > 0 ) {
				$f_row = $query->row();
            
				$facility = new stdClass;
				$facility->id = $npri_id;
				$facility->name = $f_row->FACI_NAME;
				$facility->company_name = $f_row->COMP_NAME;
				$facility->website_url = $f_row->URL_ADDRES;
				
				$facility->city = $f_row->CITY;
				$facility->province = $f_row->PROVINCE;
				
				$sectors = $this->_retrieve_sectors();
				
				$naics2 = false !== strpos('-', $f_row->NAICS2) ? substr($f_row->NAICS2, 0, strpos('-', $f_row->NAICS2)) : $f_row->NAICS2;
                $naics2 = (int) $naics2;
                
                $facility->sector_name = isset($sectors[$naics2]) ? $sectors[$naics2] : 'Unknown Sector';
                
                unset($sectors, $naics2);
				
				$facility->location = new stdClass;
				$facility->location->latitude = $f_row->facility_latitude;
				$facility->location->longitude = $f_row->facility_longitude;
				
				$facility->num_of_employees = $f_row->EMPLOYEES;
				
				$facility->whole_npri_id = str_pad($npri_id, 10, '0', STR_PAD_LEFT);
				
				$facility->npri_website  = 'http://www.ec.gc.ca/pdb/websol/querysite/facility_substance_summary_e.cfm' .
					'?opt_npri_id=' . $facility->whole_npri_id . '&opt_report_year=2008';
				
				$this->db->select('*');
				$this->db->from('facility_rankings');
				$this->db->where('NPRI_ID', $facility->whole_npri_id);
				$ranking_query = $this->db->get();
				
				if( $ranking_query->num_rows() > 0 ) {
					$r_row = $ranking_query->row();
				
					$facility->ranking = new stdClass;
					$facility->ranking->rank = $r_row->Rank;
					$facility->ranking->percent_rank = ($r_row->PercentRank * 100);
				} else {
					$facility->ranking = null;
				}
				
				$this->load->library('HowdTheyVote', null, 'htv');
				$riding_mp = $this->htv->find_member_by_latlng($f_row->facility_latitude, $f_row->facility_longitude);
				
				$facility->riding = new stdClass;
				$facility->riding->mp = $riding_mp;
				
				$facility->chemicals = array();
				
				$this->load->library('OGDI', null, 'ogdi');
				$chemicals = $this->ogdi->query('emittersubstancerelease', "npri_id eq '{$facility->whole_npri_id}'");
				$chemicals_air_rank = $this->_retrieve_chemsubstance_air_ranks($facility->whole_npri_id);
				if( 0 < count($chemicals) ) { 
					foreach( $chemicals as &$chemical ) {
						if( !isset($chemicals_air_rank[$chemical->cas_number]) ) {
							continue;
						}
						
						$o = new stdClass;
						$o->chem_name = $chemical->chem_e;
						
						$o->cas_number = $chemical->cas_number;
						$o->adj_air = $chemicals_air_rank[$chemical->cas_number]->adj_air;
						$o->rank = $chemicals_air_rank[$chemical->cas_number]->rank;
						$o->castotal = $chemicals_air_rank[$chemical->cas_number]->castotal;
						$o->percentrank = $chemicals_air_rank[$chemical->cas_number]->percentrank;
						
						$facility->chemicals[] = $o;
					}
					unset($chemical, $chemicals);
				}
				
				$facility->contacts = array();
				
				$contacts = $this->ogdi->query('emitterfacilitycontact', "npri_id eq '{$npri_id}'");
				
				if( 0 < count($contacts) ) {
					foreach( $contacts as &$contact ) {
						$o = new stdClass;
						$o->name = (!empty($contact->title) ? ($contact->title . ' ') : '') . $contact->name_first . ' ' . $contact->name_last;
						$o->phone = '(' . $contact->voice_area . ') ' . $contact->voice_numb;
						$o->email = $contact->email;
						$o->position = $contact->c_position;
						
						$facility->contacts[] = $o;
					}
					unset($contact, $contacts);
				}
				
				$this->_output_json(array('status' => 'OK', 'result' => $facility));
				return;
            }
        }
            
		$this->_output_json(array('status' => 'BAD', 'message' => 'Invalid Facility NPRI ID'));
    }
    
    /**
     * search
     * 
     * @access: public
     * @param: void
     * @return: void
     */
    public function search() {
        if( !empty($_POST) ) {
            $terms = trim($this->input->post('search_terms'));
            
            $proximity = trim($this->input->post('search_proximity'));
            $proximity = (is_numeric($proximity) && 0 < $proximity) ? ((int) $proximity) : 'all';
            
            $search_by = trim($this->input->post('search_by'));
            
            /* Load the BingMaps class library wrapper for handling SOAP interactions */
            $this->load->library('BingMaps', null, 'bing_maps');
            
            /* Should results be cached? */
            $terms_latlng = $this->bing_maps->geocode_lookup_latlng($terms);
            
            if( is_array($terms_latlng) ) {
                if(  1 === count($terms_latlng) ) {
                    $terms_latlng = $terms_latlng[0];
                }
                else if( 1 < count($terms_latlng) ) {
					$same_coords = false;
                    foreach( $terms_latlng as $i => &$term )
                    {
                        for( $j = 0, $size = count($terms_latlng); $j != $size; ++$j )
                        {
                            if( $j == $i )
                            {
                                continue;
                            }
                            
                            if( round($term->latitude, 2) == round($terms_latlng[$j]->latitude, 2) && round($term->longitude, 2) == round($terms_latlng[$j]->longitude, 2) )
                            {
                                $same_coords = true;
                            } else {
								$same_coords = false;
							}
                        }
                    }
                    unset($term);
                    
                    if( !$same_coords )
                    {
                        $ambiguous_results =& $terms_latlng;
						$this->_output_json(array('status' => 'PARTIAL', 'search_ambiguous' => $ambiguous_results));
						return;
                    }
                    
                    $terms_latlng = $terms_latlng[0];
                }
                else {
                    $this->_output_json(array('status' => 'BAD', 'message' => 'No results match search criteria.'));
                    return;
                }
            }
            else {
                $this->_output_json(array('status' => 'BAD', 'message' => 'No results match search criteria.'));
                return;
            }
            
            /* Load the HowdTheyVote library, lookup member ... */
            $this->load->library('HowdTheyVote', null, 'htv');
			$htv_mp = $this->htv->find_member_by_latlng($terms_latlng->latitude, $terms_latlng->longitude);
			
			/* Start building the search info object */
			$search_info = new stdClass;
			$search_info->type = $search_by;
            $search_info->location = new stdClass;
            $search_info->location->latitude = $terms_latlng->latitude;
            $search_info->location->longitude = $terms_latlng->longitude;
            
            /* Retrieve sector information */
            $sectors = $this->_retrieve_sectors();
			
			/* Initialize the query for facilities */
            $this->db->select('_facility_loc_info.LATI_DEC as facility_latitude, _facility_loc_info.LONG_DEC as facility_longitude, _facility.*, _facility_loc_info.ADDRESS1 AS ADDRESS_LINE1, _facility_loc_info.ADDRESS2 as ADDRESS_LINE2, _facility_loc_info.CITY as ADDRESS_CITY, _facility_loc_info.PROVINCE as ADDRESS_PROV, _facility_loc_info.POSTALCODE as ADDRESS_POSTAL');
            $this->db->from('_facility_loc_info');
            $this->db->join('_facility', '_facility_loc_info.NPRI_ID = _facility.NPRI_ID');
            
            if( $search_by == 'city' ) {
				$locality_terms = $terms_latlng->locality;
				$this->db->where('_facility.CITY', $this->db->escape_str($locality_terms));
            }
            else if( 'riding' == $search_by ) {
				$edid = (int) $htv_mp->edid;
            
				$this->db->join('facility_fedridings', 'facility_fedridings.facility_npri_id = _facility.NPRI_ID');
				$this->db->where('facility_fedridings.edid', $edid);
				
				$search_info->riding = new stdClass;
				$search_info->riding->mp = $htv_mp;
            }
            
            $facility_query = $this->db->get();
            
            if( 0 < $facility_query->num_rows() ) {
                $results = array();
                foreach( $facility_query->result() as $facility ) {
					$distance = calculate_distance_btwn_latlng($terms_latlng->latitude, $terms_latlng->longitude, $facility->facility_latitude, $facility->facility_longitude);
					
					if( 'nearby' == $search_by ) {
						if( 'all' != $proximity && $proximity <= $distance ) {
							continue;
						}
					}
                
                    $o = new stdClass;
                    $o->id = $facility->NPRI_ID;
                    $o->name = $facility->FACI_NAME;
                    $o->company_name = $facility->COMP_NAME;
                    
                    $o->whole_npri_id = str_pad($facility->NPRI_ID, 10, '0', STR_PAD_LEFT);
                    
                    $naics2 = false !== strpos('-', $facility->NAICS2) ? substr($facility->NAICS2, 0, strpos('-', $facility->NAICS2)) : $facility->NAICS2;
                    $naics2 = (int) $naics2;
                    
                    $o->sector_name = isset($sectors[$naics2]) ? $sectors[$naics2] : 'Unknown Sector';
                    
                    $o->location = new stdClass;
                    $o->location->distance = round($distance, 2) . ' km';
                    $o->location->latitude = $facility->facility_latitude;
                    $o->location->longitude = $facility->facility_longitude;
                    
                    $o->location->address_line1 = $facility->ADDRESS_LINE1;
                    $o->location->address_line2 = $facility->ADDRESS_LINE2;
                    $o->location->address_prov = $facility->ADDRESS_PROV;
                    $o->location->address_city = $facility->ADDRESS_CITY;
                    $o->location->address_postalcode = $facility->ADDRESS_POSTAL;
                    
					$this->db->select('*');
					$this->db->from('facility_rankings');
					$this->db->where('NPRI_ID', $o->whole_npri_id);
					$ranking_query = $this->db->get();
					
					$o->ranking = null;
					if( $ranking_query->num_rows() > 0 ) {
						$r_row = $ranking_query->row();
						if( isset($r_row->Rank) && isset($r_row->PercentRank) ) {
							$o->ranking = new stdClass;
							$o->ranking->rank = $r_row->Rank;
							$o->ranking->percent_rank = ($r_row->PercentRank * 100);
						}
					}
					
					$o->riding = new stdClass;
					$o->riding->mp = $htv_mp;
                    
                    $results[] = $o;
                }
                unset($facility);
                
                if( $results ) {
					$this->_output_json(array('status' => 'OK', 'results' => $results, 'search' => $search_info));
				} else {
					$this->_output_json(array('status' => 'BAD', 'message' => 'No results match search criteria.'));
				}
            }
            else {
                $this->_output_json(array('status' => 'BAD', 'message' => 'No results match search criteria.'));
            }
        }
        else {
            $this->_output_json(array('status' => 'BAD', 'message' => 'Invalid search.'));
        }
    }
    
    /**
     * _output_json
     * 
     * @access: protected
     * @param: mixed
     * @return: void
     */
    protected function _output_json($data) {
        header("Content-Type: text/javascript; charset=utf-8");
        print json_encode($data);
    }
    
    /**
    * _retrieve_sectors
    *
    * @access: protected
    * @param: void
    * @return: array
    */
    protected function _retrieve_sectors() {
		$this->db->select('*');
		$this->db->from('sectors');
		$query = $this->db->get();
		
		$results = array();
		if( $query->num_rows() > 0 ) {
			foreach( $query->result() as $row ) {
				$results[$row->csi2_code] = $row->name;
			}
		}
		
		return $results;
    }
    
    protected function _retrieve_chemsubstance_air_ranks($npri_id) {
		$return = array();
		if( !isset($this->ogdi) )
			return $return;
		
		$chem_air_ranks = $this->ogdi->query('emittersubstancerankair', "npri_id eq '{$npri_id}'");
		if( !$chem_air_ranks )
			return $return;
		
		foreach( $chem_air_ranks as &$rank ) {
			$return[$rank->cas_number] = $rank;
		}
		unset($chem_air_ranks, $rank);
		
		return $return;
    }
}