<?php
/**
 * Plugin Name: Snow Alerts Pro
 * Plugin URI: https://snowday25.com
 * Description: Automated snow alerts and weather updates for ALL US snow-prone locations with advanced anti-duplication and content variation
 * Version: 2.0.0
 * Author: SnowDay25
 * Author URI: https://snowday25.com
 * License: GPL v2 or later
 * Text Domain: snow-alerts
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SNOW_ALERTS_VERSION', '2.0.0');
define('SNOW_ALERTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SNOW_ALERTS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Require core classes
require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-uniqueness-checker-v2.php';
require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-content-variation-generator.php';
require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-location-manager.php';
require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-scheduler.php';

/**
 * Main plugin class
 */
class Snow_Alerts_Plugin {
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('snow-alerts', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Activation hook
     */
    public function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'snow_alerts_articles';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            location_name varchar(255) NOT NULL,
            state varchar(2) NOT NULL,
            title_hash varchar(64) NOT NULL,
            content_hash varchar(64) NOT NULL,
            published_date datetime NOT NULL,
            weather_data text,
            is_update tinyint(1) DEFAULT 0,
            previous_post_id bigint(20) DEFAULT NULL,
            format varchar(50) DEFAULT 'standard',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY location_name (location_name),
            KEY state (state),
            KEY title_hash (title_hash),
            KEY content_hash (content_hash),
            KEY published_date (published_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Deactivation hook
     */
    public function deactivate() {
        // Clear scheduled events if any
        wp_clear_scheduled_hook('snow_alerts_check_weather');
    }
}

// Initialize plugin
Snow_Alerts_Plugin::get_instance();
