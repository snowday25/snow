<?php
/**
 * Plugin Name: Snow Alerts
 * Plugin URI: https://github.com/snowday25/snow
 * Description: Advanced snow alert system with comprehensive SEO features for fast indexing, high rankings, and Google Discover visibility
 * Version: 1.0.0
 * Author: Snow Alerts Team
 * Author URI: https://github.com/snowday25
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: snow-alerts
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('SNOW_ALERTS_VERSION', '1.0.0');
define('SNOW_ALERTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SNOW_ALERTS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SNOW_ALERTS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Require plugin classes
require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-advanced-seo.php';
require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-image-optimizer.php';
require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-headline-optimizer.php';
require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-scheduler.php';
require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-seo-optimizer.php';

/**
 * Initialize the plugin
 */
function snow_alerts_init() {
    // Initialize SEO features
    Snow_Alerts_Advanced_SEO::init();
    Snow_Alerts_SEO_Optimizer::init();
}
add_action('plugins_loaded', 'snow_alerts_init');

/**
 * Activation hook
 */
function snow_alerts_activate() {
    // Create IndexNow key file
    Snow_Alerts_Advanced_SEO::create_indexnow_key_file();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'snow_alerts_activate');

/**
 * Deactivation hook
 */
function snow_alerts_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'snow_alerts_deactivate');

/**
 * Add settings link on plugin page
 */
function snow_alerts_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=snow-alerts-settings">' . __('Settings', 'snow-alerts') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . SNOW_ALERTS_PLUGIN_BASENAME, 'snow_alerts_settings_link');

/**
 * Register settings page
 */
function snow_alerts_register_settings_page() {
    add_options_page(
        __('Snow Alerts Settings', 'snow-alerts'),
        __('Snow Alerts', 'snow-alerts'),
        'manage_options',
        'snow-alerts-settings',
        'snow_alerts_render_settings_page'
    );
}
add_action('admin_menu', 'snow_alerts_register_settings_page');

/**
 * Render settings page
 */
function snow_alerts_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    require_once SNOW_ALERTS_PLUGIN_DIR . 'admin/views/settings.php';
}

/**
 * Register plugin settings
 */
function snow_alerts_register_settings() {
    // API Keys
    register_setting('snow_alerts_settings', 'snow_alerts_unsplash_api_key');
    
    // SEO Settings
    register_setting('snow_alerts_settings', 'snow_alerts_enable_indexnow', array(
        'type' => 'boolean',
        'default' => true,
    ));
    register_setting('snow_alerts_settings', 'snow_alerts_author_name', array(
        'type' => 'string',
        'default' => 'Weather Team',
    ));
    register_setting('snow_alerts_settings', 'snow_alerts_facebook_url');
    register_setting('snow_alerts_settings', 'snow_alerts_twitter_url');
    register_setting('snow_alerts_settings', 'snow_alerts_instagram_url');
}
add_action('admin_init', 'snow_alerts_register_settings');
