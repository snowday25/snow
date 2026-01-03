<?php
/**
 * Image Optimizer
 * 
 * Fetches and optimizes images from Unsplash API
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Image_Optimizer {
    
    /**
     * @var Snow_Alerts_API_Manager
     */
    private $api_manager;
    
    /**
     * @var Snow_Alerts_Database
     */
    private $database;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_manager = new Snow_Alerts_API_Manager();
        $this->database = new Snow_Alerts_Database();
    }
    
    /**
     * Get featured image for article
     */
    public function get_featured_image($location_name, $storm_type = 'snow') {
        $api_key = $this->api_manager->get_api_key('unsplash');
        
        if (empty($api_key)) {
            return $this->get_placeholder_image();
        }
        
        $query = 'winter snow storm ' . $storm_type;
        $url = 'https://api.unsplash.com/photos/random?query=' . urlencode($query) . '&orientation=landscape&w=1200&h=630';
        
        $start_time = microtime(true);
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Client-ID ' . $api_key,
            ),
        ));
        $execution_time = microtime(true) - $start_time;
        
        if (is_wp_error($response)) {
            $this->database->log_api_call(
                'unsplash',
                $url,
                array('query' => $query),
                $response->get_error_message(),
                0,
                false,
                $response->get_error_message(),
                $execution_time
            );
            
            return $this->get_placeholder_image();
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            $this->database->log_api_call(
                'unsplash',
                $url,
                array('query' => $query),
                $body,
                $status_code,
                false,
                'HTTP ' . $status_code,
                $execution_time
            );
            
            return $this->get_placeholder_image();
        }
        
        $data = json_decode($body, true);
        
        if (!$data || json_last_error() !== JSON_ERROR_NONE) {
            return $this->get_placeholder_image();
        }
        
        $this->database->log_api_call(
            'unsplash',
            $url,
            array('query' => $query),
            $body,
            $status_code,
            true,
            '',
            $execution_time
        );
        
        $image_url = isset($data['urls']) && isset($data['urls']['regular']) ? $data['urls']['regular'] : '';
        $photographer = isset($data['user']) && isset($data['user']['name']) ? $data['user']['name'] : '';
        $photographer_url = isset($data['user']) && isset($data['user']['links']) && isset($data['user']['links']['html']) ? $data['user']['links']['html'] : '';
        
        if (empty($image_url)) {
            return $this->get_placeholder_image();
        }
        
        return array(
            'url' => $image_url,
            'alt' => 'Winter snow storm in ' . $location_name,
            'photographer' => $photographer,
            'photographer_url' => $photographer_url,
            'source' => 'unsplash',
        );
    }
    
    /**
     * Get placeholder image
     */
    private function get_placeholder_image() {
        return array(
            'url' => 'https://via.placeholder.com/1200x630/4A90E2/FFFFFF?text=Snow+Alert',
            'alt' => 'Snow Alert Placeholder',
            'source' => 'placeholder',
        );
    }
    
    /**
     * Upload image to WordPress media library
     */
    public function upload_to_media_library($image_url, $alt_text, $post_id = 0) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        // Download image
        $tmp = download_url($image_url);
        
        if (is_wp_error($tmp)) {
            return false;
        }
        
        $file_array = array(
            'name' => 'snow-alert-' . time() . '.jpg',
            'tmp_name' => $tmp,
        );
        
        // Upload to media library
        $attachment_id = media_handle_sideload($file_array, $post_id);
        
        if (is_wp_error($attachment_id)) {
            @unlink($file_array['tmp_name']);
            return false;
        }
        
        // Set alt text
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
        
        return $attachment_id;
    }
    
    /**
     * Set featured image for post
     */
    public function set_featured_image($post_id, $location_name, $storm_type = 'snow') {
        $image_data = $this->get_featured_image($location_name, $storm_type);
        
        if (!isset($image_data['url'])) {
            return false;
        }
        
        $attachment_id = $this->upload_to_media_library(
            $image_data['url'],
            isset($image_data['alt']) ? $image_data['alt'] : '',
            $post_id
        );
        
        if (!$attachment_id) {
            return false;
        }
        
        set_post_thumbnail($post_id, $attachment_id);
        
        // Store photographer credit if from Unsplash
        if (isset($image_data['source']) && $image_data['source'] === 'unsplash') {
            if (isset($image_data['photographer'])) {
                update_post_meta($post_id, '_image_photographer', $image_data['photographer']);
            }
            if (isset($image_data['photographer_url'])) {
                update_post_meta($post_id, '_image_photographer_url', $image_data['photographer_url']);
            }
        }
        
        return $attachment_id;
    }
}
