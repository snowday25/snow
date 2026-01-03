<?php
/**
 * Location Manager
 * 
 * Manages all snow-prone locations across the United States
 * Loads from us-snow-locations.json with comprehensive filtering
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Location_Manager {
    
    /**
     * Cached locations
     */
    private $locations = null;
    
    /**
     * Population thresholds for classification
     */
    const CITY_MIN_POPULATION = 100000;
    const TOWN_MIN_POPULATION = 25000;
    const VILLAGE_MIN_POPULATION = 5000;
    
    /**
     * Load locations from JSON file
     * 
     * @return array Array of locations
     */
    public function load_locations() {
        if ($this->locations !== null) {
            return $this->locations;
        }
        
        $json_file = SNOW_ALERTS_PLUGIN_DIR . 'data/us-snow-locations.json';
        
        if (!file_exists($json_file)) {
            error_log('[Snow Alerts] Location file not found: ' . $json_file);
            return array();
        }
        
        $json_content = file_get_contents($json_file);
        if ($json_content === false) {
            error_log('[Snow Alerts] Failed to read location file');
            return array();
        }
        
        $data = json_decode($json_content, true);
        
        if ($data === null || !isset($data['locations'])) {
            error_log('[Snow Alerts] Invalid JSON in location file');
            return array();
        }
        
        $this->locations = $data['locations'];
        return $this->locations;
    }
    
    /**
     * Get all snow locations
     * 
     * @return array All locations
     */
    public function get_all_snow_locations() {
        $locations = $this->load_locations();
        return $locations;
    }
    
    /**
     * Get locations by type
     * 
     * @param string $type Type: city, town, village, locality
     * @return array Filtered locations
     */
    public function get_locations_by_type($type) {
        $locations = $this->load_locations();
        
        if (empty($locations)) {
            return array();
        }
        
        $filtered = array();
        
        foreach ($locations as $location) {
            if (!isset($location['type'])) {
                continue;
            }
            
            if ($location['type'] === $type) {
                $filtered[] = $location;
            }
        }
        
        return $filtered;
    }
    
    /**
     * Get cities only (population >= 100,000)
     * 
     * @return array Cities
     */
    public function get_cities_only() {
        return $this->get_locations_by_type('city');
    }
    
    /**
     * Get towns only (population 25,000 - 99,999)
     * 
     * @return array Towns
     */
    public function get_towns_only() {
        return $this->get_locations_by_type('town');
    }
    
    /**
     * Get villages only (population 5,000 - 24,999)
     * 
     * @return array Villages
     */
    public function get_villages_only() {
        return $this->get_locations_by_type('village');
    }
    
    /**
     * Get small localities (population < 5,000)
     * 
     * @return array Small localities
     */
    public function get_small_localities() {
        return $this->get_locations_by_type('locality');
    }
    
    /**
     * Get random location with weighted selection
     * Larger cities are more likely to be selected
     * 
     * @return array|null Random location or null if none available
     */
    public function get_random_location_weighted() {
        $locations = $this->load_locations();
        
        if (empty($locations)) {
            return null;
        }
        
        // Calculate weights based on population
        $weights = array();
        $total_weight = 0;
        
        foreach ($locations as $index => $location) {
            if (!isset($location['population'])) {
                $weight = 1;
            } else {
                // Weight is logarithmic of population (larger cities weighted more)
                $population = intval($location['population']);
                $weight = $population > 0 ? log($population + 1) : 1;
            }
            
            $weights[$index] = $weight;
            $total_weight += $weight;
        }
        
        // Select random location based on weight
        $random = mt_rand() / mt_getrandmax() * $total_weight;
        $current_weight = 0;
        
        foreach ($weights as $index => $weight) {
            $current_weight += $weight;
            if ($random <= $current_weight) {
                return $locations[$index];
            }
        }
        
        // Fallback to last location
        return $locations[count($locations) - 1];
    }
    
    /**
     * Search locations by name
     * Fuzzy search support
     * 
     * @param string $search_term Search term
     * @return array Matching locations
     */
    public function search_locations($search_term) {
        $locations = $this->load_locations();
        
        if (empty($locations) || empty($search_term)) {
            return array();
        }
        
        $search_term = strtolower(trim($search_term));
        $results = array();
        
        foreach ($locations as $location) {
            if (!isset($location['name'])) {
                continue;
            }
            
            $location_name = strtolower($location['name']);
            
            // Exact match
            if ($location_name === $search_term) {
                $results[] = $location;
                continue;
            }
            
            // Contains match
            if (strpos($location_name, $search_term) !== false) {
                $results[] = $location;
                continue;
            }
            
            // Fuzzy match using similar_text
            $similarity = 0;
            similar_text($location_name, $search_term, $similarity);
            
            if ($similarity > 70) {
                $results[] = $location;
            }
        }
        
        return $results;
    }
    
    /**
     * Classify location by population
     * 
     * @param int $population Population count
     * @return string Type: city, town, village, or locality
     */
    public function classify_by_population($population) {
        if ($population >= self::CITY_MIN_POPULATION) {
            return 'city';
        } elseif ($population >= self::TOWN_MIN_POPULATION) {
            return 'town';
        } elseif ($population >= self::VILLAGE_MIN_POPULATION) {
            return 'village';
        } else {
            return 'locality';
        }
    }
    
    /**
     * Get statistics about loaded locations
     * 
     * @return array Statistics
     */
    public function get_statistics() {
        $locations = $this->load_locations();
        
        if (empty($locations)) {
            return array(
                'total' => 0,
                'by_type' => array(),
                'by_state' => array()
            );
        }
        
        $stats = array(
            'total' => count($locations),
            'by_type' => array(
                'city' => 0,
                'town' => 0,
                'village' => 0,
                'locality' => 0
            ),
            'by_state' => array()
        );
        
        foreach ($locations as $location) {
            // Count by type
            if (isset($location['type'])) {
                if (isset($stats['by_type'][$location['type']])) {
                    $stats['by_type'][$location['type']]++;
                }
            }
            
            // Count by state
            if (isset($location['state'])) {
                if (!isset($stats['by_state'][$location['state']])) {
                    $stats['by_state'][$location['state']] = 0;
                }
                $stats['by_state'][$location['state']]++;
            }
        }
        
        // Sort states by count
        arsort($stats['by_state']);
        
        return $stats;
    }
}
