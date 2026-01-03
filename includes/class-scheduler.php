<?php
/**
 * Scheduler
 * 
 * Handles article generation with update detection and format variation
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Scheduler {
    
    /**
     * Uniqueness checker instance
     */
    private $uniqueness_checker;
    
    /**
     * Content variation generator instance
     */
    private $variation_generator;
    
    /**
     * Location manager instance
     */
    private $location_manager;
    
    /**
     * Maximum title generation attempts
     */
    const MAX_TITLE_ATTEMPTS = 5;
    
    /**
     * Update article formats (excluding standard)
     */
    private $update_formats = array(
        'update',
        'detailed_timeline',
        'qa_format',
        'listicle',
        'safety_focused'
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->uniqueness_checker = new Snow_Alerts_Uniqueness_Checker_V2();
        $this->variation_generator = new Snow_Alerts_Content_Variation_Generator();
        $this->location_manager = new Snow_Alerts_Location_Manager();
    }
    
    /**
     * Generate and publish article
     * Main entry point for article creation
     * 
     * @param array $location_data Location data
     * @param array $weather_data Weather data
     * @return int|false Post ID on success, false on failure
     */
    public function generate_and_publish_article($location_data, $weather_data) {
        if (!isset($location_data['name']) || !isset($location_data['state'])) {
            error_log('[Snow Alerts] Invalid location data');
            return false;
        }
        
        $location_name = $location_data['name'];
        $state = $location_data['state'];
        
        // Check if this is an update
        $update_info = $this->uniqueness_checker->check_for_weather_update(
            $location_name,
            $state,
            $weather_data
        );
        
        if ($update_info && isset($update_info['has_update']) && $update_info['has_update']) {
            // Generate UPDATE article in different format
            $this->log_event('Generating update article', $location_name, $update_info['update_type']);
            return $this->generate_update_article($location_data, $weather_data, $update_info);
        }
        
        // Generate NEW article
        $this->log_event('Generating new article', $location_name);
        return $this->generate_new_article($location_data, $weather_data);
    }
    
    /**
     * Generate update article
     * 
     * @param array $location_data Location data
     * @param array $weather_data Weather data
     * @param array $update_info Update information
     * @return int|false Post ID on success, false on failure
     */
    public function generate_update_article($location_data, $weather_data, $update_info) {
        // Select random format (not standard)
        $format = $this->update_formats[array_rand($this->update_formats)];
        
        $this->log_event('Selected update format', $location_data['name'], $format);
        
        // Generate content
        $previous_post_id = isset($update_info['previous_post_id']) ? $update_info['previous_post_id'] : null;
        
        $article_data = $this->variation_generator->generate_variation(
            $location_data,
            $weather_data,
            $format,
            $previous_post_id
        );
        
        if (!$article_data || !isset($article_data['title']) || !isset($article_data['content'])) {
            error_log('[Snow Alerts] Failed to generate update article content');
            return false;
        }
        
        // Check title uniqueness
        $title = $article_data['title'];
        $attempt = 0;
        
        while (!$this->uniqueness_checker->is_title_unique($title, $location_data['name']) && $attempt < self::MAX_TITLE_ATTEMPTS) {
            $attempt++;
            $title = $this->uniqueness_checker->generate_unique_title_variation(
                $article_data['title'],
                $attempt,
                $update_info
            );
            $this->log_event('Title retry', $location_data['name'], "Attempt $attempt");
        }
        
        if ($attempt >= self::MAX_TITLE_ATTEMPTS) {
            error_log('[Snow Alerts] Failed to generate unique title after max attempts');
            return false;
        }
        
        $article_data['title'] = $title;
        $article_data['is_update'] = true;
        $article_data['previous_post_id'] = $previous_post_id;
        $article_data['update_type'] = isset($update_info['update_type']) ? $update_info['update_type'] : 'general';
        
        return $this->publish_article($article_data, $location_data, $weather_data);
    }
    
    /**
     * Generate new article
     * 
     * @param array $location_data Location data
     * @param array $weather_data Weather data
     * @return int|false Post ID on success, false on failure
     */
    public function generate_new_article($location_data, $weather_data) {
        // Try AI generation first if API key available
        $article_data = $this->try_ai_generation($location_data, $weather_data);
        
        // Fallback to template-based generation
        if (!$article_data) {
            $article_data = $this->variation_generator->generate_variation(
                $location_data,
                $weather_data,
                'standard'
            );
        }
        
        if (!$article_data || !isset($article_data['title']) || !isset($article_data['content'])) {
            error_log('[Snow Alerts] Failed to generate new article content');
            return false;
        }
        
        // Retry title generation up to 5 times if not unique
        $title = $article_data['title'];
        $attempt = 0;
        
        while (!$this->uniqueness_checker->is_title_unique($title, $location_data['name']) && $attempt < self::MAX_TITLE_ATTEMPTS) {
            $attempt++;
            $title = $this->uniqueness_checker->generate_unique_title_variation(
                $article_data['title'],
                $attempt
            );
            $this->log_event('Title retry', $location_data['name'], "Attempt $attempt");
        }
        
        if ($attempt >= self::MAX_TITLE_ATTEMPTS) {
            error_log('[Snow Alerts] Failed to generate unique title after max attempts');
            return false;
        }
        
        $article_data['title'] = $title;
        $article_data['is_update'] = false;
        
        return $this->publish_article($article_data, $location_data, $weather_data);
    }
    
    /**
     * Publish article
     * Common publishing method for both new and update articles
     * 
     * @param array $article_data Article data
     * @param array $location_data Location data
     * @param array $weather_data Weather data
     * @return int|false Post ID on success, false on failure
     */
    public function publish_article($article_data, $location_data, $weather_data) {
        // Check content uniqueness
        if (!$this->uniqueness_checker->is_content_unique($article_data['content'], $location_data['name'])) {
            error_log('[Snow Alerts] Content not unique, aborting publish');
            return false;
        }
        
        // Create WordPress post
        $post_data = array(
            'post_title' => $article_data['title'],
            'post_content' => $article_data['content'],
            'post_status' => 'publish',
            'post_type' => 'post',
            'post_author' => 1
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id) || !$post_id) {
            error_log('[Snow Alerts] Failed to create WordPress post');
            return false;
        }
        
        // Add post meta
        $is_update = isset($article_data['is_update']) ? $article_data['is_update'] : false;
        update_post_meta($post_id, '_snow_alerts_is_update', $is_update);
        
        if ($is_update && isset($article_data['previous_post_id'])) {
            $previous_post_id = $article_data['previous_post_id'];
            
            // Link to previous article
            update_post_meta($post_id, '_snow_alerts_updates', $previous_post_id);
            
            // Bidirectional link
            $previous_updates = get_post_meta($previous_post_id, '_snow_alerts_updated_by', true);
            if (!is_array($previous_updates)) {
                $previous_updates = array();
            }
            $previous_updates[] = $post_id;
            update_post_meta($previous_post_id, '_snow_alerts_updated_by', $previous_updates);
        }
        
        // Store in custom table
        global $wpdb;
        $table_name = $wpdb->prefix . 'snow_alerts_articles';
        
        $title_hash = md5(strtolower(trim($article_data['title'])));
        $content_hash = md5(strtolower(strip_tags($article_data['content'])));
        
        $format = isset($article_data['format']) ? $article_data['format'] : 'standard';
        
        $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'location_name' => $location_data['name'],
                'state' => isset($location_data['state']) ? $location_data['state'] : '',
                'title_hash' => $title_hash,
                'content_hash' => $content_hash,
                'published_date' => current_time('mysql'),
                'weather_data' => json_encode($weather_data),
                'is_update' => $is_update ? 1 : 0,
                'previous_post_id' => isset($article_data['previous_post_id']) ? $article_data['previous_post_id'] : null,
                'format' => $format
            ),
            array(
                '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s'
            )
        );
        
        $this->log_event('Article published', $location_data['name'], "Post ID: $post_id, Format: $format");
        
        return $post_id;
    }
    
    /**
     * Try AI generation (placeholder for future AI integration)
     * 
     * @param array $location_data Location data
     * @param array $weather_data Weather data
     * @return array|false Article data or false
     */
    private function try_ai_generation($location_data, $weather_data) {
        // Check if API key is available
        $api_key = get_option('snow_alerts_ai_api_key');
        
        if (empty($api_key)) {
            return false;
        }
        
        // Placeholder for AI generation
        // In production, this would call an AI API
        
        return false;
    }
    
    /**
     * Log event
     * 
     * @param string $event Event description
     * @param string $location Location name
     * @param string $details Additional details
     */
    private function log_event($event, $location = '', $details = '') {
        if (function_exists('error_log')) {
            $message = "[Snow Alerts] $event";
            if ($location) {
                $message .= " | Location: $location";
            }
            if ($details) {
                $message .= " | Details: $details";
            }
            error_log($message);
        }
    }
}
