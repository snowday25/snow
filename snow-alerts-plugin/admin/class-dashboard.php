<?php
/**
 * Dashboard Class
 *
 * Handles admin dashboard with statistics and manual generation
 *
 * @package Snow_Alerts
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Dashboard {
    
    /**
     * Initialize dashboard
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_menu_page'));
        add_action('wp_ajax_snow_alerts_generate_now', array(__CLASS__, 'ajax_generate_now'));
    }
    
    /**
     * Add admin menu page
     */
    public static function add_menu_page() {
        add_submenu_page(
            'snow-alerts-settings',
            __('Dashboard', 'snow-alerts'),
            __('Dashboard', 'snow-alerts'),
            'manage_options',
            'snow-alerts-dashboard',
            array(__CLASS__, 'render_dashboard_page')
        );
    }
    
    /**
     * Render dashboard page
     */
    public static function render_dashboard_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'snow-alerts'));
        }
        
        // Get statistics
        $stats = Snow_Alerts_Database::get_statistics();
        
        // Get recent articles
        $recent_articles = Snow_Alerts_Database::get_recent_articles(10);
        
        // Get recent logs
        $recent_logs = Snow_Alerts_Database::get_recent_logs(20);
        
        // Get configured APIs
        $configured_apis = Snow_Alerts_API_Manager::get_configured_apis();
        
        // Get next scheduled run
        $next_run = Snow_Alerts_Scheduler::get_next_run();
        
        // Get city count
        $city_count = Snow_Alerts_City_Manager::get_city_count();
        
        include SNOW_ALERTS_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * AJAX handler for manual article generation
     */
    public static function ajax_generate_now() {
        // Verify nonce
        check_ajax_referer('snow_alerts_admin_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'snow-alerts')));
        }
        
        // Get count from request
        $count = isset($_POST['count']) ? absint($_POST['count']) : 5;
        
        if ($count < 1 || $count > 20) {
            $count = 5;
        }
        
        // Generate articles
        $results = Snow_Alerts_Scheduler::manual_generate($count);
        
        if ($results['success'] > 0) {
            $message = sprintf(
                __('Successfully generated %d article(s). %d failed.', 'snow-alerts'),
                $results['success'],
                $results['failed']
            );
            
            wp_send_json_success(array(
                'message' => $message,
                'results' => $results
            ));
        } else {
            $error_message = !empty($results['errors']) ? implode(' ', $results['errors']) : __('Failed to generate articles', 'snow-alerts');
            
            wp_send_json_error(array(
                'message' => $error_message,
                'results' => $results
            ));
        }
    }
}

// Initialize dashboard
Snow_Alerts_Dashboard::init();
