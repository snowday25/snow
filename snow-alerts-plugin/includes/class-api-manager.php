<?php
/**
 * API Manager Class
 *
 * Handles secure API key storage and retrieval with AES-256-CBC encryption
 *
 * @package Snow_Alerts
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_API_Manager {
    
    /**
     * Encryption method
     */
    const ENCRYPTION_METHOD = 'AES-256-CBC';
    
    /**
     * Get encryption key
     *
     * @return string Encryption key
     */
    private static function get_encryption_key() {
        // Use WordPress authentication keys as encryption base
        $key = AUTH_KEY . SECURE_AUTH_KEY . LOGGED_IN_KEY . NONCE_KEY;
        return substr(hash('sha256', $key), 0, 32);
    }
    
    /**
     * Get encryption IV
     *
     * @return string Encryption IV
     */
    private static function get_encryption_iv() {
        $iv = AUTH_SALT . SECURE_AUTH_SALT . LOGGED_IN_SALT . NONCE_SALT;
        return substr(hash('sha256', $iv), 0, 16);
    }
    
    /**
     * Encrypt API key
     *
     * @param string $key API key to encrypt
     * @return string Encrypted API key
     */
    public static function encrypt_api_key($key) {
        if (empty($key)) {
            return '';
        }
        
        $encryption_key = self::get_encryption_key();
        $iv = self::get_encryption_iv();
        
        $encrypted = openssl_encrypt($key, self::ENCRYPTION_METHOD, $encryption_key, 0, $iv);
        
        return base64_encode($encrypted);
    }
    
    /**
     * Decrypt API key
     *
     * @param string $encrypted_key Encrypted API key
     * @return string Decrypted API key
     */
    public static function decrypt_api_key($encrypted_key) {
        if (empty($encrypted_key)) {
            return '';
        }
        
        $encryption_key = self::get_encryption_key();
        $iv = self::get_encryption_iv();
        
        $decoded = base64_decode($encrypted_key);
        
        if ($decoded === false) {
            return '';
        }
        
        $decrypted = openssl_decrypt($decoded, self::ENCRYPTION_METHOD, $encryption_key, 0, $iv);
        
        return $decrypted !== false ? $decrypted : '';
    }
    
    /**
     * Save API key (encrypted)
     *
     * @param string $api_name API name (weatherapi, openai, etc.)
     * @param string $api_key API key to save
     * @return bool Success status
     */
    public static function save_api_key($api_name, $api_key) {
        $option_name = 'snow_alerts_api_key_' . sanitize_key($api_name);
        
        if (empty($api_key)) {
            return delete_option($option_name);
        }
        
        $encrypted_key = self::encrypt_api_key($api_key);
        
        return update_option($option_name, $encrypted_key);
    }
    
    /**
     * Get API key (decrypted)
     *
     * @param string $api_name API name (weatherapi, openai, etc.)
     * @return string Decrypted API key
     */
    public static function get_api_key($api_name) {
        $option_name = 'snow_alerts_api_key_' . sanitize_key($api_name);
        $encrypted_key = get_option($option_name);
        
        if (empty($encrypted_key)) {
            return '';
        }
        
        return self::decrypt_api_key($encrypted_key);
    }
    
    /**
     * Check if API key exists
     *
     * @param string $api_name API name
     * @return bool True if key exists
     */
    public static function has_api_key($api_name) {
        $key = self::get_api_key($api_name);
        return !empty($key);
    }
    
    /**
     * Delete API key
     *
     * @param string $api_name API name
     * @return bool Success status
     */
    public static function delete_api_key($api_name) {
        $option_name = 'snow_alerts_api_key_' . sanitize_key($api_name);
        return delete_option($option_name);
    }
    
    /**
     * Get all configured APIs
     *
     * @return array Array of API names that have keys configured
     */
    public static function get_configured_apis() {
        $apis = array('weatherapi', 'openai', 'windy');
        $configured = array();
        
        foreach ($apis as $api) {
            if (self::has_api_key($api)) {
                $configured[] = $api;
            }
        }
        
        return $configured;
    }
    
    /**
     * Validate API key format
     *
     * @param string $api_name API name
     * @param string $api_key API key to validate
     * @return bool|WP_Error True if valid, WP_Error if invalid
     */
    public static function validate_api_key($api_name, $api_key) {
        if (empty($api_key)) {
            return new WP_Error('empty_key', __('API key cannot be empty.', 'snow-alerts'));
        }
        
        // Basic validation based on API
        switch ($api_name) {
            case 'weatherapi':
                // WeatherAPI keys are typically 32 characters
                if (strlen($api_key) < 20) {
                    return new WP_Error('invalid_key', __('WeatherAPI key appears to be invalid.', 'snow-alerts'));
                }
                break;
                
            case 'openai':
                // OpenAI keys start with 'sk-'
                if (strpos($api_key, 'sk-') !== 0) {
                    return new WP_Error('invalid_key', __('OpenAI API key must start with "sk-".', 'snow-alerts'));
                }
                break;
                
            case 'windy':
                // Windy keys are typically alphanumeric
                if (strlen($api_key) < 10) {
                    return new WP_Error('invalid_key', __('Windy API key appears to be invalid.', 'snow-alerts'));
                }
                break;
        }
        
        return true;
    }
    
    /**
     * Test API connection
     *
     * @param string $api_name API name
     * @param string $api_key API key to test
     * @return bool|WP_Error True if connection successful, WP_Error on failure
     */
    public static function test_api_connection($api_name, $api_key) {
        switch ($api_name) {
            case 'weatherapi':
                return self::test_weatherapi_connection($api_key);
                
            case 'openai':
                return self::test_openai_connection($api_key);
                
            case 'windy':
                // Windy doesn't have a test endpoint, just validate format
                return true;
                
            default:
                return new WP_Error('unknown_api', __('Unknown API name.', 'snow-alerts'));
        }
    }
    
    /**
     * Test WeatherAPI connection
     *
     * @param string $api_key API key
     * @return bool|WP_Error
     */
    private static function test_weatherapi_connection($api_key) {
        $url = 'https://api.weatherapi.com/v1/current.json?key=' . urlencode($api_key) . '&q=New York';
        
        $response = wp_remote_get($url, array('timeout' => 10));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code === 200) {
            return true;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        $error_message = isset($data['error']['message']) ? $data['error']['message'] : __('API connection failed.', 'snow-alerts');
        
        return new WP_Error('api_error', $error_message);
    }
    
    /**
     * Test OpenAI connection
     *
     * @param string $api_key API key
     * @return bool|WP_Error
     */
    private static function test_openai_connection($api_key) {
        $url = 'https://api.openai.com/v1/models';
        
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $code = wp_remote_retrieve_response_code($response);
        
        if ($code === 200) {
            return true;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        $error_message = isset($data['error']['message']) ? $data['error']['message'] : __('API connection failed.', 'snow-alerts');
        
        return new WP_Error('api_error', $error_message);
    }
}
