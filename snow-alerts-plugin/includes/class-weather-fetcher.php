<?php
/**
 * Weather Fetcher
 * 
 * Fetches weather data from multiple APIs (WeatherAPI, NWS, Windy)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Weather_Fetcher {
    
    /**
     * @var Snow_Alerts_API_Manager
     */
    private $api_manager;
    
    /**
     * @var Snow_Alerts_Database
     */
    private $database;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_manager = new Snow_Alerts_API_Manager();
        $this->database = new Snow_Alerts_Database();
    }
    
    /**
     * Fetch weather data for location
     */
    public function fetch_weather($location) {
        $lat = isset($location['latitude']) ? $location['latitude'] : 0;
        $lon = isset($location['longitude']) ? $location['longitude'] : 0;
        $name = isset($location['name']) ? $location['name'] : '';
        
        // Try WeatherAPI first
        $weather_data = $this->fetch_from_weatherapi($lat, $lon, $name);
        
        if ($weather_data && isset($weather_data['success']) && $weather_data['success']) {
            return $weather_data;
        }
        
        // Fallback to NWS
        $weather_data = $this->fetch_from_nws($lat, $lon);
        
        if ($weather_data && isset($weather_data['success']) && $weather_data['success']) {
            return $weather_data;
        }
        
        // Return error
        return array(
            'success' => false,
            'error' => 'Failed to fetch weather data from all sources',
        );
    }
    
    /**
     * Fetch from WeatherAPI.com
     */
    private function fetch_from_weatherapi($lat, $lon, $location_name) {
        $api_key = $this->api_manager->get_api_key('weatherapi');
        
        if (empty($api_key)) {
            return array('success' => false, 'error' => 'WeatherAPI key not configured');
        }
        
        $coords = $lat . ',' . $lon;
        $url = "https://api.weatherapi.com/v1/forecast.json?key=" . $api_key . "&q=" . urlencode($coords) . "&days=3&alerts=yes";
        
        $start_time = microtime(true);
        $response = wp_remote_get($url, array('timeout' => 15));
        $execution_time = microtime(true) - $start_time;
        
        if (is_wp_error($response)) {
            $this->database->log_api_call(
                'weatherapi',
                $url,
                array('lat' => $lat, 'lon' => $lon),
                $response->get_error_message(),
                0,
                false,
                $response->get_error_message(),
                $execution_time
            );
            
            return array('success' => false, 'error' => $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            $this->database->log_api_call(
                'weatherapi',
                $url,
                array('lat' => $lat, 'lon' => $lon),
                $body,
                $status_code,
                false,
                'HTTP ' . $status_code,
                $execution_time
            );
            
            return array('success' => false, 'error' => 'HTTP ' . $status_code);
        }
        
        $data = json_decode($body, true);
        
        if (!$data || json_last_error() !== JSON_ERROR_NONE) {
            $this->database->log_api_call(
                'weatherapi',
                $url,
                array('lat' => $lat, 'lon' => $lon),
                $body,
                $status_code,
                false,
                'Invalid JSON response',
                $execution_time
            );
            
            return array('success' => false, 'error' => 'Invalid JSON response');
        }
        
        $this->database->log_api_call(
            'weatherapi',
            $url,
            array('lat' => $lat, 'lon' => $lon),
            $body,
            $status_code,
            true,
            '',
            $execution_time
        );
        
        // Parse weather data
        $parsed = $this->parse_weatherapi_response($data, $location_name);
        $parsed['raw_data'] = $data;
        
        return $parsed;
    }
    
    /**
     * Parse WeatherAPI response
     */
    private function parse_weatherapi_response($data, $location_name) {
        $current = isset($data['current']) ? $data['current'] : array();
        $forecast = isset($data['forecast']) && isset($data['forecast']['forecastday']) ? $data['forecast']['forecastday'] : array();
        
        $result = array(
            'success' => true,
            'source' => 'weatherapi',
            'location' => $location_name,
            'current' => array(
                'temperature' => isset($current['temp_f']) ? $current['temp_f'] : 0,
                'feels_like' => isset($current['feelslike_f']) ? $current['feelslike_f'] : 0,
                'condition' => isset($current['condition']) && isset($current['condition']['text']) ? $current['condition']['text'] : '',
                'wind_speed' => isset($current['wind_mph']) ? $current['wind_mph'] : 0,
                'wind_direction' => isset($current['wind_dir']) ? $current['wind_dir'] : '',
                'humidity' => isset($current['humidity']) ? $current['humidity'] : 0,
                'precipitation' => isset($current['precip_in']) ? $current['precip_in'] : 0,
                'snow' => isset($current['snow_cm']) ? $current['snow_cm'] * 0.393701 : 0, // Convert cm to inches
                'visibility' => isset($current['vis_miles']) ? $current['vis_miles'] : 0,
            ),
            'forecast' => array(),
            'has_snow' => false,
            'total_snow' => 0,
        );
        
        // Parse forecast
        foreach ($forecast as $day) {
            $day_data = isset($day['day']) ? $day['day'] : array();
            $snow_cm = isset($day_data['totalsnow_cm']) ? $day_data['totalsnow_cm'] : 0;
            $snow_inches = $snow_cm * 0.393701;
            
            $result['forecast'][] = array(
                'date' => isset($day['date']) ? $day['date'] : '',
                'max_temp' => isset($day_data['maxtemp_f']) ? $day_data['maxtemp_f'] : 0,
                'min_temp' => isset($day_data['mintemp_f']) ? $day_data['mintemp_f'] : 0,
                'condition' => isset($day_data['condition']) && isset($day_data['condition']['text']) ? $day_data['condition']['text'] : '',
                'snow' => $snow_inches,
                'precipitation' => isset($day_data['totalprecip_in']) ? $day_data['totalprecip_in'] : 0,
                'chance_of_snow' => isset($day_data['daily_chance_of_snow']) ? $day_data['daily_chance_of_snow'] : 0,
            );
            
            $result['total_snow'] += $snow_inches;
            
            if ($snow_inches > 0) {
                $result['has_snow'] = true;
            }
        }
        
        return $result;
    }
    
    /**
     * Fetch from National Weather Service
     */
    private function fetch_from_nws($lat, $lon) {
        // NWS Points API
        $points_url = "https://api.weather.gov/points/" . $lat . "," . $lon;
        
        $start_time = microtime(true);
        $response = wp_remote_get($points_url, array(
            'timeout' => 15,
            'headers' => array(
                'User-Agent' => 'Snow-Alerts-Plugin/2.0.0 (WordPress)'
            )
        ));
        $execution_time = microtime(true) - $start_time;
        
        if (is_wp_error($response)) {
            return array('success' => false, 'error' => $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            return array('success' => false, 'error' => 'NWS HTTP ' . $status_code);
        }
        
        $points_data = json_decode($body, true);
        
        if (!$points_data || json_last_error() !== JSON_ERROR_NONE) {
            return array('success' => false, 'error' => 'Invalid NWS response');
        }
        
        // Get forecast URL
        $forecast_url = isset($points_data['properties']) && isset($points_data['properties']['forecast']) ? $points_data['properties']['forecast'] : '';
        
        if (empty($forecast_url)) {
            return array('success' => false, 'error' => 'No forecast URL from NWS');
        }
        
        // Fetch forecast
        $forecast_response = wp_remote_get($forecast_url, array(
            'timeout' => 15,
            'headers' => array(
                'User-Agent' => 'Snow-Alerts-Plugin/2.0.0 (WordPress)'
            )
        ));
        
        if (is_wp_error($forecast_response)) {
            return array('success' => false, 'error' => $forecast_response->get_error_message());
        }
        
        $forecast_body = wp_remote_retrieve_body($forecast_response);
        $forecast_data = json_decode($forecast_body, true);
        
        if (!$forecast_data || json_last_error() !== JSON_ERROR_NONE) {
            return array('success' => false, 'error' => 'Invalid NWS forecast');
        }
        
        $this->database->log_api_call(
            'nws',
            $forecast_url,
            array('lat' => $lat, 'lon' => $lon),
            $forecast_body,
            200,
            true,
            '',
            $execution_time
        );
        
        // Parse NWS data (simplified)
        $result = array(
            'success' => true,
            'source' => 'nws',
            'has_snow' => false,
            'total_snow' => 0,
            'forecast' => array(),
            'raw_data' => $forecast_data,
        );
        
        return $result;
    }
    
    /**
     * Check if location has significant snow
     */
    public function has_significant_snow($weather_data, $threshold = 2) {
        if (!isset($weather_data['success']) || !$weather_data['success']) {
            return false;
        }
        
        if (!isset($weather_data['total_snow'])) {
            return false;
        }
        
        return $weather_data['total_snow'] >= $threshold;
    }
}
