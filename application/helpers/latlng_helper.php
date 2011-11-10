<?php
/**
 * latlng_helper.php
 *
 * @author  Aaron McGowan < me@amcgowan.ca >
 */

/**
 * @ignore
 */
defined('BASEPATH') OR exit;

if( !function_exists('calculate_distance_btwn_latlng') ) {

/**
 * calculate_distance_btwn_latlng
 *
 * Calculates the distance between a two coords, specifying two latitude and longitude values.
 *
 * @param: float    Latitude one
 * @param: float    Longitude one
 * @param: float    Latitude two
 * @param: float    Longitude two
 * @return: float   Returns the calculated distance in kilometers.
 */
function calculate_distance_btwn_latlng($lat1, $lng1, $lat2, $lng2) {
    $pi80 = M_PI / 180;
    
    $lat1 *= $pi80;     $lng1 *= $pi80;
    $lat2 *= $pi80;     $lng2 *= $pi80;
    
    $r = 6372.797;
    
    $dlat = $lat2 - $lat1;
    $dlng = $lng2 - $lng1;

    $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    $dist = $r * $c;
    return $dist;
}

}