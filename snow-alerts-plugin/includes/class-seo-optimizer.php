<?php
/**
 * SEO Optimizer Class
 *
 * Handles SEO optimization, Schema.org markup, and meta tags
 * FIXED: NO ?? operators - uses isset() checks before foreach and array access
 *
 * @package Snow_Alerts
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_SEO_Optimizer {
    
    /**
     * Initialize SEO hooks
     */
    public static function init() {
        add_action('wp_head', array(__CLASS__, 'add_meta_tags'));
        add_action('wp_footer', array(__CLASS__, 'add_schema_markup'));
    }
    
    /**
     * Optimize post for SEO
     *
     * @param int $post_id Post ID
     * @param array $city City data
     * @param array $weather_data Weather data
     */
    public static function optimize_post($post_id, $city, $weather_data) {
        // Generate meta description
        $meta_description = self::generate_meta_description($city, $weather_data);
        update_post_meta($post_id, '_snow_alerts_meta_description', $meta_description);
        
        // Generate keywords
        $keywords = self::generate_keywords($city, $weather_data);
        update_post_meta($post_id, '_snow_alerts_keywords', $keywords);
        
        // Store weather data for schema
        update_post_meta($post_id, '_snow_alerts_weather_data', wp_json_encode($weather_data));
        
        // Add post tags
        self::add_post_tags($post_id, $city, $weather_data);
    }
    
    /**
     * Generate SEO meta description
     * FIXED: Added isset() checks before array access
     *
     * @param array $city City data
     * @param array $weather_data Weather data
     * @return string Meta description
     */
    private static function generate_meta_description($city, $weather_data) {
        $city_name = isset($city['city']) ? $city['city'] : '';
        $state = isset($city['state']) ? $city['state'] : '';
        
        $description = "Snow forecast for {$city_name}, {$state}. ";
        
        // Add snowfall info
        if (isset($weather_data['weather_api'])) {
            $snowfall = Snow_Alerts_Weather_Fetcher::extract_snowfall_forecast($weather_data['weather_api']);
            
            if (!empty($snowfall)) {
                $total_snow = 0;
                foreach ($snowfall as $day) {
                    if (isset($day['snow_inches'])) {
                        $total_snow += floatval($day['snow_inches']);
                    }
                }
                
                if ($total_snow > 0) {
                    $description .= "Expected snowfall: " . round($total_snow, 1) . " inches. ";
                }
            }
        }
        
        // Add alert info
        if (isset($weather_data['nws_alerts'])) {
            $alerts = Snow_Alerts_Weather_Fetcher::extract_snow_alerts($weather_data['nws_alerts']);
            
            if (!empty($alerts) && isset($alerts[0]['event'])) {
                $description .= $alerts[0]['event'] . " in effect. ";
            }
        }
        
        $description .= "Get the latest winter weather updates and safety information.";
        
        // Ensure description is 150-160 characters
        if (strlen($description) > 160) {
            $description = substr($description, 0, 157) . '...';
        }
        
        return $description;
    }
    
    /**
     * Generate SEO keywords
     *
     * @param array $city City data
     * @param array $weather_data Weather data
     * @return string Comma-separated keywords
     */
    private static function generate_keywords($city, $weather_data) {
        $city_name = isset($city['city']) ? $city['city'] : '';
        $state = isset($city['state']) ? $city['state'] : '';
        
        $keywords = array(
            'snow forecast',
            'winter weather',
            $city_name . ' snow',
            $state . ' weather',
            'snow alert',
            'winter storm'
        );
        
        // Add alert-specific keywords
        if (isset($weather_data['nws_alerts'])) {
            $alerts = Snow_Alerts_Weather_Fetcher::extract_snow_alerts($weather_data['nws_alerts']);
            
            foreach ($alerts as $alert) {
                if (isset($alert['event'])) {
                    $keywords[] = strtolower($alert['event']);
                }
            }
        }
        
        return implode(', ', array_unique($keywords));
    }
    
    /**
     * Add post tags
     *
     * @param int $post_id Post ID
     * @param array $city City data
     * @param array $weather_data Weather data
     */
    private static function add_post_tags($post_id, $city, $weather_data) {
        $tags = array(
            'snow',
            'winter weather',
            isset($city['city']) ? $city['city'] : '',
            isset($city['state']) ? $city['state'] : ''
        );
        
        // Add alert types as tags
        if (isset($weather_data['nws_alerts'])) {
            $alerts = Snow_Alerts_Weather_Fetcher::extract_snow_alerts($weather_data['nws_alerts']);
            
            foreach ($alerts as $alert) {
                if (isset($alert['event'])) {
                    $tags[] = $alert['event'];
                }
            }
        }
        
        wp_set_post_tags($post_id, $tags, false);
    }
    
    /**
     * Add meta tags to head
     */
    public static function add_meta_tags() {
        if (!is_single()) {
            return;
        }
        
        global $post;
        
        if (!$post) {
            return;
        }
        
        // Check if this is a snow alerts post
        $city = get_post_meta($post->ID, '_snow_alerts_city', true);
        
        if (empty($city)) {
            return;
        }
        
        $meta_description = get_post_meta($post->ID, '_snow_alerts_meta_description', true);
        $keywords = get_post_meta($post->ID, '_snow_alerts_keywords', true);
        
        // Meta description
        if (!empty($meta_description)) {
            echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
        }
        
        // Keywords
        if (!empty($keywords)) {
            echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
        }
        
        // Open Graph tags
        echo '<meta property="og:title" content="' . esc_attr(get_the_title()) . '">' . "\n";
        echo '<meta property="og:type" content="article">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '">' . "\n";
        
        if (!empty($meta_description)) {
            echo '<meta property="og:description" content="' . esc_attr($meta_description) . '">' . "\n";
        }
        
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        
        // Twitter Card tags
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr(get_the_title()) . '">' . "\n";
        
        if (!empty($meta_description)) {
            echo '<meta name="twitter:description" content="' . esc_attr($meta_description) . '">' . "\n";
        }
        
        // Google Discover optimization
        echo '<meta name="robots" content="max-image-preview:large">' . "\n";
    }
    
    /**
     * Add Schema.org markup to footer
     * FIXED: Added isset() checks before foreach
     */
    public static function add_schema_markup() {
        if (!is_single()) {
            return;
        }
        
        global $post;
        
        if (!$post) {
            return;
        }
        
        // Check if this is a snow alerts post
        $city = get_post_meta($post->ID, '_snow_alerts_city', true);
        $state = get_post_meta($post->ID, '_snow_alerts_state', true);
        
        if (empty($city)) {
            return;
        }
        
        $weather_data_json = get_post_meta($post->ID, '_snow_alerts_weather_data', true);
        $weather_data = !empty($weather_data_json) ? json_decode($weather_data_json, true) : array();
        
        // Article schema
        $article_schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => get_the_title(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'author' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name')
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => get_site_icon_url()
                )
            ),
            'description' => get_post_meta($post->ID, '_snow_alerts_meta_description', true),
            'articleBody' => wp_strip_all_tags(get_the_content())
        );
        
        // Weather forecast schema - FIXED: Added isset() check before foreach
        if (isset($weather_data['weather_api']['forecast']['forecastday']) && 
            is_array($weather_data['weather_api']['forecast']['forecastday'])) {
            
            $forecast_days = array();
            
            foreach ($weather_data['weather_api']['forecast']['forecastday'] as $day) {
                $day_data = isset($day['day']) ? $day['day'] : array();
                
                $forecast_days[] = array(
                    '@type' => 'WeatherForecast',
                    'validFrom' => isset($day['date']) ? $day['date'] . 'T00:00:00' : '',
                    'forecastType' => 'snow',
                    'description' => isset($day_data['condition']['text']) ? $day_data['condition']['text'] : '',
                    'temperatureMax' => isset($day_data['maxtemp_f']) ? floatval($day_data['maxtemp_f']) : null,
                    'temperatureMin' => isset($day_data['mintemp_f']) ? floatval($day_data['mintemp_f']) : null
                );
            }
            
            if (!empty($forecast_days)) {
                $weather_schema = array(
                    '@context' => 'https://schema.org',
                    '@type' => 'WeatherForecast',
                    'name' => 'Snow Forecast for ' . $city . ', ' . $state,
                    'forecast' => $forecast_days
                );
                
                echo '<script type="application/ld+json">' . "\n";
                echo wp_json_encode($weather_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
                echo '</script>' . "\n";
            }
        }
        
        // Output article schema
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($article_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        echo '</script>' . "\n";
    }
}

// Initialize SEO hooks
Snow_Alerts_SEO_Optimizer::init();
