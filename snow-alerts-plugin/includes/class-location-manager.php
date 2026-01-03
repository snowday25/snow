<?php
/**
 * Location Manager
 * 
 * Manages snow-prone US locations (cities, towns, villages, localities)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Location_Manager {
    
    /**
     * @var array Cached locations
     */
    private $locations_cache = null;
    
    /**
     * Load locations from JSON file
     */
    public function load_locations() {
        if ($this->locations_cache !== null) {
            return $this->locations_cache;
        }
        
        $json_file = SNOW_ALERTS_PLUGIN_DIR . 'data/us-snow-locations.json';
        
        if (!file_exists($json_file)) {
            return $this->get_default_locations();
        }
        
        $json_content = file_get_contents($json_file);
        $locations = json_decode($json_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($locations)) {
            return $this->get_default_locations();
        }
        
        $this->locations_cache = $locations;
        return $locations;
    }
    
    /**
     * Get default locations (fallback)
     */
    private function get_default_locations() {
        return array(
            array('name' => 'Anchorage', 'state' => 'AK', 'type' => 'city', 'lat' => 61.2181, 'lon' => -149.9003, 'population' => 291538),
            array('name' => 'Fairbanks', 'state' => 'AK', 'type' => 'city', 'lat' => 64.8378, 'lon' => -147.7164, 'population' => 32193),
            array('name' => 'Buffalo', 'state' => 'NY', 'type' => 'city', 'lat' => 42.8864, 'lon' => -78.8784, 'population' => 258612),
            array('name' => 'Syracuse', 'state' => 'NY', 'type' => 'city', 'lat' => 43.0481, 'lon' => -76.1474, 'population' => 142553),
            array('name' => 'Rochester', 'state' => 'NY', 'type' => 'city', 'lat' => 43.1566, 'lon' => -77.6088, 'population' => 206284),
            array('name' => 'Denver', 'state' => 'CO', 'type' => 'city', 'lat' => 39.7392, 'lon' => -104.9903, 'population' => 715522),
            array('name' => 'Boulder', 'state' => 'CO', 'type' => 'city', 'lat' => 40.0150, 'lon' => -105.2705, 'population' => 108090),
            array('name' => 'Minneapolis', 'state' => 'MN', 'type' => 'city', 'lat' => 44.9778, 'lon' => -93.2650, 'population' => 425115),
            array('name' => 'St. Paul', 'state' => 'MN', 'type' => 'city', 'lat' => 44.9537, 'lon' => -93.0900, 'population' => 307193),
            array('name' => 'Duluth', 'state' => 'MN', 'type' => 'city', 'lat' => 46.7867, 'lon' => -92.1005, 'population' => 86697),
            array('name' => 'Milwaukee', 'state' => 'WI', 'type' => 'city', 'lat' => 43.0389, 'lon' => -87.9065, 'population' => 590157),
            array('name' => 'Madison', 'state' => 'WI', 'type' => 'city', 'lat' => 43.0731, 'lon' => -89.4012, 'population' => 269840),
            array('name' => 'Green Bay', 'state' => 'WI', 'type' => 'city', 'lat' => 44.5133, 'lon' => -88.0133, 'population' => 105207),
            array('name' => 'Chicago', 'state' => 'IL', 'type' => 'city', 'lat' => 41.8781, 'lon' => -87.6298, 'population' => 2746388),
            array('name' => 'Detroit', 'state' => 'MI', 'type' => 'city', 'lat' => 42.3314, 'lon' => -83.0458, 'population' => 639111),
            array('name' => 'Grand Rapids', 'state' => 'MI', 'type' => 'city', 'lat' => 42.9634, 'lon' => -85.6681, 'population' => 198917),
            array('name' => 'Cleveland', 'state' => 'OH', 'type' => 'city', 'lat' => 41.4993, 'lon' => -81.6944, 'population' => 372624),
            array('name' => 'Columbus', 'state' => 'OH', 'type' => 'city', 'lat' => 39.9612, 'lon' => -82.9988, 'population' => 905748),
            array('name' => 'Pittsburgh', 'state' => 'PA', 'type' => 'city', 'lat' => 40.4406, 'lon' => -79.9959, 'population' => 302205),
            array('name' => 'Philadelphia', 'state' => 'PA', 'type' => 'city', 'lat' => 39.9526, 'lon' => -75.1652, 'population' => 1584064),
            array('name' => 'Boston', 'state' => 'MA', 'type' => 'city', 'lat' => 42.3601, 'lon' => -71.0589, 'population' => 692600),
            array('name' => 'Worcester', 'state' => 'MA', 'type' => 'city', 'lat' => 42.2626, 'lon' => -71.8023, 'population' => 185428),
            array('name' => 'Portland', 'state' => 'ME', 'type' => 'city', 'lat' => 43.6591, 'lon' => -70.2568, 'population' => 66215),
            array('name' => 'Burlington', 'state' => 'VT', 'type' => 'city', 'lat' => 44.4759, 'lon' => -73.2121, 'population' => 42545),
            array('name' => 'Manchester', 'state' => 'NH', 'type' => 'city', 'lat' => 42.9956, 'lon' => -71.4548, 'population' => 115644),
            array('name' => 'Salt Lake City', 'state' => 'UT', 'type' => 'city', 'lat' => 40.7608, 'lon' => -111.8910, 'population' => 200567),
            array('name' => 'Boise', 'state' => 'ID', 'type' => 'city', 'lat' => 43.6150, 'lon' => -116.2023, 'population' => 228959),
            array('name' => 'Seattle', 'state' => 'WA', 'type' => 'city', 'lat' => 47.6062, 'lon' => -122.3321, 'population' => 753675),
            array('name' => 'Spokane', 'state' => 'WA', 'type' => 'city', 'lat' => 47.6588, 'lon' => -117.4260, 'population' => 222081),
            array('name' => 'Fargo', 'state' => 'ND', 'type' => 'city', 'lat' => 46.8772, 'lon' => -96.7898, 'population' => 125990),
        );
    }
    
    /**
     * Import locations to database
     */
    public function import_locations_to_db() {
        global $wpdb;
        
        $locations = $this->load_locations();
        $table = $wpdb->prefix . 'snow_cities';
        $imported = 0;
        
        foreach ($locations as $location) {
            // Check if already exists
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $table WHERE name = %s AND state = %s",
                    isset($location['name']) ? $location['name'] : '',
                    isset($location['state']) ? $location['state'] : ''
                )
            );
            
            if ($exists) {
                continue;
            }
            
            $wpdb->insert(
                $table,
                array(
                    'name' => isset($location['name']) ? $location['name'] : '',
                    'state' => isset($location['state']) ? $location['state'] : '',
                    'location_type' => isset($location['type']) ? $location['type'] : 'city',
                    'latitude' => isset($location['lat']) ? $location['lat'] : 0,
                    'longitude' => isset($location['lon']) ? $location['lon'] : 0,
                    'population' => isset($location['population']) ? $location['population'] : 0,
                    'elevation' => isset($location['elevation']) ? $location['elevation'] : null,
                    'timezone' => isset($location['timezone']) ? $location['timezone'] : null,
                    'is_active' => 1,
                    'weight' => 1,
                    'created_at' => current_time('mysql'),
                ),
                array('%s', '%s', '%s', '%f', '%f', '%d', '%d', '%s', '%d', '%d', '%s')
            );
            
            $imported++;
        }
        
        return $imported;
    }
    
    /**
     * Get random location with weighted selection
     */
    public function get_random_location() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_cities';
        
        // Get total weight
        $total_weight = (int) $wpdb->get_var(
            "SELECT SUM(weight) FROM $table WHERE is_active = 1"
        );
        
        if ($total_weight <= 0) {
            // Fallback to simple random
            $result = $wpdb->get_row(
                "SELECT * FROM $table WHERE is_active = 1 ORDER BY RAND() LIMIT 1",
                ARRAY_A
            );
            
            return $result ? $result : null;
        }
        
        // Weighted random selection
        $random = mt_rand(1, $total_weight);
        $cumulative = 0;
        
        $results = $wpdb->get_results(
            "SELECT * FROM $table WHERE is_active = 1 ORDER BY weight DESC",
            ARRAY_A
        );
        
        foreach ($results as $location) {
            $weight = isset($location['weight']) ? (int) $location['weight'] : 1;
            $cumulative += $weight;
            
            if ($cumulative >= $random) {
                return $location;
            }
        }
        
        // Fallback
        return $results && isset($results[0]) ? $results[0] : null;
    }
    
    /**
     * Get location by name and state
     */
    public function get_location($name, $state) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_cities';
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE name = %s AND state = %s LIMIT 1",
                $name,
                $state
            ),
            ARRAY_A
        );
        
        return $result ? $result : null;
    }
    
    /**
     * Get locations by state
     */
    public function get_locations_by_state($state, $limit = 50) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_cities';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE state = %s AND is_active = 1 ORDER BY population DESC LIMIT %d",
                $state,
                $limit
            ),
            ARRAY_A
        );
        
        return $results ? $results : array();
    }
    
    /**
     * Get least recently checked locations
     */
    public function get_stale_locations($limit = 10) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_cities';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE is_active = 1 
                ORDER BY last_checked ASC NULLS FIRST 
                LIMIT %d",
                $limit
            ),
            ARRAY_A
        );
        
        return $results ? $results : array();
    }
    
    /**
     * Get total location count
     */
    public function get_location_count() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_cities';
        
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $table WHERE is_active = 1"
        );
    }
    
    /**
     * Get snow-prone states
     */
    public function get_snow_states() {
        return array(
            'AK' => 'Alaska',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        );
    }
}
