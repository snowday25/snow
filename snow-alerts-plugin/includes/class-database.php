<?php
/**
 * Database Manager
 * 
 * Handles database table creation and management
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Database {
    
    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Articles table
        $table_articles = $wpdb->prefix . 'snow_articles';
        $sql_articles = "CREATE TABLE $table_articles (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            location_name varchar(255) NOT NULL,
            location_state varchar(2) NOT NULL,
            location_type varchar(50) DEFAULT 'city',
            weather_data longtext,
            content_format varchar(50) DEFAULT 'standard',
            title_hash varchar(64) NOT NULL,
            content_hash varchar(64) NOT NULL,
            is_update tinyint(1) DEFAULT 0,
            parent_article_id bigint(20) unsigned DEFAULT NULL,
            snow_amount decimal(5,2) DEFAULT NULL,
            temperature decimal(5,2) DEFAULT NULL,
            wind_speed decimal(5,2) DEFAULT NULL,
            generated_at datetime NOT NULL,
            published_at datetime,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY location (location_name, location_state),
            KEY title_hash (title_hash),
            KEY content_hash (content_hash),
            KEY generated_at (generated_at),
            KEY parent_article_id (parent_article_id)
        ) $charset_collate;";
        
        dbDelta($sql_articles);
        
        // Cities/Locations table
        $table_cities = $wpdb->prefix . 'snow_cities';
        $sql_cities = "CREATE TABLE $table_cities (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            state varchar(2) NOT NULL,
            location_type varchar(50) DEFAULT 'city',
            latitude decimal(10,7) NOT NULL,
            longitude decimal(10,7) NOT NULL,
            population int(11) DEFAULT 0,
            elevation int(11) DEFAULT NULL,
            timezone varchar(50) DEFAULT NULL,
            last_checked datetime DEFAULT NULL,
            last_article_id bigint(20) unsigned DEFAULT NULL,
            article_count int(11) DEFAULT 0,
            is_active tinyint(1) DEFAULT 1,
            weight int(11) DEFAULT 1,
            created_at datetime NOT NULL,
            updated_at datetime,
            PRIMARY KEY  (id),
            UNIQUE KEY name_state (name, state),
            KEY location_type (location_type),
            KEY last_checked (last_checked),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        dbDelta($sql_cities);
        
        // API Logs table
        $table_logs = $wpdb->prefix . 'snow_api_logs';
        $sql_logs = "CREATE TABLE $table_logs (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            api_name varchar(50) NOT NULL,
            endpoint varchar(255),
            request_data longtext,
            response_data longtext,
            status_code int(11),
            success tinyint(1) DEFAULT 0,
            error_message text,
            execution_time decimal(10,4) DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY api_name (api_name),
            KEY created_at (created_at),
            KEY success (success)
        ) $charset_collate;";
        
        dbDelta($sql_logs);
        
        // Update version
        update_option('snow_alerts_db_version', SNOW_ALERTS_VERSION);
    }
    
    /**
     * Log API call
     */
    public function log_api_call($api_name, $endpoint, $request_data, $response_data, $status_code, $success, $error_message = '', $execution_time = 0) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_api_logs';
        
        $wpdb->insert(
            $table,
            array(
                'api_name' => $api_name,
                'endpoint' => $endpoint,
                'request_data' => is_array($request_data) ? json_encode($request_data) : $request_data,
                'response_data' => is_array($response_data) ? json_encode($response_data) : $response_data,
                'status_code' => $status_code,
                'success' => $success ? 1 : 0,
                'error_message' => $error_message,
                'execution_time' => $execution_time,
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%d', '%d', '%s', '%f', '%s')
        );
    }
    
    /**
     * Save article record
     */
    public function save_article($post_id, $location_name, $location_state, $location_type, $weather_data, $content_format, $title_hash, $content_hash, $is_update = false, $parent_article_id = null, $snow_amount = null, $temperature = null, $wind_speed = null) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_articles';
        
        $wpdb->insert(
            $table,
            array(
                'post_id' => $post_id,
                'location_name' => $location_name,
                'location_state' => $location_state,
                'location_type' => $location_type,
                'weather_data' => is_array($weather_data) ? json_encode($weather_data) : $weather_data,
                'content_format' => $content_format,
                'title_hash' => $title_hash,
                'content_hash' => $content_hash,
                'is_update' => $is_update ? 1 : 0,
                'parent_article_id' => $parent_article_id,
                'snow_amount' => $snow_amount,
                'temperature' => $temperature,
                'wind_speed' => $wind_speed,
                'generated_at' => current_time('mysql'),
                'published_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%f', '%f', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get recent articles
     */
    public function get_recent_articles($limit = 10) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_articles';
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table ORDER BY generated_at DESC LIMIT %d",
                $limit
            ),
            ARRAY_A
        );
        
        return $results ? $results : array();
    }
    
    /**
     * Get article by hash
     */
    public function get_article_by_hash($title_hash, $content_hash = null) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_articles';
        
        if ($content_hash) {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table WHERE title_hash = %s AND content_hash = %s LIMIT 1",
                    $title_hash,
                    $content_hash
                ),
                ARRAY_A
            );
        } else {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table WHERE title_hash = %s LIMIT 1",
                    $title_hash
                ),
                ARRAY_A
            );
        }
        
        return $results ? $results[0] : null;
    }
    
    /**
     * Update location last checked
     */
    public function update_location_last_checked($location_name, $location_state, $article_id = null) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_cities';
        
        $data = array(
            'last_checked' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        );
        
        if ($article_id) {
            $data['last_article_id'] = $article_id;
            $data['article_count'] = 'article_count + 1';
        }
        
        $wpdb->update(
            $table,
            $data,
            array(
                'name' => $location_name,
                'state' => $location_state,
            ),
            array('%s', '%s', '%d'),
            array('%s', '%s')
        );
    }
    
    /**
     * Get statistics
     */
    public function get_statistics() {
        global $wpdb;
        
        $table_articles = $wpdb->prefix . 'snow_articles';
        $table_cities = $wpdb->prefix . 'snow_cities';
        $table_logs = $wpdb->prefix . 'snow_api_logs';
        
        $stats = array();
        
        // Total articles
        $stats['total_articles'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_articles");
        
        // Articles today
        $stats['articles_today'] = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_articles WHERE DATE(generated_at) = CURDATE()"
        );
        
        // Total locations
        $stats['total_locations'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_cities WHERE is_active = 1");
        
        // API calls today
        $stats['api_calls_today'] = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_logs WHERE DATE(created_at) = CURDATE()"
        );
        
        // Success rate
        $total_calls = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_logs");
        $successful_calls = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_logs WHERE success = 1");
        $stats['success_rate'] = $total_calls > 0 ? round(($successful_calls / $total_calls) * 100, 2) : 0;
        
        return $stats;
    }
}
