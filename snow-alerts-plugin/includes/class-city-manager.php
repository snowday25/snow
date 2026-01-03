<?php
/**
 * City Manager Class
 *
 * Manages US cities with 100k+ population for snow alerts
 *
 * @package Snow_Alerts
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_City_Manager {
    
    /**
     * Get all cities from JSON file
     *
     * @return array Array of city data
     */
    public static function get_all_cities() {
        $json_file = SNOW_ALERTS_PLUGIN_DIR . 'data/us-cities-100k.json';
        
        if (!file_exists($json_file)) {
            return array();
        }
        
        $json_data = file_get_contents($json_file);
        
        if ($json_data === false) {
            return array();
        }
        
        $cities = json_decode($json_data, true);
        
        return is_array($cities) ? $cities : array();
    }
    
    /**
     * Get a random city
     *
     * @return array|null City data or null if no cities available
     */
    public static function get_random_city() {
        $cities = self::get_all_cities();
        
        if (empty($cities)) {
            return null;
        }
        
        $random_index = array_rand($cities);
        
        return $cities[$random_index];
    }
    
    /**
     * Get multiple random cities
     *
     * @param int $count Number of cities to get
     * @return array Array of city data
     */
    public static function get_random_cities($count = 5) {
        $cities = self::get_all_cities();
        
        if (empty($cities)) {
            return array();
        }
        
        // Shuffle cities and get first $count
        shuffle($cities);
        
        return array_slice($cities, 0, min($count, count($cities)));
    }
    
    /**
     * Get city by name and state
     *
     * @param string $city_name City name
     * @param string $state_code State abbreviation
     * @return array|null City data or null if not found
     */
    public static function get_city($city_name, $state_code) {
        $cities = self::get_all_cities();
        
        foreach ($cities as $city) {
            if (isset($city['city']) && isset($city['state']) &&
                strtolower($city['city']) === strtolower($city_name) &&
                strtoupper($city['state']) === strtoupper($state_code)) {
                return $city;
            }
        }
        
        return null;
    }
    
    /**
     * Get cities by state
     *
     * @param string $state_code State abbreviation
     * @return array Array of cities in the state
     */
    public static function get_cities_by_state($state_code) {
        $cities = self::get_all_cities();
        $state_cities = array();
        
        foreach ($cities as $city) {
            if (isset($city['state']) && strtoupper($city['state']) === strtoupper($state_code)) {
                $state_cities[] = $city;
            }
        }
        
        return $state_cities;
    }
    
    /**
     * Get snow-prone cities
     * Returns cities in states known for significant snowfall
     *
     * @return array Array of snow-prone cities
     */
    public static function get_snow_prone_cities() {
        $snow_states = array(
            'AK', 'CO', 'CT', 'ID', 'IL', 'IN', 'IA', 'ME', 'MA', 'MI',
            'MN', 'MT', 'NH', 'NJ', 'NY', 'ND', 'OH', 'OR', 'PA', 'RI',
            'SD', 'UT', 'VT', 'WA', 'WV', 'WI', 'WY'
        );
        
        $cities = self::get_all_cities();
        $snow_cities = array();
        
        foreach ($cities as $city) {
            if (isset($city['state']) && in_array(strtoupper($city['state']), $snow_states)) {
                $snow_cities[] = $city;
            }
        }
        
        return $snow_cities;
    }
    
    /**
     * Get cities that haven't had recent articles
     *
     * @param int $days Number of days to check for recent articles
     * @param int $count Number of cities to return
     * @return array Array of city data
     */
    public static function get_cities_needing_articles($days = 7, $count = 5) {
        global $wpdb;
        
        $cities = self::get_snow_prone_cities();
        
        if (empty($cities)) {
            return array();
        }
        
        $table = $wpdb->prefix . 'snow_alerts_articles';
        
        // Get cities with recent articles
        $recent_cities = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT city, state FROM {$table} WHERE created_at >= %s",
            date('Y-m-d H:i:s', strtotime("-{$days} days"))
        ));
        
        // Create array of recent city-state combinations
        $recent_combinations = array();
        if ($recent_cities) {
            foreach ($recent_cities as $recent) {
                $recent_combinations[] = strtolower($recent->city) . '_' . strtoupper($recent->state);
            }
        }
        
        // Filter out cities with recent articles
        $available_cities = array();
        foreach ($cities as $city) {
            $combination = strtolower($city['city']) . '_' . strtoupper($city['state']);
            if (!in_array($combination, $recent_combinations)) {
                $available_cities[] = $city;
            }
        }
        
        // If all cities have recent articles, return random snow-prone cities
        if (empty($available_cities)) {
            $available_cities = $cities;
        }
        
        // Shuffle and return requested count
        shuffle($available_cities);
        
        return array_slice($available_cities, 0, min($count, count($available_cities)));
    }
    
    /**
     * Validate city data structure
     *
     * @param array $city City data array
     * @return bool True if valid
     */
    public static function validate_city_data($city) {
        $required_fields = array('city', 'state', 'latitude', 'longitude', 'population');
        
        foreach ($required_fields as $field) {
            if (!isset($city[$field]) || empty($city[$field])) {
                return false;
            }
        }
        
        // Validate coordinates
        $lat = floatval($city['latitude']);
        $lon = floatval($city['longitude']);
        
        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            return false;
        }
        
        // Validate state code
        if (strlen($city['state']) !== 2) {
            return false;
        }
        
        // Validate population
        if (intval($city['population']) < 100000) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Format city name for display
     *
     * @param array $city City data
     * @return string Formatted city name (e.g., "New York, NY")
     */
    public static function format_city_name($city) {
        if (!isset($city['city']) || !isset($city['state'])) {
            return '';
        }
        
        return $city['city'] . ', ' . strtoupper($city['state']);
    }
    
    /**
     * Get total city count
     *
     * @return int Number of cities
     */
    public static function get_city_count() {
        $cities = self::get_all_cities();
        return count($cities);
    }
}
