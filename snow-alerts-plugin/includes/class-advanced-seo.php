<?php
/**
 * Advanced SEO
 * 
 * IndexNow API integration, Google sitemap ping, and enhanced SEO features
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Advanced_SEO {
    
    /**
     * @var Snow_Alerts_Database
     */
    private $database;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->database = new Snow_Alerts_Database();
    }
    
    /**
     * Submit URL to IndexNow (Bing, Yandex instant indexing)
     */
    public function submit_to_indexnow($post_id) {
        $url = get_permalink($post_id);
        
        if (empty($url)) {
            return false;
        }
        
        $host = parse_url(home_url(), PHP_URL_HOST);
        $key = $this->get_indexnow_key();
        
        $api_url = 'https://api.indexnow.org/indexnow';
        
        $body = array(
            'host' => $host,
            'key' => $key,
            'keyLocation' => home_url('/' . $key . '.txt'),
            'urlList' => array($url),
        );
        
        $start_time = microtime(true);
        $response = wp_remote_post($api_url, array(
            'timeout' => 15,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($body),
        ));
        $execution_time = microtime(true) - $start_time;
        
        if (is_wp_error($response)) {
            $this->database->log_api_call(
                'indexnow',
                $api_url,
                $body,
                $response->get_error_message(),
                0,
                false,
                $response->get_error_message(),
                $execution_time
            );
            
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        $success = in_array($status_code, array(200, 202));
        
        $this->database->log_api_call(
            'indexnow',
            $api_url,
            $body,
            $response_body,
            $status_code,
            $success,
            $success ? '' : 'HTTP ' . $status_code,
            $execution_time
        );
        
        return $success;
    }
    
    /**
     * Get or generate IndexNow key
     */
    private function get_indexnow_key() {
        $key = get_option('snow_alerts_indexnow_key');
        
        if (!$key) {
            $key = bin2hex(random_bytes(16));
            update_option('snow_alerts_indexnow_key', $key);
            
            // Create key file
            $this->create_indexnow_key_file($key);
        }
        
        return $key;
    }
    
    /**
     * Create IndexNow key file
     */
    private function create_indexnow_key_file($key) {
        $file_path = ABSPATH . $key . '.txt';
        file_put_contents($file_path, $key);
    }
    
    /**
     * Ping Google sitemap
     */
    public function ping_google_sitemap() {
        $sitemap_url = home_url('/sitemap.xml');
        $ping_url = 'https://www.google.com/ping?sitemap=' . urlencode($sitemap_url);
        
        $start_time = microtime(true);
        $response = wp_remote_get($ping_url, array('timeout' => 15));
        $execution_time = microtime(true) - $start_time;
        
        if (is_wp_error($response)) {
            $this->database->log_api_call(
                'google_sitemap_ping',
                $ping_url,
                array('sitemap' => $sitemap_url),
                $response->get_error_message(),
                0,
                false,
                $response->get_error_message(),
                $execution_time
            );
            
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        $success = $status_code === 200;
        
        $this->database->log_api_call(
            'google_sitemap_ping',
            $ping_url,
            array('sitemap' => $sitemap_url),
            $response_body,
            $status_code,
            $success,
            $success ? '' : 'HTTP ' . $status_code,
            $execution_time
        );
        
        return $success;
    }
    
    /**
     * Update sitemap priority for snow articles
     */
    public function update_sitemap_priority($post_id, $priority = 0.9) {
        update_post_meta($post_id, '_yoast_wpseo_sitemap_priority', $priority);
        update_post_meta($post_id, '_yoast_wpseo_sitemap_changefreq', 'hourly');
    }
    
    /**
     * Add E-E-A-T signals to post
     */
    public function add_eeat_signals($post_id) {
        $author_name = get_option('snow_alerts_author_name', get_bloginfo('name'));
        
        // Add author bio
        $author_bio = 'Expert weather analysis and forecasts from ' . $author_name . ', your trusted source for local weather information.';
        update_post_meta($post_id, '_author_bio', $author_bio);
        
        // Add expertise signals
        update_post_meta($post_id, '_content_expertise', 'weather_forecasting');
        update_post_meta($post_id, '_content_authority', 'meteorological');
        update_post_meta($post_id, '_content_trustworthiness', 'verified_sources');
        
        // Add last updated timestamp
        update_post_meta($post_id, '_last_fact_checked', current_time('mysql'));
    }
    
    /**
     * Optimize for Core Web Vitals
     */
    public function optimize_core_web_vitals() {
        // Add resource hints
        add_action('wp_head', function() {
            echo '<link rel="preconnect" href="https://api.weatherapi.com">' . "\n";
            echo '<link rel="preconnect" href="https://api.openai.com">' . "\n";
            echo '<link rel="dns-prefetch" href="https://images.unsplash.com">' . "\n";
        }, 1);
        
        // Defer non-critical CSS
        add_filter('style_loader_tag', function($html, $handle) {
            if ($handle === 'snow-alerts-public') {
                return str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html);
            }
            return $html;
        }, 10, 2);
        
        // Lazy load images
        add_filter('wp_get_attachment_image_attributes', function($attr) {
            if (!isset($attr['loading'])) {
                $attr['loading'] = 'lazy';
            }
            return $attr;
        });
    }
    
    /**
     * Add social media meta tags
     */
    public function add_social_meta_tags($post_id, $location_name, $snow_amount) {
        $title = get_the_title($post_id);
        $description = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
        $image_url = get_the_post_thumbnail_url($post_id, 'full');
        $permalink = get_permalink($post_id);
        
        $facebook_url = get_option('snow_alerts_facebook_url', '');
        $twitter_url = get_option('snow_alerts_twitter_url', '');
        
        add_action('wp_head', function() use ($title, $description, $image_url, $permalink, $facebook_url, $twitter_url) {
            // Open Graph
            echo '<meta property="og:type" content="article">' . "\n";
            echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
            echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
            echo '<meta property="og:url" content="' . esc_url($permalink) . '">' . "\n";
            
            if ($image_url) {
                echo '<meta property="og:image" content="' . esc_url($image_url) . '">' . "\n";
                echo '<meta property="og:image:width" content="1200">' . "\n";
                echo '<meta property="og:image:height" content="630">' . "\n";
            }
            
            if ($facebook_url) {
                echo '<meta property="article:publisher" content="' . esc_url($facebook_url) . '">' . "\n";
            }
            
            // Twitter Card
            echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
            echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
            echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
            
            if ($image_url) {
                echo '<meta name="twitter:image" content="' . esc_url($image_url) . '">' . "\n";
            }
            
            if ($twitter_url) {
                $twitter_handle = str_replace('https://twitter.com/', '@', $twitter_url);
                echo '<meta name="twitter:site" content="' . esc_attr($twitter_handle) . '">' . "\n";
            }
        });
    }
    
    /**
     * Comprehensive post optimization
     */
    public function optimize_post($post_id, $location_name, $snow_amount) {
        // Submit to IndexNow
        $this->submit_to_indexnow($post_id);
        
        // Update sitemap priority
        $this->update_sitemap_priority($post_id);
        
        // Add E-E-A-T signals
        $this->add_eeat_signals($post_id);
        
        // Add social meta tags
        $this->add_social_meta_tags($post_id, $location_name, $snow_amount);
        
        // Ping Google sitemap (do this last, less frequently)
        if (mt_rand(1, 10) === 1) {
            $this->ping_google_sitemap();
        }
    }
}
