<?php
/**
 * Plugin Name: Snow Alerts
 * Plugin URI: https://github.com/snowday25/snow
 * Description: Automated snow alert article generator with AI and template-based fallback system
 * Version: 1.0.0
 * Author: Snow Alerts Team
 * Author URI: https://github.com/snowday25
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: snow-alerts
 * Requires at least: 5.0
 * Requires PHP: 7.4
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

// Include required files
require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-api-manager.php';
require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-uniqueness-checker.php';
require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-template-manager.php';
require_once SNOW_ALERTS_PLUGIN_DIR . 'includes/class-content-generator.php';

/**
 * Plugin activation hook
 */
function snow_alerts_activate() {
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Log activation
    error_log('Snow Alerts Plugin activated successfully');
}
register_activation_hook(__FILE__, 'snow_alerts_activate');

/**
 * Plugin deactivation hook
 */
function snow_alerts_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Log deactivation
    error_log('Snow Alerts Plugin deactivated');
}
register_deactivation_hook(__FILE__, 'snow_alerts_deactivate');

/**
 * Initialize plugin
 */
function snow_alerts_init() {
    // Additional initialization code can go here
}
add_action('init', 'snow_alerts_init');
