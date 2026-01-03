<?php
/**
 * Plugin Name: Snow Alerts Plugin
 * Plugin URI: https://github.com/snowday25/snow
 * Description: Generates unique articles about snow forecasts and winter storm alerts in the United States using AI and multiple weather APIs.
 * Version: 1.0.0
 * Author: Snow Alerts Team
 * Author URI: https://github.com/snowday25
 * Text Domain: snow-alerts
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SNOW_ALERTS_VERSION', '1.0.0');
define('SNOW_ALERTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SNOW_ALERTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SNOW_ALERTS_PLUGIN_FILE', __FILE__);
define('SNOW_ALERTS_TEXT_DOMAIN', 'snow-alerts');

/**
 * Main Snow Alerts Plugin Class
 */
class Snow_Alerts_Plugin {
    
    /**
     * Single instance of the class
     *
     * @var Snow_Alerts_Plugin
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     *
     * @return Snow_Alerts_Plugin
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
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Core classes
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-database.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-api-manager.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-city-manager.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-weather-fetcher.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-content-generator.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-seo-optimizer.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-uniqueness-checker.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-scheduler.php';
        
        // Admin classes
        if (is_admin()) {
            require_once SNOW_ALERTS_PLUGIN_DIR . 'admin/class-settings-page.php';
            require_once SNOW_ALERTS_PLUGIN_DIR . 'admin/class-dashboard.php';
        }
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(SNOW_ALERTS_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(SNOW_ALERTS_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        Snow_Alerts_Database::create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Schedule cron jobs
        if (!wp_next_scheduled('snow_alerts_generate_articles')) {
            wp_schedule_event(time(), 'hourly', 'snow_alerts_generate_articles');
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled cron jobs
        $timestamp = wp_next_scheduled('snow_alerts_generate_articles');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'snow_alerts_generate_articles');
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $defaults = array(
            'snow_alerts_schedule_enabled' => 'no',
            'snow_alerts_schedule_interval' => 'hourly',
            'snow_alerts_articles_per_run' => 5,
            'snow_alerts_min_snowfall' => 2,
            'snow_alerts_word_count_min' => 800,
            'snow_alerts_word_count_max' => 1200,
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain(
            SNOW_ALERTS_TEXT_DOMAIN,
            false,
            dirname(plugin_basename(SNOW_ALERTS_PLUGIN_FILE)) . '/languages'
        );
        
        // Initialize scheduler
        Snow_Alerts_Scheduler::init();
    }
    
    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'snow-alerts') === false) {
            return;
        }
        
        // Admin CSS
        wp_enqueue_style(
            'snow-alerts-admin',
            SNOW_ALERTS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SNOW_ALERTS_VERSION
        );
        
        // Admin JS
        wp_enqueue_script(
            'snow-alerts-admin',
            SNOW_ALERTS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            SNOW_ALERTS_VERSION,
            true
        );
        
        // Localize script with AJAX URL and nonce
        wp_localize_script('snow-alerts-admin', 'snowAlertsAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('snow_alerts_admin_nonce'),
            'strings' => array(
                'generating' => __('Generating articles...', 'snow-alerts'),
                'success' => __('Articles generated successfully!', 'snow-alerts'),
                'error' => __('An error occurred. Please try again.', 'snow-alerts'),
            )
        ));
    }
    
    /**
     * Enqueue public scripts and styles
     */
    public function enqueue_public_assets() {
        // Public CSS
        wp_enqueue_style(
            'snow-alerts-public',
            SNOW_ALERTS_PLUGIN_URL . 'assets/css/public.css',
            array(),
            SNOW_ALERTS_VERSION
        );
        
        // Public JS
        wp_enqueue_script(
            'snow-alerts-public',
            SNOW_ALERTS_PLUGIN_URL . 'assets/js/public.js',
            array('jquery'),
            SNOW_ALERTS_VERSION,
            true
        );
    }
}

/**
 * Initialize the plugin
 */
function snow_alerts_init() {
    return Snow_Alerts_Plugin::get_instance();
}

// Start the plugin
snow_alerts_init();
