<?php
/**
 * Advanced SEO Class
 * 
 * Handles IndexNow API integration, Google Discover optimization,
 * sitemap enhancements, and comprehensive SEO features.
 */

class Snow_Alerts_Advanced_SEO {
    
    /**
     * IndexNow API endpoints
     */
    private static $indexnow_endpoints = array(
        'https://api.indexnow.org/indexnow',
        'https://www.bing.com/indexnow',
        'https://yandex.com/indexnow',
    );
    
    /**
     * Initialize the class
     */
    public static function init() {
        // Hook into article publication
        add_action('snow_alerts_article_published', array(__CLASS__, 'on_article_published'), 10, 2);
        
        // Add Google Discover meta tags
        add_action('wp_head', array(__CLASS__, 'add_discover_meta_tags'));
        
        // Modify sitemap
        add_filter('wp_sitemaps_posts_entry', array(__CLASS__, 'modify_sitemap_entry'), 10, 3);
        
        // Enhance RSS feed
        add_action('rss2_item', array(__CLASS__, 'enhance_rss_feed'));
        
        // Add Core Web Vitals optimizations
        add_action('wp_head', array(__CLASS__, 'add_core_web_vitals_optimizations'), 1);
    }
    
    /**
     * Handle article publication
     */
    public static function on_article_published($post_id, $location_data) {
        // Submit to IndexNow
        if (get_option('snow_alerts_enable_indexnow', true)) {
            self::submit_to_indexnow($post_id, $location_data);
        }
        
        // Ping Google sitemap
        self::ping_google_sitemap();
    }
    
    /**
     * Submit URL to IndexNow API
     */
    public static function submit_to_indexnow($post_id, $location_data) {
        $url = get_permalink($post_id);
        $key = self::get_indexnow_key();
        
        if (empty($key) || empty($url)) {
            error_log('Snow Alerts: IndexNow submission failed - missing key or URL');
            return false;
        }
        
        $host = parse_url(home_url(), PHP_URL_HOST);
        $key_location = home_url() . '/' . $key . '.txt';
        
        $payload = array(
            'host' => $host,
            'key' => $key,
            'keyLocation' => $key_location,
            'urlList' => array($url),
        );
        
        $success = false;
        
        // Submit to all IndexNow endpoints
        foreach (self::$indexnow_endpoints as $endpoint) {
            $response = wp_remote_post($endpoint, array(
                'headers' => array(
                    'Content-Type' => 'application/json; charset=utf-8',
                ),
                'body' => wp_json_encode($payload),
                'timeout' => 15,
            ));
            
            if (!is_wp_error($response)) {
                $status_code = wp_remote_retrieve_response_code($response);
                if ($status_code === 200 || $status_code === 202) {
                    $success = true;
                    error_log('Snow Alerts: IndexNow submission successful to ' . $endpoint);
                } else {
                    error_log('Snow Alerts: IndexNow submission failed to ' . $endpoint . ' - Status: ' . $status_code);
                }
            } else {
                error_log('Snow Alerts: IndexNow submission error to ' . $endpoint . ' - ' . $response->get_error_message());
            }
        }
        
        return $success;
    }
    
    /**
     * Get or generate IndexNow API key
     */
    public static function get_indexnow_key() {
        $key = get_option('snow_alerts_indexnow_key');
        
        if (empty($key)) {
            // Generate a new key (32 character hex string)
            $key = bin2hex(random_bytes(16));
            update_option('snow_alerts_indexnow_key', $key);
        }
        
        return $key;
    }
    
    /**
     * Create IndexNow key file in root directory
     */
    public static function create_indexnow_key_file() {
        $key = self::get_indexnow_key();
        $file_path = ABSPATH . $key . '.txt';
        
        if (!file_exists($file_path)) {
            $result = file_put_contents($file_path, $key);
            if ($result === false) {
                error_log('Snow Alerts: Failed to create IndexNow key file at ' . $file_path);
            }
        }
    }
    
    /**
     * Ping Google sitemap
     */
    public static function ping_google_sitemap() {
        $sitemap_url = home_url() . '/wp-sitemap.xml';
        $ping_url = 'https://www.google.com/ping?sitemap=' . urlencode($sitemap_url);
        
        $response = wp_remote_get($ping_url, array(
            'timeout' => 10,
        ));
        
        if (is_wp_error($response)) {
            error_log('Snow Alerts: Google sitemap ping failed - ' . $response->get_error_message());
            return false;
        }
        
        error_log('Snow Alerts: Google sitemap pinged successfully');
        return true;
    }
    
    /**
     * Modify sitemap entry for snow alert posts
     */
    public static function modify_sitemap_entry($entry, $post, $name) {
        if ($post->post_type === 'post') {
            $is_snow_alert = get_post_meta($post->ID, '_snow_alerts_article', true);
            
            if ($is_snow_alert) {
                // Set high priority for snow alerts
                $entry['priority'] = 0.9;
                
                // Set frequent change
                $entry['changefreq'] = 'hourly';
                
                // Use modified date
                $entry['lastmod'] = get_post_modified_time('c', false, $post);
            }
        }
        
        return $entry;
    }
    
    /**
     * Add Google Discover meta tags
     */
    public static function add_discover_meta_tags() {
        if (!is_single()) {
            return;
        }
        
        global $post;
        
        // Check if this is a snow alert post
        $is_snow_alert = get_post_meta($post->ID, '_snow_alerts_article', true);
        if (!$is_snow_alert) {
            return;
        }
        
        // Robots meta for Google Discover
        echo '<meta name="robots" content="max-image-preview:large, max-snippet:-1, max-video-preview:-1">' . "\n";
        
        // Article meta tags
        $published_time = get_the_date('c', $post);
        $modified_time = get_post_modified_time('c', false, $post);
        $author_name = get_option('snow_alerts_author_name', 'Weather Team');
        
        echo '<meta property="article:published_time" content="' . esc_attr($published_time) . '">' . "\n";
        echo '<meta property="article:modified_time" content="' . esc_attr($modified_time) . '">' . "\n";
        echo '<meta property="article:author" content="' . esc_attr($author_name) . '">' . "\n";
        echo '<meta property="article:section" content="Weather">' . "\n";
        
        // Article tags
        $tags = get_the_tags($post->ID);
        if ($tags && is_array($tags)) {
            foreach ($tags as $tag) {
                echo '<meta property="article:tag" content="' . esc_attr($tag->name) . '">' . "\n";
            }
        }
        
        // Viewport optimization for mobile
        echo '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">' . "\n";
    }
    
    /**
     * Enhance RSS feed with geo tags
     */
    public static function enhance_rss_feed() {
        global $post;
        
        $latitude = get_post_meta($post->ID, '_snow_alerts_latitude', true);
        $longitude = get_post_meta($post->ID, '_snow_alerts_longitude', true);
        
        if (!empty($latitude) && !empty($longitude)) {
            echo '<geo:lat>' . esc_html($latitude) . '</geo:lat>' . "\n";
            echo '<geo:long>' . esc_html($longitude) . '</geo:long>' . "\n";
        }
        
        // Add categories for news aggregators
        echo '<category>Weather</category>' . "\n";
        echo '<category>News</category>' . "\n";
        
        $location_name = get_post_meta($post->ID, '_snow_alerts_location_name', true);
        if (!empty($location_name)) {
            echo '<category>' . esc_html($location_name) . '</category>' . "\n";
        }
    }
    
    /**
     * Add Core Web Vitals optimizations
     */
    public static function add_core_web_vitals_optimizations() {
        // Preconnect to external resources
        echo '<link rel="preconnect" href="https://embed.windy.com" crossorigin>' . "\n";
        echo '<link rel="dns-prefetch" href="//embed.windy.com">' . "\n";
        
        // Add critical inline CSS hint
        echo '<style id="critical-css">/* Critical CSS loaded inline for LCP optimization */</style>' . "\n";
        
        // Add lazy loading script hint
        echo '<script>/* Lazy load configuration */' . "\n";
        echo 'if ("loading" in HTMLIFrameElement.prototype) {' . "\n";
        echo '  const iframes = document.querySelectorAll("iframe[data-src]");' . "\n";
        echo '  iframes.forEach(iframe => {' . "\n";
        echo '    iframe.src = iframe.dataset.src;' . "\n";
        echo '  });' . "\n";
        echo '}' . "\n";
        echo '</script>' . "\n";
    }
    
    /**
     * Generate breadcrumb schema
     */
    public static function generate_breadcrumb_schema($post_id, $location_data) {
        $post = get_post($post_id);
        if (!$post) {
            return null;
        }
        
        $state_name = isset($location_data['state']) ? $location_data['state'] : 'Unknown State';
        
        $breadcrumb = array(
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array(
                array(
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => home_url(),
                ),
                array(
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Weather',
                    'item' => home_url('/weather/'),
                ),
                array(
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $state_name,
                    'item' => home_url('/weather/' . sanitize_title($state_name) . '/'),
                ),
                array(
                    '@type' => 'ListItem',
                    'position' => 4,
                    'name' => get_the_title($post_id),
                    'item' => get_permalink($post_id),
                ),
            ),
        );
        
        return $breadcrumb;
    }
    
    /**
     * Add FAQ schema for Q&A format articles
     */
    public static function add_faq_schema($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return null;
        }
        
        $content = $post->post_content;
        
        // Detect Q&A format (looking for questions)
        $questions = array();
        
        // Pattern to match questions and answers
        // Looking for <h3>Question</h3> followed by answer paragraphs
        preg_match_all('/<h3>(.*?)<\/h3>\s*<p>(.*?)<\/p>/is', $content, $matches, PREG_SET_ORDER);
        
        if (empty($matches)) {
            return null;
        }
        
        foreach ($matches as $match) {
            if (isset($match[1]) && isset($match[2])) {
                $question_text = wp_strip_all_tags($match[1]);
                $answer_text = wp_strip_all_tags($match[2]);
                
                // Only include if it looks like a question
                if (preg_match('/\?$/', $question_text)) {
                    $questions[] = array(
                        '@type' => 'Question',
                        'name' => $question_text,
                        'acceptedAnswer' => array(
                            '@type' => 'Answer',
                            'text' => $answer_text,
                        ),
                    );
                }
            }
        }
        
        if (empty($questions)) {
            return null;
        }
        
        $faq_schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $questions,
        );
        
        return $faq_schema;
    }
    
    /**
     * Generate enhanced article schema with E-E-A-T signals
     */
    public static function generate_enhanced_article_schema($post_id, $location_data, $weather_data) {
        $post = get_post($post_id);
        if (!$post) {
            return null;
        }
        
        $author_name = get_option('snow_alerts_author_name', 'Weather Team');
        $site_url = home_url();
        $site_name = get_bloginfo('name');
        
        // Get social media URLs for E-E-A-T
        $facebook_url = get_option('snow_alerts_facebook_url', '');
        $twitter_url = get_option('snow_alerts_twitter_url', '');
        $instagram_url = get_option('snow_alerts_instagram_url', '');
        
        $same_as = array();
        if (!empty($facebook_url)) {
            $same_as[] = $facebook_url;
        }
        if (!empty($twitter_url)) {
            $same_as[] = $twitter_url;
        }
        if (!empty($instagram_url)) {
            $same_as[] = $instagram_url;
        }
        
        // Build author as Organization
        $author = array(
            '@type' => 'Organization',
            'name' => $author_name,
            'url' => $site_url,
        );
        
        if (!empty($same_as)) {
            $author['sameAs'] = $same_as;
        }
        
        // Get logo
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
            if ($logo_url) {
                $author['logo'] = array(
                    '@type' => 'ImageObject',
                    'url' => $logo_url,
                );
            }
        }
        
        // Get featured image
        $image_url = get_the_post_thumbnail_url($post_id, 'full');
        $image_schema = null;
        if ($image_url) {
            $image_schema = array(
                '@type' => 'ImageObject',
                'url' => $image_url,
                'width' => 1200,
                'height' => 630,
            );
        }
        
        // Build article schema
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => get_the_title($post_id),
            'description' => get_the_excerpt($post_id),
            'articleBody' => wp_strip_all_tags($post->post_content),
            'author' => $author,
            'publisher' => array(
                '@type' => 'Organization',
                'name' => $site_name,
                'url' => $site_url,
            ),
            'datePublished' => get_the_date('c', $post),
            'dateModified' => get_post_modified_time('c', false, $post),
            'articleSection' => 'Weather',
            'wordCount' => str_word_count(wp_strip_all_tags($post->post_content)),
        );
        
        if ($image_schema) {
            $schema['image'] = $image_schema;
        }
        
        // Add temporal coverage if available
        if (isset($weather_data['alert_start']) && isset($weather_data['alert_end'])) {
            $schema['temporalCoverage'] = $weather_data['alert_start'] . '/' . $weather_data['alert_end'];
        }
        
        // Add spatial coverage
        if (isset($location_data['latitude']) && isset($location_data['longitude'])) {
            $schema['spatialCoverage'] = array(
                '@type' => 'Place',
                'geo' => array(
                    '@type' => 'GeoCoordinates',
                    'latitude' => $location_data['latitude'],
                    'longitude' => $location_data['longitude'],
                ),
            );
            
            if (isset($location_data['city'])) {
                $schema['spatialCoverage']['name'] = $location_data['city'];
            }
        }
        
        // Add speakable for voice search
        $schema['speakable'] = array(
            '@type' => 'SpeakableSpecification',
            'cssSelector' => array('.entry-title', '.entry-content p'),
        );
        
        return $schema;
    }
}
