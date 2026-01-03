<?php
/**
 * Settings Page Class
 *
 * Handles admin settings interface with nonce verification
 *
 * @package Snow_Alerts
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Settings_Page {
    
    /**
     * Initialize settings page
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_menu_page'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('wp_ajax_snow_alerts_save_api_key', array(__CLASS__, 'ajax_save_api_key'));
        add_action('wp_ajax_snow_alerts_test_api', array(__CLASS__, 'ajax_test_api'));
    }
    
    /**
     * Add admin menu page
     */
    public static function add_menu_page() {
        add_menu_page(
            __('Snow Alerts Settings', 'snow-alerts'),
            __('Snow Alerts', 'snow-alerts'),
            'manage_options',
            'snow-alerts-settings',
            array(__CLASS__, 'render_settings_page'),
            'dashicons-cloud-saved',
            30
        );
    }
    
    /**
     * Register settings
     */
    public static function register_settings() {
        // Schedule settings
        register_setting('snow_alerts_settings', 'snow_alerts_schedule_enabled');
        register_setting('snow_alerts_settings', 'snow_alerts_schedule_interval');
        register_setting('snow_alerts_settings', 'snow_alerts_articles_per_run');
        
        // Content settings
        register_setting('snow_alerts_settings', 'snow_alerts_min_snowfall');
        register_setting('snow_alerts_settings', 'snow_alerts_word_count_min');
        register_setting('snow_alerts_settings', 'snow_alerts_word_count_max');
    }
    
    /**
     * Render settings page
     */
    public static function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'snow-alerts'));
        }
        
        // Handle form submission
        if (isset($_POST['snow_alerts_settings_submit'])) {
            check_admin_referer('snow_alerts_settings_nonce');
            
            // Save settings
            $fields = array(
                'snow_alerts_schedule_enabled',
                'snow_alerts_schedule_interval',
                'snow_alerts_articles_per_run',
                'snow_alerts_min_snowfall',
                'snow_alerts_word_count_min',
                'snow_alerts_word_count_max'
            );
            
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    update_option($field, sanitize_text_field($_POST[$field]));
                }
            }
            
            // Update cron schedule
            $interval = get_option('snow_alerts_schedule_interval', 'hourly');
            Snow_Alerts_Scheduler::update_schedule($interval);
            
            echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved successfully!', 'snow-alerts') . '</p></div>';
        }
        
        include SNOW_ALERTS_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * AJAX handler for saving API keys
     */
    public static function ajax_save_api_key() {
        // Verify nonce
        check_ajax_referer('snow_alerts_admin_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'snow-alerts')));
        }
        
        // Get POST data
        $api_name = isset($_POST['api_name']) ? sanitize_text_field($_POST['api_name']) : '';
        $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
        
        if (empty($api_name)) {
            wp_send_json_error(array('message' => __('API name is required', 'snow-alerts')));
        }
        
        // Validate API key format
        $validation = Snow_Alerts_API_Manager::validate_api_key($api_name, $api_key);
        
        if (is_wp_error($validation)) {
            wp_send_json_error(array('message' => $validation->get_error_message()));
        }
        
        // Save API key (encrypted)
        $saved = Snow_Alerts_API_Manager::save_api_key($api_name, $api_key);
        
        if ($saved) {
            wp_send_json_success(array('message' => __('API key saved successfully', 'snow-alerts')));
        } else {
            wp_send_json_error(array('message' => __('Failed to save API key', 'snow-alerts')));
        }
    }
    
    /**
     * AJAX handler for testing API connection
     */
    public static function ajax_test_api() {
        // Verify nonce
        check_ajax_referer('snow_alerts_admin_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'snow-alerts')));
        }
        
        // Get POST data
        $api_name = isset($_POST['api_name']) ? sanitize_text_field($_POST['api_name']) : '';
        $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
        
        if (empty($api_name) || empty($api_key)) {
            wp_send_json_error(array('message' => __('API name and key are required', 'snow-alerts')));
        }
        
        // Test connection
        $result = Snow_Alerts_API_Manager::test_api_connection($api_name, $api_key);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array('message' => __('API connection successful', 'snow-alerts')));
    }
}

// Initialize settings page
Snow_Alerts_Settings_Page::init();
