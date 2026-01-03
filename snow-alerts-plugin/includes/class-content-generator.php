<?php
/**
 * Content Generator Class
 *
 * Generates unique AI-powered content using OpenAI
 * FIXED: NO ?? operators - uses isset() ternary for PHP 7.4 compatibility
 *
 * @package Snow_Alerts
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Content_Generator {
    
    /**
     * Generate article for a city with snow forecast
     *
     * @param array $city City data
     * @param array $weather_data Weather data
     * @return int|WP_Error Post ID or error
     */
    public static function generate_article($city, $weather_data) {
        // Extract relevant weather information
        $snowfall = array();
        $alerts = array();
        
        if (isset($weather_data['weather_api'])) {
            $snowfall = Snow_Alerts_Weather_Fetcher::extract_snowfall_forecast($weather_data['weather_api']);
        }
        
        if (isset($weather_data['nws_alerts'])) {
            $alerts = Snow_Alerts_Weather_Fetcher::extract_snow_alerts($weather_data['nws_alerts']);
        }
        
        // Generate unique title
        $title = self::generate_title($city, $snowfall, $alerts);
        
        if (is_wp_error($title)) {
            return $title;
        }
        
        // Check for duplicate title
        if (Snow_Alerts_Uniqueness_Checker::is_duplicate_title($title)) {
            // Regenerate with different prompt
            $title = self::generate_title($city, $snowfall, $alerts, true);
            
            if (is_wp_error($title)) {
                return $title;
            }
        }
        
        // Generate article content
        $content = self::generate_content($city, $weather_data, $snowfall, $alerts, $title);
        
        if (is_wp_error($content)) {
            return $content;
        }
        
        // Check for duplicate content
        if (Snow_Alerts_Uniqueness_Checker::is_duplicate_content($content)) {
            return new WP_Error('duplicate_content', __('Generated content is too similar to existing article', 'snow-alerts'));
        }
        
        // Create WordPress post
        $post_id = self::create_post($title, $content, $city);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Add SEO optimization
        Snow_Alerts_SEO_Optimizer::optimize_post($post_id, $city, $weather_data);
        
        // Log article generation
        $log_data = array();
        
        if (!empty($alerts) && isset($alerts[0]['onset'])) {
            $log_data['alert_start'] = $alerts[0]['onset'];
        }
        
        if (!empty($alerts) && isset($alerts[0]['expires'])) {
            $log_data['alert_end'] = $alerts[0]['expires'];
        }
        
        if (!empty($snowfall)) {
            $log_data['snowfall_start'] = isset($snowfall[0]['date']) ? $snowfall[0]['date'] : '';
            $last_index = count($snowfall) - 1;
            $log_data['snowfall_end'] = isset($snowfall[$last_index]['date']) ? $snowfall[$last_index]['date'] : '';
            
            $total_snow = 0;
            foreach ($snowfall as $day) {
                if (isset($day['snow_inches'])) {
                    $total_snow += floatval($day['snow_inches']);
                }
            }
            $log_data['snowfall_amount'] = $total_snow;
        }
        
        Snow_Alerts_Database::log_article(
            $post_id,
            isset($city['city']) ? $city['city'] : '',
            isset($city['state']) ? $city['state'] : '',
            $title,
            $content,
            $log_data
        );
        
        return $post_id;
    }
    
    /**
     * Generate unique title using OpenAI
     * FIXED: NO ?? operators
     *
     * @param array $city City data
     * @param array $snowfall Snowfall forecast
     * @param array $alerts Weather alerts
     * @param bool $alternative Generate alternative title
     * @return string|WP_Error Title or error
     */
    private static function generate_title($city, $snowfall, $alerts, $alternative = false) {
        $api_key = Snow_Alerts_API_Manager::get_api_key('openai');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('OpenAI API key not configured', 'snow-alerts'));
        }
        
        $city_name = isset($city['city']) ? $city['city'] : '';
        $state = isset($city['state']) ? $city['state'] : '';
        
        // Build context for title generation
        $context = "City: {$city_name}, {$state}\n";
        
        if (!empty($snowfall)) {
            $total_snow = 0;
            foreach ($snowfall as $day) {
                if (isset($day['snow_inches'])) {
                    $total_snow += floatval($day['snow_inches']);
                }
            }
            $context .= "Expected snowfall: " . round($total_snow, 1) . " inches\n";
        }
        
        if (!empty($alerts)) {
            $alert_types = array();
            foreach ($alerts as $alert) {
                if (isset($alert['event'])) {
                    $alert_types[] = $alert['event'];
                }
            }
            $context .= "Active alerts: " . implode(', ', $alert_types) . "\n";
        }
        
        $variation = $alternative ? ' Create a completely different title variation.' : '';
        
        $prompt = "Generate a unique, engaging news headline about snow weather for {$city_name}, {$state}. "
                . "Maximum 60 characters. Focus on the snow forecast and any weather alerts. "
                . "Make it newsworthy and click-worthy.{$variation}\n\n{$context}\n\nHeadline:";
        
        $title = self::call_openai($prompt, 60);
        
        if (is_wp_error($title)) {
            return $title;
        }
        
        // Clean up title
        $title = trim($title);
        $title = str_replace(array('"', "'", "\n", "\r"), '', $title);
        
        // Ensure title is not too long
        if (strlen($title) > 60) {
            $title = substr($title, 0, 57) . '...';
        }
        
        return $title;
    }
    
    /**
     * Generate article content using OpenAI
     * FIXED: NO ?? operators - uses isset() ternary
     *
     * @param array $city City data
     * @param array $weather_data Full weather data
     * @param array $snowfall Snowfall forecast
     * @param array $alerts Weather alerts
     * @param string $title Article title
     * @return string|WP_Error Content or error
     */
    private static function generate_content($city, $weather_data, $snowfall, $alerts, $title) {
        $api_key = Snow_Alerts_API_Manager::get_api_key('openai');
        
        if (empty($api_key)) {
            return new WP_Error('no_api_key', __('OpenAI API key not configured', 'snow-alerts'));
        }
        
        $city_name = isset($city['city']) ? $city['city'] : '';
        $state = isset($city['state']) ? $city['state'] : '';
        $lat = isset($city['latitude']) ? floatval($city['latitude']) : 0;
        $lon = isset($city['longitude']) ? floatval($city['longitude']) : 0;
        
        // Build detailed weather context
        $context = "Title: {$title}\n\n";
        $context .= "City: {$city_name}, {$state}\n";
        $context .= "Coordinates: {$lat}, {$lon}\n\n";
        
        // Add snowfall forecast details
        if (!empty($snowfall)) {
            $context .= "Snowfall Forecast:\n";
            foreach ($snowfall as $day) {
                $date = isset($day['date']) ? $day['date'] : '';
                $inches = isset($day['snow_inches']) ? $day['snow_inches'] : 0;
                $condition = isset($day['condition']) ? $day['condition'] : '';
                $context .= "- {$date}: {$inches} inches, {$condition}\n";
            }
            $context .= "\n";
        }
        
        // Add alert details
        if (!empty($alerts)) {
            $context .= "Weather Alerts:\n";
            foreach ($alerts as $alert) {
                $event = isset($alert['event']) ? $alert['event'] : '';
                $headline = isset($alert['headline']) ? $alert['headline'] : '';
                $severity = isset($alert['severity']) ? $alert['severity'] : '';
                $context .= "- {$event} ({$severity})\n";
                $context .= "  {$headline}\n";
            }
            $context .= "\n";
        }
        
        $word_count_min = get_option('snow_alerts_word_count_min', 800);
        $word_count_max = get_option('snow_alerts_word_count_max', 1200);
        
        $prompt = "Write a unique, informative news article about the snow forecast for {$city_name}, {$state}. "
                . "Target length: {$word_count_min}-{$word_count_max} words.\n\n"
                . "Requirements:\n"
                . "- Write in journalistic style\n"
                . "- Include specific snowfall amounts and dates\n"
                . "- Mention any active weather alerts\n"
                . "- Add safety recommendations for residents\n"
                . "- Include information about how this may impact daily life\n"
                . "- Use paragraph breaks for readability\n"
                . "- DO NOT include the title in the article body\n"
                . "- English language only\n\n"
                . "{$context}\n\n"
                . "Article:";
        
        $content = self::call_openai($prompt, $word_count_max * 6); // Approximate max tokens
        
        if (is_wp_error($content)) {
            return $content;
        }
        
        // Add Windy map embed
        $windy_embed = self::generate_windy_embed($lat, $lon);
        
        // Add related articles section
        $related = self::get_related_articles_html($city);
        
        // Combine content
        $full_content = wpautop($content);
        $full_content .= "\n\n" . $windy_embed;
        
        if (!empty($related)) {
            $full_content .= "\n\n" . $related;
        }
        
        return $full_content;
    }
    
    /**
     * Call OpenAI API
     *
     * @param string $prompt Prompt text
     * @param int $max_tokens Maximum tokens
     * @return string|WP_Error Response or error
     */
    private static function call_openai($prompt, $max_tokens = 1000) {
        $api_key = Snow_Alerts_API_Manager::get_api_key('openai');
        
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $body = array(
            'model' => 'gpt-3.5-turbo',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are a professional weather news writer. Write clear, accurate, and engaging content about snow forecasts and winter weather.'
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'max_tokens' => $max_tokens,
            'temperature' => 0.8
        );
        
        $response = wp_remote_post($url, array(
            'timeout' => 60,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => wp_json_encode($body)
        ));
        
        // Log API activity
        $response_code = is_wp_error($response) ? null : wp_remote_retrieve_response_code($response);
        $success = !is_wp_error($response) && $response_code === 200;
        
        Snow_Alerts_Database::log_api_activity(
            'OpenAI',
            $url,
            array('prompt_length' => strlen($prompt)),
            $response_code,
            is_wp_error($response) ? $response->get_error_message() : 'Success',
            $success
        );
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        if ($response_code !== 200) {
            $body_text = wp_remote_retrieve_body($response);
            $error_data = json_decode($body_text, true);
            $error_msg = isset($error_data['error']['message']) ? $error_data['error']['message'] : 'OpenAI API error';
            return new WP_Error('openai_error', $error_msg);
        }
        
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('invalid_response', __('Invalid OpenAI response', 'snow-alerts'));
        }
        
        return trim($data['choices'][0]['message']['content']);
    }
    
    /**
     * Create WordPress post
     *
     * @param string $title Post title
     * @param string $content Post content
     * @param array $city City data
     * @return int|WP_Error Post ID or error
     */
    private static function create_post($title, $content, $city) {
        $post_data = array(
            'post_title' => sanitize_text_field($title),
            'post_content' => wp_kses_post($content),
            'post_status' => 'publish',
            'post_type' => 'post',
            'post_category' => array(self::get_or_create_category())
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Add custom fields
        update_post_meta($post_id, '_snow_alerts_city', isset($city['city']) ? $city['city'] : '');
        update_post_meta($post_id, '_snow_alerts_state', isset($city['state']) ? $city['state'] : '');
        update_post_meta($post_id, '_snow_alerts_generated', current_time('mysql'));
        
        return $post_id;
    }
    
    /**
     * Get or create Snow Alerts category
     *
     * @return int Category ID
     */
    private static function get_or_create_category() {
        $category_name = 'Snow Alerts';
        $category = get_term_by('name', $category_name, 'category');
        
        if ($category) {
            return $category->term_id;
        }
        
        $result = wp_insert_term($category_name, 'category', array(
            'description' => 'Snow forecasts and winter storm alerts',
            'slug' => 'snow-alerts'
        ));
        
        if (is_wp_error($result)) {
            return 1; // Default category
        }
        
        return $result['term_id'];
    }
    
    /**
     * Generate Windy map embed code
     *
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return string HTML embed code
     */
    private static function generate_windy_embed($lat, $lon) {
        $zoom = 8;
        
        $html = '<div class="snow-alerts-windy-map">';
        $html .= '<h3>' . esc_html__('Interactive Weather Map', 'snow-alerts') . '</h3>';
        $html .= '<iframe width="100%" height="450" ';
        $html .= 'src="https://embed.windy.com/embed2.html?lat=' . esc_attr($lat) . '&lon=' . esc_attr($lon);
        $html .= '&detailLat=' . esc_attr($lat) . '&detailLon=' . esc_attr($lon);
        $html .= '&width=650&height=450&zoom=' . esc_attr($zoom);
        $html .= '&level=surface&overlay=rain&product=ecmwf&menu=&message=&marker=&calendar=now';
        $html .= '&pressure=&type=map&location=coordinates&detail=&metricWind=default&metricTemp=default';
        $html .= '&radarRange=-1" frameborder="0"></iframe>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get related articles HTML
     *
     * @param array $city Current city
     * @return string HTML for related articles
     */
    private static function get_related_articles_html($city) {
        $state = isset($city['state']) ? $city['state'] : '';
        
        if (empty($state)) {
            return '';
        }
        
        // Get recent articles from the same state
        global $wpdb;
        $table = $wpdb->prefix . 'snow_alerts_articles';
        
        $related = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, city FROM {$table} WHERE state = %s ORDER BY created_at DESC LIMIT 5",
            $state
        ));
        
        if (empty($related)) {
            return '';
        }
        
        $html = '<div class="snow-alerts-related">';
        $html .= '<h3>' . esc_html__('Related Snow Alerts', 'snow-alerts') . '</h3>';
        $html .= '<ul>';
        
        foreach ($related as $article) {
            $post_title = get_the_title($article->post_id);
            $post_url = get_permalink($article->post_id);
            
            if ($post_title && $post_url) {
                $html .= '<li><a href="' . esc_url($post_url) . '">' . esc_html($post_title) . '</a></li>';
            }
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        return $html;
    }
}
