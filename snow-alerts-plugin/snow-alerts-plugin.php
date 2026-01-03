<?php
/**
 * Plugin Name: Snow Alerts Plugin
 * Plugin URI: https://github.com/snowday25/snow
 * Description: Comprehensive WordPress plugin for automated snow alert content generation with AI, multi-API integration, and advanced SEO optimization.
 * Version: 2.0.0
 * Author: Snow Alerts Team
 * Author URI: https://github.com/snowday25
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: snow-alerts
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('SNOW_ALERTS_VERSION', '2.0.0');
define('SNOW_ALERTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SNOW_ALERTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SNOW_ALERTS_PLUGIN_FILE', __FILE__);
define('SNOW_ALERTS_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Snow_Alerts_Plugin {
    
    /**
     * @var Snow_Alerts_Plugin The single instance of the class
     */
    protected static $instance = null;
    
    /**
     * Main Instance
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
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
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-location-manager.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-weather-fetcher.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-template-manager.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-content-generator.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-uniqueness-checker-v2.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-content-variation-generator.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-headline-optimizer.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-image-optimizer.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-seo-optimizer.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-advanced-seo.php';
        require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-scheduler.php';
        
        // Admin classes
        if (is_admin()) {
            require_once SNOW_ALERTS_PLUGIN_DIR . 'admin/class-settings-page.php';
            require_once SNOW_ALERTS_PLUGIN_DIR . 'admin/class-dashboard.php';
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Admin initialization
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        }
        
        // Public assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        
        // Initialize scheduler
        add_action('init', array($this, 'init_scheduler'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $database = new Snow_Alerts_Database();
        $database->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Schedule cron events
        if (!wp_next_scheduled('snow_alerts_check_weather')) {
            wp_schedule_event(time(), 'hourly', 'snow_alerts_check_weather');
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('snow_alerts_check_weather');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $defaults = array(
            'auto_generation_enabled' => false,
            'check_interval' => 'hourly',
            'minimum_snow_amount' => 2,
            'author_name' => get_bloginfo('name'),
            'facebook_url' => '',
            'twitter_url' => '',
            'instagram_url' => '',
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option('snow_alerts_' . $key) === false) {
                add_option('snow_alerts_' . $key, $value);
            }
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Snow Alerts', 'snow-alerts'),
            __('Snow Alerts', 'snow-alerts'),
            'manage_options',
            'snow-alerts',
            array('Snow_Alerts_Dashboard', 'render'),
            'dashicons-cloud-saved',
            30
        );
        
        add_submenu_page(
            'snow-alerts',
            __('Dashboard', 'snow-alerts'),
            __('Dashboard', 'snow-alerts'),
            'manage_options',
            'snow-alerts',
            array('Snow_Alerts_Dashboard', 'render')
        );
        
        add_submenu_page(
            'snow-alerts',
            __('Settings', 'snow-alerts'),
            __('Settings', 'snow-alerts'),
            'manage_options',
            'snow-alerts-settings',
            array('Snow_Alerts_Settings_Page', 'render')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'snow-alerts') === false) {
            return;
        }
        
        wp_enqueue_style(
            'snow-alerts-admin',
            SNOW_ALERTS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SNOW_ALERTS_VERSION
        );
        
        wp_enqueue_script(
            'snow-alerts-admin',
            SNOW_ALERTS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            SNOW_ALERTS_VERSION,
            true
        );
        
        wp_localize_script('snow-alerts-admin', 'snowAlertsAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('snow_alerts_admin'),
        ));
    }
    
    /**
     * Enqueue public assets
     */
    public function enqueue_public_assets() {
        if (!is_singular('post')) {
            return;
        }
        
        wp_enqueue_style(
            'snow-alerts-public',
            SNOW_ALERTS_PLUGIN_URL . 'assets/css/public.css',
            array(),
            SNOW_ALERTS_VERSION
        );
        
        wp_enqueue_script(
            'snow-alerts-public',
            SNOW_ALERTS_PLUGIN_URL . 'assets/js/public.js',
            array('jquery'),
            SNOW_ALERTS_VERSION,
            true
        );
    }
    
    /**
     * Initialize scheduler
     */
    public function init_scheduler() {
        if (get_option('snow_alerts_auto_generation_enabled', false)) {
            $scheduler = new Snow_Alerts_Scheduler();
            add_action('snow_alerts_check_weather', array($scheduler, 'check_and_generate'));
        }
    }
}

/**
 * Initialize the plugin
 */
function snow_alerts_init() {
    return Snow_Alerts_Plugin::instance();
}

// Start the plugin
snow_alerts_init();
