<?php
/**
 * API Manager
 * 
 * Handles encrypted API key storage and retrieval
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_API_Manager {
    
    /**
     * Encryption method
     */
    private $cipher = 'AES-256-CBC';
    
    /**
     * Get encryption key
     */
    private function get_encryption_key() {
        $key = get_option('snow_alerts_encryption_key');
        
        if (!$key) {
            // Generate new key
            $key = bin2hex(random_bytes(32));
            update_option('snow_alerts_encryption_key', $key, false);
        }
        
        return hex2bin($key);
    }
    
    /**
     * Encrypt data
     */
    public function encrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        $key = $this->get_encryption_key();
        $iv_length = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($iv_length);
        
        $encrypted = openssl_encrypt($data, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
        
        if ($encrypted === false) {
            return '';
        }
        
        // Combine IV and encrypted data
        $result = base64_encode($iv . $encrypted);
        
        return $result;
    }
    
    /**
     * Decrypt data
     */
    public function decrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        $key = $this->get_encryption_key();
        $data = base64_decode($data);
        
        $iv_length = openssl_cipher_iv_length($this->cipher);
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);
        
        $decrypted = openssl_decrypt($encrypted, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
        
        if ($decrypted === false) {
            return '';
        }
        
        return $decrypted;
    }
    
    /**
     * Save API key
     */
    public function save_api_key($api_name, $api_key) {
        $encrypted = $this->encrypt($api_key);
        update_option('snow_alerts_api_' . $api_name, $encrypted, false);
    }
    
    /**
     * Get API key
     */
    public function get_api_key($api_name) {
        $encrypted = get_option('snow_alerts_api_' . $api_name, '');
        
        if (empty($encrypted)) {
            return '';
        }
        
        return $this->decrypt($encrypted);
    }
    
    /**
     * Delete API key
     */
    public function delete_api_key($api_name) {
        delete_option('snow_alerts_api_' . $api_name);
    }
    
    /**
     * Check if API key exists
     */
    public function has_api_key($api_name) {
        $key = $this->get_api_key($api_name);
        return !empty($key);
    }
    
    /**
     * Get all configured APIs
     */
    public function get_configured_apis() {
        $apis = array('weatherapi', 'openai', 'unsplash');
        $configured = array();
        
        foreach ($apis as $api) {
            if ($this->has_api_key($api)) {
                $configured[] = $api;
            }
        }
        
        return $configured;
    }
    
    /**
     * Validate API key format
     */
    public function validate_api_key($api_name, $api_key) {
        if (empty($api_key)) {
            return false;
        }
        
        // Basic validation based on API
        switch ($api_name) {
            case 'weatherapi':
                // WeatherAPI keys are 32 characters alphanumeric
                return strlen($api_key) === 32 && ctype_alnum($api_key);
                
            case 'openai':
                // OpenAI keys start with sk-
                return strpos($api_key, 'sk-') === 0;
                
            case 'unsplash':
                // Unsplash keys are alphanumeric with dashes
                return strlen($api_key) > 20 && preg_match('/^[a-zA-Z0-9_-]+$/', $api_key);
                
            default:
                return strlen($api_key) > 10;
        }
    }
}
