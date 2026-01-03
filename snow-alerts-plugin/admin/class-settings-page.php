<?php
/**
 * Settings Page
 * 
 * Admin settings page with API keys, configuration options
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Settings_Page {
    
    /**
     * Render settings page
     */
    public static function render() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Handle form submission
        if (isset($_POST['snow_alerts_settings_nonce']) && wp_verify_nonce($_POST['snow_alerts_settings_nonce'], 'snow_alerts_save_settings')) {
            self::save_settings();
        }
        
        // Load view
        require_once SNOW_ALERTS_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * Save settings
     */
    private static function save_settings() {
        $api_manager = new Snow_Alerts_API_Manager();
        
        // Save API keys (encrypted)
        if (isset($_POST['weatherapi_key'])) {
            $api_manager->save_api_key('weatherapi', sanitize_text_field($_POST['weatherapi_key']));
        }
        
        if (isset($_POST['openai_key'])) {
            $api_manager->save_api_key('openai', sanitize_text_field($_POST['openai_key']));
        }
        
        if (isset($_POST['unsplash_key'])) {
            $api_manager->save_api_key('unsplash', sanitize_text_field($_POST['unsplash_key']));
        }
        
        // Save general settings
        $settings = array(
            'auto_generation_enabled' => isset($_POST['auto_generation_enabled']) ? 1 : 0,
            'check_interval' => isset($_POST['check_interval']) ? sanitize_text_field($_POST['check_interval']) : 'hourly',
            'minimum_snow_amount' => isset($_POST['minimum_snow_amount']) ? floatval($_POST['minimum_snow_amount']) : 2,
            'author_name' => isset($_POST['author_name']) ? sanitize_text_field($_POST['author_name']) : get_bloginfo('name'),
            'facebook_url' => isset($_POST['facebook_url']) ? esc_url_raw($_POST['facebook_url']) : '',
            'twitter_url' => isset($_POST['twitter_url']) ? esc_url_raw($_POST['twitter_url']) : '',
            'instagram_url' => isset($_POST['instagram_url']) ? esc_url_raw($_POST['instagram_url']) : '',
        );
        
        foreach ($settings as $key => $value) {
            update_option('snow_alerts_' . $key, $value);
        }
        
        // Import locations if requested
        if (isset($_POST['import_locations']) && $_POST['import_locations'] === '1') {
            $location_manager = new Snow_Alerts_Location_Manager();
            $imported = $location_manager->import_locations_to_db();
            
            add_settings_error(
                'snow_alerts_messages',
                'snow_alerts_message',
                sprintf(__('%d locations imported successfully.', 'snow-alerts'), $imported),
                'success'
            );
        }
        
        add_settings_error(
            'snow_alerts_messages',
            'snow_alerts_message',
            __('Settings saved successfully.', 'snow-alerts'),
            'success'
        );
    }
}
