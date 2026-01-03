<?php
/**
 * Scheduler
 * 
 * Enhanced scheduler with update detection, format selection, and retry logic
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Scheduler {
    
    /**
     * @var Snow_Alerts_Location_Manager
     */
    private $location_manager;
    
    /**
     * @var Snow_Alerts_Weather_Fetcher
     */
    private $weather_fetcher;
    
    /**
     * @var Snow_Alerts_Content_Generator
     */
    private $content_generator;
    
    /**
     * @var Snow_Alerts_Content_Variation_Generator
     */
    private $variation_generator;
    
    /**
     * @var Snow_Alerts_Template_Manager
     */
    private $template_manager;
    
    /**
     * @var Snow_Alerts_Uniqueness_Checker_V2
     */
    private $uniqueness_checker;
    
    /**
     * @var Snow_Alerts_Headline_Optimizer
     */
    private $headline_optimizer;
    
    /**
     * @var Snow_Alerts_Image_Optimizer
     */
    private $image_optimizer;
    
    /**
     * @var Snow_Alerts_SEO_Optimizer
     */
    private $seo_optimizer;
    
    /**
     * @var Snow_Alerts_Advanced_SEO
     */
    private $advanced_seo;
    
    /**
     * @var Snow_Alerts_Database
     */
    private $database;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->location_manager = new Snow_Alerts_Location_Manager();
        $this->weather_fetcher = new Snow_Alerts_Weather_Fetcher();
        $this->content_generator = new Snow_Alerts_Content_Generator();
        $this->variation_generator = new Snow_Alerts_Content_Variation_Generator();
        $this->template_manager = new Snow_Alerts_Template_Manager();
        $this->uniqueness_checker = new Snow_Alerts_Uniqueness_Checker_V2();
        $this->headline_optimizer = new Snow_Alerts_Headline_Optimizer();
        $this->image_optimizer = new Snow_Alerts_Image_Optimizer();
        $this->seo_optimizer = new Snow_Alerts_SEO_Optimizer();
        $this->advanced_seo = new Snow_Alerts_Advanced_SEO();
        $this->database = new Snow_Alerts_Database();
    }
    
    /**
     * Check weather and generate articles
     */
    public function check_and_generate() {
        $min_snow = get_option('snow_alerts_minimum_snow_amount', 2);
        
        // Get random location
        $location = $this->location_manager->get_random_location();
        
        if (!$location) {
            return false;
        }
        
        // Fetch weather
        $weather_data = $this->weather_fetcher->fetch_weather($location);
        
        if (!isset($weather_data['success']) || !$weather_data['success']) {
            return false;
        }
        
        // Check if significant snow
        if (!$this->weather_fetcher->has_significant_snow($weather_data, $min_snow)) {
            return false;
        }
        
        $location_name = isset($location['name']) ? $location['name'] : '';
        $location_state = isset($location['state']) ? $location['state'] : '';
        $snow_amount = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        // Check for update vs new article
        $is_update = $this->uniqueness_checker->has_weather_changed($location_name, $location_state, $snow_amount, 24);
        
        // Get parent article if update
        $parent_article_id = null;
        if ($is_update) {
            $parent_article_id = $this->get_recent_article_id($location_name, $location_state);
        }
        
        // Select format
        $format = $is_update ? 'update' : $this->variation_generator->get_random_format();
        
        // Generate article with retry
        $result = $this->generate_article_with_retry($location, $weather_data, $format, $is_update, $parent_article_id);
        
        if ($result) {
            // Update location tracking
            $this->database->update_location_last_checked($location_name, $location_state, $result);
        }
        
        return $result;
    }
    
    /**
     * Generate article with retry logic (up to 5 attempts)
     */
    private function generate_article_with_retry($location, $weather_data, $format, $is_update, $parent_article_id, $max_retries = 5) {
        $location_name = isset($location['name']) ? $location['name'] : '';
        $location_state = isset($location['state']) ? $location['state'] : '';
        $snow_amount = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        for ($attempt = 1; $attempt <= $max_retries; $attempt++) {
            // Detect storm type
            $storm_type = $this->template_manager->detect_storm_type($weather_data, $location);
            
            // Generate title
            $title = $this->template_manager->generate_title($location_name, $storm_type, $snow_amount);
            
            // Optimize title
            $title = $this->headline_optimizer->optimize($title, $location_name, $snow_amount);
            
            // Generate content
            $content_result = $this->variation_generator->generate($location, $weather_data, $format, $is_update, $parent_article_id);
            $content = isset($content_result) ? $content_result : '';
            
            // Check uniqueness
            $uniqueness = $this->uniqueness_checker->is_unique($title, $content, $location_name, $location_state);
            
            if (isset($uniqueness['unique']) && $uniqueness['unique']) {
                // Create post
                $post_id = $this->create_post($title, $content, $location, $weather_data, $format, $is_update, $parent_article_id, $uniqueness);
                
                if ($post_id) {
                    return $post_id;
                }
            }
            
            // Try different format for next attempt
            if ($attempt < $max_retries) {
                $format = $this->variation_generator->get_random_format(array($format));
            }
        }
        
        return false;
    }
    
    /**
     * Create WordPress post
     */
    private function create_post($title, $content, $location, $weather_data, $format, $is_update, $parent_article_id, $uniqueness) {
        $location_name = isset($location['name']) ? $location['name'] : '';
        $location_state = isset($location['state']) ? $location['state'] : '';
        $location_type = isset($location['location_type']) ? $location['location_type'] : 'city';
        $snow_amount = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        $temperature = isset($weather_data['current']) && isset($weather_data['current']['temperature']) ? $weather_data['current']['temperature'] : null;
        $wind_speed = isset($weather_data['current']) && isset($weather_data['current']['wind_speed']) ? $weather_data['current']['wind_speed'] : null;
        
        // Create post
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'post',
            'post_category' => array(get_option('default_category')),
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id) || !$post_id) {
            return false;
        }
        
        // Generate meta description
        $meta_desc = $this->headline_optimizer->generate_meta_description($location_name, $snow_amount, 'informative');
        
        // SEO optimization
        $this->seo_optimizer->optimize_post($post_id, $location, $weather_data, $meta_desc);
        $this->advanced_seo->optimize_post($post_id, $location_name, $snow_amount);
        
        // Set featured image
        $storm_type = $this->template_manager->detect_storm_type($weather_data, $location);
        $this->image_optimizer->set_featured_image($post_id, $location_name, $storm_type);
        
        // Save to database
        $title_hash = isset($uniqueness['title_hash']) ? $uniqueness['title_hash'] : '';
        $content_hash = isset($uniqueness['content_hash']) ? $uniqueness['content_hash'] : '';
        
        $article_id = $this->database->save_article(
            $post_id,
            $location_name,
            $location_state,
            $location_type,
            $weather_data,
            $format,
            $title_hash,
            $content_hash,
            $is_update,
            $parent_article_id,
            $snow_amount,
            $temperature,
            $wind_speed
        );
        
        // Bidirectional linking
        if ($is_update && $parent_article_id) {
            $this->add_bidirectional_links($post_id, $parent_article_id);
        }
        
        return $post_id;
    }
    
    /**
     * Get recent article ID for location
     */
    private function get_recent_article_id($location_name, $location_state) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_articles';
        
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM $table 
                 WHERE location_name = %s 
                 AND location_state = %s 
                 AND generated_at > DATE_SUB(NOW(), INTERVAL 48 HOUR)
                 ORDER BY generated_at DESC
                 LIMIT 1",
                $location_name,
                $location_state
            )
        );
        
        return $result ? intval($result) : null;
    }
    
    /**
     * Add bidirectional links between articles
     */
    private function add_bidirectional_links($new_post_id, $old_post_id) {
        // Add link to old article in new article
        $old_post = get_post($old_post_id);
        if ($old_post) {
            $old_link = '<p><em>Read our previous coverage: <a href="' . get_permalink($old_post_id) . '">' . get_the_title($old_post_id) . '</a></em></p>';
            
            $new_content = get_post_field('post_content', $new_post_id);
            $new_content = $old_link . "\n\n" . $new_content;
            
            wp_update_post(array(
                'ID' => $new_post_id,
                'post_content' => $new_content,
            ));
        }
        
        // Add link to new article in old article
        $new_link = '<p><em><strong>UPDATE:</strong> See our latest coverage: <a href="' . get_permalink($new_post_id) . '">' . get_the_title($new_post_id) . '</a></em></p>';
        
        $old_content = get_post_field('post_content', $old_post_id);
        $old_content = $new_link . "\n\n" . $old_content;
        
        wp_update_post(array(
            'ID' => $old_post_id,
            'post_content' => $old_content,
        ));
    }
    
    /**
     * Manual generation (for admin)
     */
    public function generate_manual() {
        return $this->check_and_generate();
    }
}
