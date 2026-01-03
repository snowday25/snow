<?php
/**
 * SEO Optimizer
 * 
 * Enhanced SEO with 4 Schema types (NewsArticle, Breadcrumb, FAQ, WeatherForecast)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_SEO_Optimizer {
    
    /**
     * Add SEO meta tags and schemas to post
     */
    public function optimize_post($post_id, $location, $weather_data, $meta_description = '') {
        if (!$post_id) {
            return;
        }
        
        $post = get_post($post_id);
        
        if (!$post) {
            return;
        }
        
        $location_name = isset($location['name']) ? $location['name'] : '';
        $location_state = isset($location['state']) ? $location['state'] : '';
        $snow_amount = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        // Meta description
        if (!empty($meta_description)) {
            update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_description);
        }
        
        // Focus keyword
        $keyword = $location_name . ' snow forecast';
        update_post_meta($post_id, '_yoast_wpseo_focuskw', $keyword);
        
        // Open Graph
        update_post_meta($post_id, '_yoast_wpseo_opengraph-title', $post->post_title);
        update_post_meta($post_id, '_yoast_wpseo_opengraph-description', $meta_description);
        
        // Twitter Card
        update_post_meta($post_id, '_yoast_wpseo_twitter-title', $post->post_title);
        update_post_meta($post_id, '_yoast_wpseo_twitter-description', $meta_description);
        
        // Add schemas
        add_action('wp_head', function() use ($post_id, $post, $location, $weather_data) {
            $this->output_schemas($post_id, $post, $location, $weather_data);
        });
    }
    
    /**
     * Output all schemas
     */
    public function output_schemas($post_id, $post, $location, $weather_data) {
        echo $this->generate_news_article_schema($post_id, $post, $location, $weather_data);
        echo $this->generate_breadcrumb_schema($post_id, $post, $location);
        echo $this->generate_faq_schema($post_id, $location, $weather_data);
        echo $this->generate_weather_forecast_schema($location, $weather_data);
    }
    
    /**
     * Generate NewsArticle schema
     */
    public function generate_news_article_schema($post_id, $post, $location, $weather_data) {
        $author_name = get_option('snow_alerts_author_name', get_bloginfo('name'));
        $location_name = isset($location['name']) ? $location['name'] : '';
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => get_the_title($post_id),
            'description' => get_post_meta($post_id, '_yoast_wpseo_metadesc', true),
            'datePublished' => get_the_date('c', $post_id),
            'dateModified' => get_the_modified_date('c', $post_id),
            'author' => array(
                '@type' => 'Person',
                'name' => $author_name,
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => get_site_icon_url(),
                ),
            ),
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id' => get_permalink($post_id),
            ),
            'articleSection' => 'Weather',
            'keywords' => $location_name . ', snow, winter storm, weather forecast',
        );
        
        $thumbnail_url = get_the_post_thumbnail_url($post_id, 'full');
        if ($thumbnail_url) {
            $schema['image'] = $thumbnail_url;
        }
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    
    /**
     * Generate Breadcrumb schema
     */
    public function generate_breadcrumb_schema($post_id, $post, $location) {
        $location_state = isset($location['state']) ? $location['state'] : '';
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array(
                array(
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => home_url('/'),
                ),
                array(
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Weather',
                    'item' => home_url('/category/weather/'),
                ),
                array(
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $location_state . ' Weather',
                    'item' => home_url('/category/weather/' . strtolower($location_state) . '/'),
                ),
                array(
                    '@type' => 'ListItem',
                    'position' => 4,
                    'name' => get_the_title($post_id),
                    'item' => get_permalink($post_id),
                ),
            ),
        );
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    
    /**
     * Generate FAQ schema
     */
    public function generate_faq_schema($post_id, $location, $weather_data) {
        $location_name = isset($location['name']) ? $location['name'] : '';
        $snow_amount = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array(
                array(
                    '@type' => 'Question',
                    'name' => 'How much snow is expected in ' . $location_name . '?',
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text' => 'Forecasters are predicting approximately ' . number_format($snow_amount, 1) . ' inches of snow accumulation in ' . $location_name . '.',
                    ),
                ),
                array(
                    '@type' => 'Question',
                    'name' => 'When will the snow start?',
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text' => 'Snow is expected to begin late tonight and continue through tomorrow afternoon.',
                    ),
                ),
                array(
                    '@type' => 'Question',
                    'name' => 'Is travel safe during this storm?',
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text' => 'Travel is not recommended during the storm. Road conditions will be hazardous with reduced visibility and snow-covered roadways.',
                    ),
                ),
            ),
        );
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    
    /**
     * Generate WeatherForecast schema
     */
    public function generate_weather_forecast_schema($location, $weather_data) {
        $location_name = isset($location['name']) ? $location['name'] : '';
        $snow_amount = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        $current = isset($weather_data['current']) ? $weather_data['current'] : array();
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'WeatherForecast',
            'name' => 'Snow Forecast for ' . $location_name,
            'forecast' => array(
                '@type' => 'Forecast',
                'forecastType' => 'Snow',
                'validFrom' => date('c'),
                'validThrough' => date('c', strtotime('+2 days')),
            ),
        );
        
        if (isset($current['temperature'])) {
            $schema['temperature'] = array(
                '@type' => 'QuantitativeValue',
                'value' => $current['temperature'],
                'unitCode' => 'FAH',
            );
        }
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
    
    /**
     * Add Google Discover meta tags
     */
    public function add_discover_tags($post_id) {
        add_action('wp_head', function() use ($post_id) {
            echo '<meta name="robots" content="max-image-preview:large">' . "\n";
            echo '<meta name="robots" content="max-snippet:-1">' . "\n";
            echo '<meta name="robots" content="max-video-preview:-1">' . "\n";
        });
    }
}
