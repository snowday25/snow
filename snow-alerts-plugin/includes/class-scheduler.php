<?php
/**
 * Scheduler Class
 *
 * Handles automated article generation scheduling
 * FIXED: NO Elvis operators - uses proper ternary syntax
 *
 * @package Snow_Alerts
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Scheduler {
    
    /**
     * Initialize scheduler hooks
     */
    public static function init() {
        add_action('snow_alerts_generate_articles', array(__CLASS__, 'generate_scheduled_articles'));
        add_filter('cron_schedules', array(__CLASS__, 'add_custom_schedules'));
    }
    
    /**
     * Add custom cron schedules
     *
     * @param array $schedules Existing schedules
     * @return array Modified schedules
     */
    public static function add_custom_schedules($schedules) {
        $schedules['every_30_minutes'] = array(
            'interval' => 1800,
            'display' => __('Every 30 Minutes', 'snow-alerts')
        );
        
        $schedules['every_2_hours'] = array(
            'interval' => 7200,
            'display' => __('Every 2 Hours', 'snow-alerts')
        );
        
        $schedules['every_6_hours'] = array(
            'interval' => 21600,
            'display' => __('Every 6 Hours', 'snow-alerts')
        );
        
        return $schedules;
    }
    
    /**
     * Generate articles on schedule
     */
    public static function generate_scheduled_articles() {
        // Check if scheduling is enabled
        $enabled = get_option('snow_alerts_schedule_enabled', 'no');
        
        if ($enabled !== 'yes') {
            return;
        }
        
        // Get number of articles to generate
        $articles_per_run = get_option('snow_alerts_articles_per_run', 5);
        $articles_per_run = absint($articles_per_run);
        
        if ($articles_per_run < 1) {
            $articles_per_run = 5;
        }
        
        // Generate articles
        self::generate_articles($articles_per_run);
    }
    
    /**
     * Generate multiple articles
     *
     * @param int $count Number of articles to generate
     * @return array Results array with success/error info
     */
    public static function generate_articles($count = 5) {
        $results = array(
            'success' => 0,
            'failed' => 0,
            'errors' => array()
        );
        
        // Get cities that need articles
        $cities = Snow_Alerts_City_Manager::get_cities_needing_articles(7, $count * 2);
        
        if (empty($cities)) {
            $results['errors'][] = __('No cities available for article generation', 'snow-alerts');
            return $results;
        }
        
        $min_snowfall = get_option('snow_alerts_min_snowfall', 2);
        $min_snowfall = floatval($min_snowfall);
        
        $generated = 0;
        
        foreach ($cities as $city) {
            if ($generated >= $count) {
                break;
            }
            
            // Get weather data for city
            $weather_data = Snow_Alerts_Weather_Fetcher::get_weather_data($city);
            
            if (is_wp_error($weather_data)) {
                $results['failed']++;
                $city_name = isset($city['city']) ? $city['city'] : 'Unknown';
                $results['errors'][] = sprintf(
                    __('Failed to get weather for %s: %s', 'snow-alerts'),
                    $city_name,
                    $weather_data->get_error_message()
                );
                continue;
            }
            
            // Check if city has significant snow
            if (!Snow_Alerts_Weather_Fetcher::has_significant_snow($weather_data, $min_snowfall)) {
                continue; // Skip cities without significant snow
            }
            
            // Check for recent articles
            $city_name = isset($city['city']) ? $city['city'] : '';
            $state = isset($city['state']) ? $city['state'] : '';
            
            if (Snow_Alerts_Uniqueness_Checker::has_recent_article($city_name, $state, 24)) {
                continue; // Skip if recent article exists
            }
            
            // Generate article
            $post_id = Snow_Alerts_Content_Generator::generate_article($city, $weather_data);
            
            if (is_wp_error($post_id)) {
                $results['failed']++;
                $results['errors'][] = sprintf(
                    __('Failed to generate article for %s: %s', 'snow-alerts'),
                    $city_name,
                    $post_id->get_error_message()
                );
            } else {
                $results['success']++;
                $generated++;
            }
            
            // Add delay between API calls to avoid rate limiting
            sleep(2);
        }
        
        return $results;
    }
    
    /**
     * Update cron schedule
     *
     * @param string $interval Schedule interval
     */
    public static function update_schedule($interval) {
        // Clear existing schedule
        $timestamp = wp_next_scheduled('snow_alerts_generate_articles');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'snow_alerts_generate_articles');
        }
        
        // Schedule new event
        if ($interval && $interval !== 'disabled') {
            wp_schedule_event(time(), $interval, 'snow_alerts_generate_articles');
        }
    }
    
    /**
     * Get next scheduled run time
     *
     * @return int|false Timestamp or false if not scheduled
     */
    public static function get_next_run() {
        return wp_next_scheduled('snow_alerts_generate_articles');
    }
    
    /**
     * Manually trigger article generation (for admin button)
     *
     * @param int $count Number of articles to generate
     * @return array Results
     */
    public static function manual_generate($count = 5) {
        return self::generate_articles($count);
    }
}

// Initialize scheduler
Snow_Alerts_Scheduler::init();
