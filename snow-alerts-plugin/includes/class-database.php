<?php
/**
 * Database Management Class
 *
 * Handles database table creation and management for Snow Alerts Plugin
 *
 * @package Snow_Alerts
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Database {
    
    /**
     * Create plugin database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table for generated articles tracking
        $table_articles = $wpdb->prefix . 'snow_alerts_articles';
        
        $sql_articles = "CREATE TABLE IF NOT EXISTS {$table_articles} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id bigint(20) UNSIGNED NOT NULL,
            city varchar(100) NOT NULL,
            state varchar(2) NOT NULL,
            title_hash varchar(64) NOT NULL,
            content_hash varchar(64) NOT NULL,
            alert_start datetime DEFAULT NULL,
            alert_end datetime DEFAULT NULL,
            snowfall_start datetime DEFAULT NULL,
            snowfall_end datetime DEFAULT NULL,
            snowfall_amount decimal(5,2) DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY city_state (city, state),
            KEY title_hash (title_hash),
            KEY content_hash (content_hash),
            KEY created_at (created_at)
        ) {$charset_collate};";
        
        // Table for API activity logs
        $table_logs = $wpdb->prefix . 'snow_alerts_logs';
        
        $sql_logs = "CREATE TABLE IF NOT EXISTS {$table_logs} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            api_name varchar(50) NOT NULL,
            endpoint varchar(255) NOT NULL,
            request_data text DEFAULT NULL,
            response_code int(11) DEFAULT NULL,
            response_message text DEFAULT NULL,
            success tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY api_name (api_name),
            KEY success (success),
            KEY created_at (created_at)
        ) {$charset_collate};";
        
        // Table for city weather cache
        $table_cache = $wpdb->prefix . 'snow_alerts_weather_cache';
        
        $sql_cache = "CREATE TABLE IF NOT EXISTS {$table_cache} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            city varchar(100) NOT NULL,
            state varchar(2) NOT NULL,
            weather_data longtext NOT NULL,
            cached_at datetime NOT NULL,
            expires_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY city_state (city, state),
            KEY expires_at (expires_at)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_articles);
        dbDelta($sql_logs);
        dbDelta($sql_cache);
    }
    
    /**
     * Drop plugin database tables (used for complete uninstall)
     */
    public static function drop_tables() {
        global $wpdb;
        
        $table_articles = $wpdb->prefix . 'snow_alerts_articles';
        $table_logs = $wpdb->prefix . 'snow_alerts_logs';
        $table_cache = $wpdb->prefix . 'snow_alerts_weather_cache';
        
        $wpdb->query("DROP TABLE IF EXISTS {$table_articles}");
        $wpdb->query("DROP TABLE IF EXISTS {$table_logs}");
        $wpdb->query("DROP TABLE IF EXISTS {$table_cache}");
    }
    
    /**
     * Log article generation
     *
     * @param int $post_id WordPress post ID
     * @param string $city City name
     * @param string $state State abbreviation
     * @param string $title Article title
     * @param string $content Article content
     * @param array $data Additional data (alert dates, snowfall info)
     * @return int|false Insert ID or false on failure
     */
    public static function log_article($post_id, $city, $state, $title, $content, $data = array()) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_alerts_articles';
        
        $insert_data = array(
            'post_id' => absint($post_id),
            'city' => sanitize_text_field($city),
            'state' => sanitize_text_field($state),
            'title_hash' => hash('sha256', $title),
            'content_hash' => hash('sha256', $content),
            'created_at' => current_time('mysql')
        );
        
        // Add optional data
        if (isset($data['alert_start']) && !empty($data['alert_start'])) {
            $insert_data['alert_start'] = sanitize_text_field($data['alert_start']);
        }
        
        if (isset($data['alert_end']) && !empty($data['alert_end'])) {
            $insert_data['alert_end'] = sanitize_text_field($data['alert_end']);
        }
        
        if (isset($data['snowfall_start']) && !empty($data['snowfall_start'])) {
            $insert_data['snowfall_start'] = sanitize_text_field($data['snowfall_start']);
        }
        
        if (isset($data['snowfall_end']) && !empty($data['snowfall_end'])) {
            $insert_data['snowfall_end'] = sanitize_text_field($data['snowfall_end']);
        }
        
        if (isset($data['snowfall_amount']) && !empty($data['snowfall_amount'])) {
            $insert_data['snowfall_amount'] = floatval($data['snowfall_amount']);
        }
        
        $result = $wpdb->insert($table, $insert_data);
        
        if ($result === false) {
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Log API activity
     *
     * @param string $api_name API name
     * @param string $endpoint API endpoint
     * @param mixed $request_data Request data
     * @param int $response_code HTTP response code
     * @param string $response_message Response message
     * @param bool $success Success status
     * @return int|false Insert ID or false on failure
     */
    public static function log_api_activity($api_name, $endpoint, $request_data = null, $response_code = null, $response_message = '', $success = false) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_alerts_logs';
        
        $insert_data = array(
            'api_name' => sanitize_text_field($api_name),
            'endpoint' => sanitize_text_field($endpoint),
            'request_data' => is_array($request_data) ? wp_json_encode($request_data) : sanitize_text_field($request_data),
            'response_code' => $response_code ? absint($response_code) : null,
            'response_message' => sanitize_text_field($response_message),
            'success' => $success ? 1 : 0,
            'created_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($table, $insert_data);
        
        if ($result === false) {
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get cached weather data for a city
     *
     * @param string $city City name
     * @param string $state State abbreviation
     * @return array|null Weather data or null if not found/expired
     */
    public static function get_cached_weather($city, $state) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_alerts_weather_cache';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT weather_data, expires_at FROM {$table} WHERE city = %s AND state = %s",
            $city,
            $state
        ));
        
        if (!$result) {
            return null;
        }
        
        // Check if cache is expired
        if (strtotime($result->expires_at) < time()) {
            // Delete expired cache
            $wpdb->delete($table, array('city' => $city, 'state' => $state));
            return null;
        }
        
        return json_decode($result->weather_data, true);
    }
    
    /**
     * Cache weather data for a city
     *
     * @param string $city City name
     * @param string $state State abbreviation
     * @param array $weather_data Weather data to cache
     * @param int $ttl Time to live in seconds (default: 3600 = 1 hour)
     * @return bool Success status
     */
    public static function cache_weather($city, $state, $weather_data, $ttl = 3600) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_alerts_weather_cache';
        
        $insert_data = array(
            'city' => sanitize_text_field($city),
            'state' => sanitize_text_field($state),
            'weather_data' => wp_json_encode($weather_data),
            'cached_at' => current_time('mysql'),
            'expires_at' => date('Y-m-d H:i:s', time() + $ttl)
        );
        
        // Delete existing cache for this city
        $wpdb->delete($table, array('city' => $city, 'state' => $state));
        
        $result = $wpdb->insert($table, $insert_data);
        
        return $result !== false;
    }
    
    /**
     * Get recent articles
     *
     * @param int $limit Number of articles to retrieve
     * @return array Array of article records
     */
    public static function get_recent_articles($limit = 10) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_alerts_articles';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d",
            $limit
        ));
        
        return $results ? $results : array();
    }
    
    /**
     * Get API activity logs
     *
     * @param int $limit Number of logs to retrieve
     * @return array Array of log records
     */
    public static function get_recent_logs($limit = 50) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_alerts_logs';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d",
            $limit
        ));
        
        return $results ? $results : array();
    }
    
    /**
     * Get statistics
     *
     * @return array Statistics array
     */
    public static function get_statistics() {
        global $wpdb;
        
        $table_articles = $wpdb->prefix . 'snow_alerts_articles';
        $table_logs = $wpdb->prefix . 'snow_alerts_logs';
        
        $stats = array();
        
        // Total articles generated
        $stats['total_articles'] = $wpdb->get_var("SELECT COUNT(*) FROM {$table_articles}");
        
        // Articles today
        $stats['articles_today'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_articles} WHERE DATE(created_at) = %s",
            current_time('Y-m-d')
        ));
        
        // Articles this week
        $stats['articles_week'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_articles} WHERE created_at >= %s",
            date('Y-m-d H:i:s', strtotime('-7 days'))
        ));
        
        // Total API calls
        $stats['total_api_calls'] = $wpdb->get_var("SELECT COUNT(*) FROM {$table_logs}");
        
        // Successful API calls
        $stats['successful_api_calls'] = $wpdb->get_var("SELECT COUNT(*) FROM {$table_logs} WHERE success = 1");
        
        // Failed API calls
        $stats['failed_api_calls'] = $wpdb->get_var("SELECT COUNT(*) FROM {$table_logs} WHERE success = 0");
        
        return $stats;
    }
}
