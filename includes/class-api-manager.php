<?php
/**
 * API Manager Class
 * 
 * Handles API key management for external services
 *
 * @package Snow_Alerts
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_API_Manager {
    
    /**
     * Get API key for a specific service
     *
     * @param string $service Service name (e.g., 'openai')
     * @return string|null API key or null if not found
     */
    public static function get_api_key($service) {
        // Try to get from WordPress options
        $option_name = 'snow_alerts_' . $service . '_api_key';
        $api_key = get_option($option_name);
        
        if (!empty($api_key)) {
            return $api_key;
        }
        
        // Try to get from environment variable
        $env_var = strtoupper('SNOW_ALERTS_' . $service . '_API_KEY');
        if (defined($env_var)) {
            return constant($env_var);
        }
        
        // Try getenv
        $env_value = getenv($env_var);
        if (!empty($env_value)) {
            return $env_value;
        }
        
        return null;
    }
    
    /**
     * Set API key for a specific service
     *
     * @param string $service Service name
     * @param string $api_key API key value
     * @return bool True on success, false on failure
     */
    public static function set_api_key($service, $api_key) {
        $option_name = 'snow_alerts_' . $service . '_api_key';
        return update_option($option_name, $api_key);
    }
    
    /**
     * Delete API key for a specific service
     *
     * @param string $service Service name
     * @return bool True on success, false on failure
     */
    public static function delete_api_key($service) {
        $option_name = 'snow_alerts_' . $service . '_api_key';
        return delete_option($option_name);
    }
}
