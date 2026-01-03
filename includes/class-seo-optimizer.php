<?php
/**
 * SEO Optimizer Class
 * 
 * Handles schema markup output and meta tag optimization.
 */

class Snow_Alerts_SEO_Optimizer {
    
    /**
     * Initialize the class
     */
    public static function init() {
        // Output schema markup in head
        add_action('wp_head', array(__CLASS__, 'output_schema_markup'), 5);
        
        // Add meta description
        add_action('wp_head', array(__CLASS__, 'output_meta_description'), 2);
        
        // Modify title tag
        add_filter('wp_title', array(__CLASS__, 'modify_title_tag'), 10, 2);
        add_filter('pre_get_document_title', array(__CLASS__, 'modify_document_title'));
    }
    
    /**
     * Output all schema markup types
     */
    public static function output_schema_markup() {
        if (!is_single()) {
            return;
        }
        
        global $post;
        
        // Check if this is a snow alert post
        $is_snow_alert = get_post_meta($post->ID, '_snow_alerts_article', true);
        if (!$is_snow_alert) {
            return;
        }
        
        // Article schema (existing)
        $article_schema = get_post_meta($post->ID, '_snow_alerts_article_schema', true);
        
        // Forecast schema (if exists)
        $forecast_schema = get_post_meta($post->ID, '_snow_alerts_forecast_schema', true);
        
        // Breadcrumb schema (NEW)
        $breadcrumb_schema = get_post_meta($post->ID, '_snow_alerts_breadcrumb_schema', true);
        
        // FAQ schema (NEW)
        $faq_schema = get_post_meta($post->ID, '_snow_alerts_faq_schema', true);
        
        // Output all schemas (JSON is already encoded, but validate it's valid JSON)
        if ($article_schema) {
            // Validate JSON before output
            $decoded = json_decode($article_schema);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo '<script type="application/ld+json">' . $article_schema . '</script>' . "\n";
            }
        }
        
        if ($forecast_schema) {
            $decoded = json_decode($forecast_schema);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo '<script type="application/ld+json">' . $forecast_schema . '</script>' . "\n";
            }
        }
        
        if ($breadcrumb_schema) {
            $decoded = json_decode($breadcrumb_schema);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo '<script type="application/ld+json">' . $breadcrumb_schema . '</script>' . "\n";
            }
        }
        
        if ($faq_schema) {
            $decoded = json_decode($faq_schema);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo '<script type="application/ld+json">' . $faq_schema . '</script>' . "\n";
            }
        }
    }
    
    /**
     * Output meta description
     */
    public static function output_meta_description() {
        if (!is_single()) {
            return;
        }
        
        global $post;
        
        // Get custom meta description
        $meta_description = get_post_meta($post->ID, '_snow_alerts_meta_description', true);
        
        if (empty($meta_description)) {
            // Fall back to excerpt
            $meta_description = get_the_excerpt($post);
        }
        
        if (!empty($meta_description)) {
            echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
            
            // Also output Open Graph description
            echo '<meta property="og:description" content="' . esc_attr($meta_description) . '">' . "\n";
            
            // Twitter card description
            echo '<meta name="twitter:description" content="' . esc_attr($meta_description) . '">' . "\n";
        }
    }
    
    /**
     * Modify title tag (legacy)
     */
    public static function modify_title_tag($title, $sep) {
        if (!is_single()) {
            return $title;
        }
        
        global $post;
        
        $is_snow_alert = get_post_meta($post->ID, '_snow_alerts_article', true);
        if (!$is_snow_alert) {
            return $title;
        }
        
        // Ensure title is optimized
        return get_the_title($post) . ' ' . $sep . ' ' . get_bloginfo('name');
    }
    
    /**
     * Modify document title (modern)
     */
    public static function modify_document_title($title) {
        if (!is_single()) {
            return $title;
        }
        
        global $post;
        
        $is_snow_alert = get_post_meta($post->ID, '_snow_alerts_article', true);
        if (!$is_snow_alert) {
            return $title;
        }
        
        // Return optimized title
        return get_the_title($post) . ' - ' . get_bloginfo('name');
    }
    
    /**
     * Generate forecast schema (for weather data)
     */
    public static function generate_forecast_schema($post_id, $forecast_data) {
        $post = get_post($post_id);
        if (!$post) {
            return null;
        }
        
        $location_lat = get_post_meta($post_id, '_snow_alerts_latitude', true);
        $location_lon = get_post_meta($post_id, '_snow_alerts_longitude', true);
        
        if (empty($location_lat) || empty($location_lon)) {
            return null;
        }
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'WeatherForecast',
            'spatialCoverage' => array(
                '@type' => 'Place',
                'geo' => array(
                    '@type' => 'GeoCoordinates',
                    'latitude' => $location_lat,
                    'longitude' => $location_lon,
                ),
            ),
        );
        
        // Add forecast period if available
        if (isset($forecast_data['valid_from'])) {
            $schema['validFrom'] = $forecast_data['valid_from'];
        }
        
        if (isset($forecast_data['valid_through'])) {
            $schema['validThrough'] = $forecast_data['valid_through'];
        }
        
        // Add forecast details
        if (isset($forecast_data['temperature'])) {
            $schema['temperature'] = array(
                '@type' => 'QuantitativeValue',
                'value' => $forecast_data['temperature'],
                'unitCode' => 'FAH',
            );
        }
        
        if (isset($forecast_data['precipitation'])) {
            $schema['precipitation'] = array(
                '@type' => 'QuantitativeValue',
                'value' => $forecast_data['precipitation'],
                'unitCode' => 'INH',
            );
        }
        
        return $schema;
    }
    
    /**
     * Add Open Graph tags
     */
    public static function add_open_graph_tags() {
        if (!is_single()) {
            return;
        }
        
        global $post;
        
        $is_snow_alert = get_post_meta($post->ID, '_snow_alerts_article', true);
        if (!$is_snow_alert) {
            return;
        }
        
        // OG title
        echo '<meta property="og:title" content="' . esc_attr(get_the_title($post)) . '">' . "\n";
        
        // OG type
        echo '<meta property="og:type" content="article">' . "\n";
        
        // OG URL
        echo '<meta property="og:url" content="' . esc_url(get_permalink($post)) . '">' . "\n";
        
        // OG image
        $image_url = get_the_post_thumbnail_url($post->ID, 'full');
        if ($image_url) {
            echo '<meta property="og:image" content="' . esc_url($image_url) . '">' . "\n";
            echo '<meta property="og:image:width" content="1200">' . "\n";
            echo '<meta property="og:image:height" content="630">' . "\n";
        }
        
        // OG site name
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        
        // Twitter card
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr(get_the_title($post)) . '">' . "\n";
        
        if ($image_url) {
            echo '<meta name="twitter:image" content="' . esc_url($image_url) . '">' . "\n";
        }
    }
    
    /**
     * Get schema for validation
     */
    public static function get_all_schemas($post_id) {
        $schemas = array();
        
        $article_schema = get_post_meta($post_id, '_snow_alerts_article_schema', true);
        if ($article_schema) {
            $schemas['article'] = json_decode($article_schema, true);
        }
        
        $forecast_schema = get_post_meta($post_id, '_snow_alerts_forecast_schema', true);
        if ($forecast_schema) {
            $schemas['forecast'] = json_decode($forecast_schema, true);
        }
        
        $breadcrumb_schema = get_post_meta($post_id, '_snow_alerts_breadcrumb_schema', true);
        if ($breadcrumb_schema) {
            $schemas['breadcrumb'] = json_decode($breadcrumb_schema, true);
        }
        
        $faq_schema = get_post_meta($post_id, '_snow_alerts_faq_schema', true);
        if ($faq_schema) {
            $schemas['faq'] = json_decode($faq_schema, true);
        }
        
        return $schemas;
    }
}
