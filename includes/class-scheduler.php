<?php
/**
 * Scheduler Class
 * 
 * Handles article publishing with comprehensive SEO integration.
 */

class Snow_Alerts_Scheduler {
    
    /**
     * Publish article with SEO enhancements
     */
    public static function publish_article($article_data, $location_data, $weather_data) {
        // Validate required data
        if (!isset($article_data['title']) || !isset($article_data['content'])) {
            error_log('Snow Alerts: Cannot publish article - missing title or content');
            return false;
        }
        
        // 1. Generate optimized headline
        $severity = self::determine_severity($weather_data);
        $optimized_headline = Snow_Alerts_Headline_Optimizer::optimize_headline(
            $article_data['title'],
            $location_data,
            $severity
        );
        
        // 2. Generate optimized meta description
        $optimized_meta = Snow_Alerts_Headline_Optimizer::create_meta_description(
            $location_data,
            $weather_data,
            'urgency'
        );
        
        // Prepare post data
        $post_data = array(
            'post_title' => $optimized_headline,
            'post_content' => $article_data['content'],
            'post_status' => 'publish',
            'post_type' => 'post',
            'post_author' => get_current_user_id() > 0 ? get_current_user_id() : 1,
        );
        
        // Add excerpt if provided, otherwise use optimized meta
        if (isset($article_data['excerpt']) && !empty($article_data['excerpt'])) {
            $post_data['post_excerpt'] = $article_data['excerpt'];
        } else {
            $post_data['post_excerpt'] = $optimized_meta;
        }
        
        // Insert post
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            error_log('Snow Alerts: Failed to publish article - ' . $post_id->get_error_message());
            return false;
        }
        
        // Mark as snow alert article
        update_post_meta($post_id, '_snow_alerts_article', true);
        
        // Store SEO meta description
        update_post_meta($post_id, '_snow_alerts_meta_description', $optimized_meta);
        
        // 3. Generate and set featured image
        Snow_Alerts_Image_Optimizer::generate_featured_image(
            $post_id,
            $location_data,
            $weather_data
        );
        
        // 4. Add breadcrumb schema
        $breadcrumb = Snow_Alerts_Advanced_SEO::generate_breadcrumb_schema(
            $post_id,
            $location_data
        );
        if ($breadcrumb) {
            update_post_meta($post_id, '_snow_alerts_breadcrumb_schema', wp_json_encode($breadcrumb));
        }
        
        // 5. Add FAQ schema for Q&A format
        if (isset($article_data['format']) && $article_data['format'] === 'qa') {
            $faq = Snow_Alerts_Advanced_SEO::add_faq_schema($post_id);
            if ($faq) {
                update_post_meta($post_id, '_snow_alerts_faq_schema', wp_json_encode($faq));
            }
        }
        
        // 6. Store geo coordinates
        if (isset($location_data['latitude'])) {
            update_post_meta($post_id, '_snow_alerts_latitude', $location_data['latitude']);
        }
        if (isset($location_data['longitude'])) {
            update_post_meta($post_id, '_snow_alerts_longitude', $location_data['longitude']);
        }
        
        // Store location information
        if (isset($location_data['city'])) {
            update_post_meta($post_id, '_snow_alerts_location_name', $location_data['city']);
        }
        if (isset($location_data['state'])) {
            update_post_meta($post_id, '_snow_alerts_state', $location_data['state']);
        }
        
        // 7. Generate and store enhanced article schema
        $article_schema = Snow_Alerts_Advanced_SEO::generate_enhanced_article_schema(
            $post_id,
            $location_data,
            $weather_data
        );
        if ($article_schema) {
            update_post_meta($post_id, '_snow_alerts_article_schema', wp_json_encode($article_schema));
        }
        
        // 8. Add weather category
        wp_set_post_terms($post_id, array('Weather', 'Snow Alert'), 'category');
        
        // 9. Add location tags
        $tags = array();
        if (isset($location_data['city'])) {
            $tags[] = $location_data['city'];
        }
        if (isset($location_data['state'])) {
            $tags[] = $location_data['state'];
        }
        if (isset($weather_data['event_type'])) {
            $tags[] = $weather_data['event_type'];
        }
        $tags[] = 'Winter Storm';
        $tags[] = 'Snow';
        
        wp_set_post_terms($post_id, $tags, 'post_tag');
        
        // 10. Trigger IndexNow submission
        do_action('snow_alerts_article_published', $post_id, $location_data);
        
        error_log('Snow Alerts: Article published successfully - Post ID: ' . $post_id);
        
        return $post_id;
    }
    
    /**
     * Update existing article
     */
    public static function update_article($post_id, $article_data, $location_data, $weather_data) {
        // Validate post exists
        $post = get_post($post_id);
        if (!$post) {
            error_log('Snow Alerts: Cannot update article - post not found: ' . $post_id);
            return false;
        }
        
        // Update post data
        $update_data = array(
            'ID' => $post_id,
        );
        
        if (isset($article_data['title'])) {
            $severity = self::determine_severity($weather_data);
            $optimized_headline = Snow_Alerts_Headline_Optimizer::optimize_headline(
                $article_data['title'],
                $location_data,
                $severity
            );
            $update_data['post_title'] = $optimized_headline;
        }
        
        if (isset($article_data['content'])) {
            $update_data['post_content'] = $article_data['content'];
        }
        
        // Update meta description
        $optimized_meta = Snow_Alerts_Headline_Optimizer::create_meta_description(
            $location_data,
            $weather_data,
            'urgency'
        );
        update_post_meta($post_id, '_snow_alerts_meta_description', $optimized_meta);
        
        // Update post
        $result = wp_update_post($update_data);
        
        if (is_wp_error($result)) {
            error_log('Snow Alerts: Failed to update article - ' . $result->get_error_message());
            return false;
        }
        
        // Update schemas
        $breadcrumb = Snow_Alerts_Advanced_SEO::generate_breadcrumb_schema($post_id, $location_data);
        if ($breadcrumb) {
            update_post_meta($post_id, '_snow_alerts_breadcrumb_schema', wp_json_encode($breadcrumb));
        }
        
        $article_schema = Snow_Alerts_Advanced_SEO::generate_enhanced_article_schema(
            $post_id,
            $location_data,
            $weather_data
        );
        if ($article_schema) {
            update_post_meta($post_id, '_snow_alerts_article_schema', wp_json_encode($article_schema));
        }
        
        // Trigger IndexNow submission for update
        do_action('snow_alerts_article_published', $post_id, $location_data);
        
        error_log('Snow Alerts: Article updated successfully - Post ID: ' . $post_id);
        
        return $post_id;
    }
    
    /**
     * Determine severity from weather data
     */
    private static function determine_severity($weather_data) {
        $snow_amount = self::extract_snow_amount($weather_data);
        
        if ($snow_amount >= 12) {
            return 'severe';
        }
        
        if ($snow_amount >= 6) {
            return 'moderate';
        }
        
        return 'light';
    }
    
    /**
     * Extract snow amount from weather data
     */
    private static function extract_snow_amount($weather_data) {
        // Try to get snow amount from data
        if (isset($weather_data['snow_amount'])) {
            $amount = $weather_data['snow_amount'];
            
            // Extract numeric value
            if (is_numeric($amount)) {
                return floatval($amount);
            }
            
            // Parse string like "6-12 inches"
            if (preg_match('/(\d+)/', $amount, $matches)) {
                return intval($matches[1]);
            }
        }
        
        // Default to moderate severity
        return 6;
    }
    
    /**
     * Schedule article for future publication
     */
    public static function schedule_article($article_data, $location_data, $weather_data, $publish_time) {
        // Validate timestamp
        if (!is_numeric($publish_time) || $publish_time <= time()) {
            error_log('Snow Alerts: Invalid publish time for scheduled article');
            return false;
        }
        
        // Create the article in draft status
        $article_data['status'] = 'future';
        
        // Schedule publication
        wp_schedule_single_event($publish_time, 'snow_alerts_publish_scheduled_article', array(
            'article_data' => $article_data,
            'location_data' => $location_data,
            'weather_data' => $weather_data,
        ));
        
        return true;
    }
    
    /**
     * Handle scheduled article publication
     */
    public static function handle_scheduled_publication($article_data, $location_data, $weather_data) {
        self::publish_article($article_data, $location_data, $weather_data);
    }
}

// Hook for scheduled publication
add_action('snow_alerts_publish_scheduled_article', array('Snow_Alerts_Scheduler', 'handle_scheduled_publication'), 10, 3);
