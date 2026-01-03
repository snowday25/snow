<?php
/**
 * Dashboard
 * 
 * Admin dashboard with stats and manual generation
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Dashboard {
    
    /**
     * Render dashboard
     */
    public static function render() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Handle manual generation
        if (isset($_POST['snow_alerts_generate_nonce']) && wp_verify_nonce($_POST['snow_alerts_generate_nonce'], 'snow_alerts_manual_generate')) {
            self::handle_manual_generation();
        }
        
        // Get statistics
        $database = new Snow_Alerts_Database();
        $stats = $database->get_statistics();
        $recent_articles = $database->get_recent_articles(10);
        
        // Load view
        require_once SNOW_ALERTS_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Handle manual generation
     */
    private static function handle_manual_generation() {
        $scheduler = new Snow_Alerts_Scheduler();
        $result = $scheduler->generate_manual();
        
        if ($result) {
            add_settings_error(
                'snow_alerts_messages',
                'snow_alerts_message',
                sprintf(__('Article generated successfully! Post ID: %d', 'snow-alerts'), $result),
                'success'
            );
        } else {
            add_settings_error(
                'snow_alerts_messages',
                'snow_alerts_message',
                __('Failed to generate article. Please check that you have configured API keys and there is snow in the forecast.', 'snow-alerts'),
                'error'
            );
        }
    }
}
