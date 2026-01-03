<?php
/**
 * Weather Fetcher Class
 *
 * Fetches weather data from multiple APIs (WeatherAPI.com, Weather.gov)
 * NO ?? operators - uses isset() ternary for PHP 7.4 compatibility
 *
 * @package Snow_Alerts
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Weather_Fetcher {
    
    /**
     * Get weather data for a city
     *
     * @param array $city City data with coordinates
     * @return array|WP_Error Weather data or error
     */
    public static function get_weather_data($city) {
        // Check cache first
        $cached = Snow_Alerts_Database::get_cached_weather(
            isset($city['city']) ? $city['city'] : '',
            isset($city['state']) ? $city['state'] : ''
        );
        
        if ($cached !== null) {
            return $cached;
        }
        
        $weather_data = array();
        
        // Fetch from WeatherAPI.com
        $weatherapi_data = self::fetch_from_weatherapi($city);
        if (!is_wp_error($weatherapi_data)) {
            $weather_data['weather_api'] = $weatherapi_data;
        }
        
        // Fetch from Weather.gov (NWS)
        $nws_data = self::fetch_from_nws($city);
        if (!is_wp_error($nws_data)) {
            $weather_data['nws_alerts'] = $nws_data;
        }
        
        // Cache the combined data
        if (!empty($weather_data)) {
            Snow_Alerts_Database::cache_weather(
                isset($city['city']) ? $city['city'] : '',
                isset($city['state']) ? $city['state'] : '',
                $weather_data,
                3600 // 1 hour cache
            );
        }
        
        return !empty($weather_data) ? $weather_data : new WP_Error('no_data', __('No weather data available', 'snow-alerts'));
    }
    
    /**
     * Fetch weather from WeatherAPI.com
     *
     * @param array $city City data
     * @return array|WP_Error Weather data or error
     */
    private static function fetch_from_weatherapi($city) {
        $api_key = Snow_Alerts_API_Manager::get_api_key('weatherapi');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('WeatherAPI key not configured', 'snow-alerts'));
        }
        
        $city_name = isset($city['city']) ? $city['city'] : '';
        $state = isset($city['state']) ? $city['state'] : '';
        
        if (empty($city_name) || empty($state)) {
            return new WP_Error('invalid_city', __('Invalid city data', 'snow-alerts'));
        }
        
        $query = urlencode($city_name . ', ' . $state);
        $url = "https://api.weatherapi.com/v1/forecast.json?key={$api_key}&q={$query}&days=7&alerts=yes";
        
        $response = wp_remote_get($url, array('timeout' => 15));
        
        // Log API activity
        $response_code = is_wp_error($response) ? null : wp_remote_retrieve_response_code($response);
        $success = !is_wp_error($response) && $response_code === 200;
        
        Snow_Alerts_Database::log_api_activity(
            'WeatherAPI',
            $url,
            array('city' => $city_name, 'state' => $state),
            $response_code,
            is_wp_error($response) ? $response->get_error_message() : 'Success',
            $success
        );
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if ($response_code !== 200) {
            return new WP_Error('api_error', __('WeatherAPI request failed', 'snow-alerts'));
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!is_array($data)) {
            return new WP_Error('invalid_response', __('Invalid API response', 'snow-alerts'));
        }
        
        return $data;
    }
    
    /**
     * Fetch alerts from Weather.gov (NWS)
     *
     * @param array $city City data
     * @return array|WP_Error Alerts data or error
     */
    private static function fetch_from_nws($city) {
        $lat = isset($city['latitude']) ? floatval($city['latitude']) : 0;
        $lon = isset($city['longitude']) ? floatval($city['longitude']) : 0;
        
        if ($lat === 0 || $lon === 0) {
            return new WP_Error('invalid_coordinates', __('Invalid coordinates', 'snow-alerts'));
        }
        
        // Get grid point first
        $grid_url = "https://api.weather.gov/points/{$lat},{$lon}";
        
        $response = wp_remote_get($grid_url, array(
            'timeout' => 15,
            'headers' => array(
                'User-Agent' => 'SnowAlertsPlugin/1.0 (WordPress Plugin)'
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code !== 200) {
            return new WP_Error('nws_error', __('NWS grid point request failed', 'snow-alerts'));
        }
        
        $body = wp_remote_retrieve_body($response);
        $grid_data = json_decode($body, true);
        
        if (!is_array($grid_data)) {
            return new WP_Error('invalid_response', __('Invalid NWS response', 'snow-alerts'));
        }
        
        // Get alerts URL from grid data
        $alerts_url = isset($grid_data['properties']['county']) ? $grid_data['properties']['county'] : '';
        
        if (empty($alerts_url)) {
            // Use point-based alerts as fallback
            $alerts_url = "https://api.weather.gov/alerts/active?point={$lat},{$lon}";
        } else {
            // Extract zone from county URL and get alerts for that zone
            $alerts_url = "https://api.weather.gov/alerts/active?point={$lat},{$lon}";
        }
        
        $alerts_response = wp_remote_get($alerts_url, array(
            'timeout' => 15,
            'headers' => array(
                'User-Agent' => 'SnowAlertsPlugin/1.0 (WordPress Plugin)'
            )
        ));
        
        // Log API activity
        $alert_code = is_wp_error($alerts_response) ? null : wp_remote_retrieve_response_code($alerts_response);
        $success = !is_wp_error($alerts_response) && $alert_code === 200;
        
        Snow_Alerts_Database::log_api_activity(
            'NWS',
            $alerts_url,
            array('lat' => $lat, 'lon' => $lon),
            $alert_code,
            is_wp_error($alerts_response) ? $alerts_response->get_error_message() : 'Success',
            $success
        );
        
        if (is_wp_error($alerts_response)) {
            return $alerts_response;
        }
        
        if ($alert_code !== 200) {
            return new WP_Error('nws_error', __('NWS alerts request failed', 'snow-alerts'));
        }
        
        $alerts_body = wp_remote_retrieve_body($alerts_response);
        $alerts_data = json_decode($alerts_body, true);
        
        if (!is_array($alerts_data)) {
            return new WP_Error('invalid_response', __('Invalid NWS alerts response', 'snow-alerts'));
        }
        
        return $alerts_data;
    }
    
    /**
     * Extract snow-related alerts from NWS data
     * FIXED: NO ?? operators - uses isset() ternary
     *
     * @param array $nws_data NWS alerts data
     * @return array Snow-related alerts
     */
    public static function extract_snow_alerts($nws_data) {
        $snow_alerts = array();
        
        if (!isset($nws_data['features']) || !is_array($nws_data['features'])) {
            return $snow_alerts;
        }
        
        $snow_keywords = array('snow', 'winter', 'blizzard', 'ice', 'freezing', 'wintry');
        
        foreach ($nws_data['features'] as $feature) {
            // FIXED: Use isset() instead of ??
            $props = isset($feature['properties']) ? $feature['properties'] : array();
            $event = isset($props['event']) ? $props['event'] : '';
            $headline = isset($props['headline']) ? $props['headline'] : '';
            $description = isset($props['description']) ? $props['description'] : '';
            
            // Check if alert is snow-related
            $is_snow_related = false;
            $event_lower = strtolower($event);
            
            foreach ($snow_keywords as $keyword) {
                if (strpos($event_lower, $keyword) !== false) {
                    $is_snow_related = true;
                    break;
                }
            }
            
            if ($is_snow_related) {
                $snow_alerts[] = array(
                    'event' => $event,
                    'headline' => $headline,
                    'description' => $description,
                    'severity' => isset($props['severity']) ? $props['severity'] : 'Unknown',
                    'urgency' => isset($props['urgency']) ? $props['urgency'] : 'Unknown',
                    'onset' => isset($props['onset']) ? $props['onset'] : '',
                    'expires' => isset($props['expires']) ? $props['expires'] : '',
                    'instruction' => isset($props['instruction']) ? $props['instruction'] : ''
                );
            }
        }
        
        return $snow_alerts;
    }
    
    /**
     * Extract snowfall forecast from WeatherAPI data
     * FIXED: NO ?? operators - uses isset() ternary
     *
     * @param array $weather_data WeatherAPI data
     * @return array Snowfall forecast
     */
    public static function extract_snowfall_forecast($weather_data) {
        $snowfall = array();
        
        if (!isset($weather_data['forecast']['forecastday']) || !is_array($weather_data['forecast']['forecastday'])) {
            return $snowfall;
        }
        
        foreach ($weather_data['forecast']['forecastday'] as $day) {
            // FIXED: Use isset() instead of ??
            $date = isset($day['date']) ? $day['date'] : '';
            $day_data = isset($day['day']) ? $day['day'] : array();
            
            $snow_cm = isset($day_data['totalsnow_cm']) ? floatval($day_data['totalsnow_cm']) : 0;
            
            if ($snow_cm > 0) {
                // Convert cm to inches
                $snow_inches = $snow_cm / 2.54;
                
                $snowfall[] = array(
                    'date' => $date,
                    'snow_cm' => $snow_cm,
                    'snow_inches' => round($snow_inches, 1),
                    'max_temp_f' => isset($day_data['maxtemp_f']) ? floatval($day_data['maxtemp_f']) : 0,
                    'min_temp_f' => isset($day_data['mintemp_f']) ? floatval($day_data['mintemp_f']) : 0,
                    'condition' => isset($day_data['condition']['text']) ? $day_data['condition']['text'] : ''
                );
            }
        }
        
        return $snowfall;
    }
    
    /**
     * Check if city has significant snow forecast
     *
     * @param array $weather_data Combined weather data
     * @param float $min_inches Minimum inches to consider significant
     * @return bool True if significant snow forecast exists
     */
    public static function has_significant_snow($weather_data, $min_inches = 2.0) {
        // Check WeatherAPI forecast
        if (isset($weather_data['weather_api'])) {
            $snowfall = self::extract_snowfall_forecast($weather_data['weather_api']);
            
            foreach ($snowfall as $day) {
                if (isset($day['snow_inches']) && $day['snow_inches'] >= $min_inches) {
                    return true;
                }
            }
        }
        
        // Check NWS alerts
        if (isset($weather_data['nws_alerts'])) {
            $snow_alerts = self::extract_snow_alerts($weather_data['nws_alerts']);
            
            if (!empty($snow_alerts)) {
                return true;
            }
        }
        
        return false;
    }
}
