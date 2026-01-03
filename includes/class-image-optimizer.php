<?php
/**
 * Image Optimizer Class
 * 
 * Handles automatic featured image generation and optimization
 * for Google Discover and social media sharing.
 */

class Snow_Alerts_Image_Optimizer {
    
    /**
     * Unsplash API endpoint
     */
    private static $unsplash_api_url = 'https://api.unsplash.com/search/photos';
    
    /**
     * Generate and set featured image for post
     */
    public static function generate_featured_image($post_id, $location_data, $weather_data) {
        // Check if post already has featured image
        if (has_post_thumbnail($post_id)) {
            return get_post_thumbnail_id($post_id);
        }
        
        $image_id = null;
        
        // Try Unsplash API first
        $unsplash_key = get_option('snow_alerts_unsplash_api_key', '');
        if (!empty($unsplash_key)) {
            $image_id = self::fetch_from_unsplash($post_id, $location_data, $unsplash_key);
        }
        
        // Fall back to placeholder if Unsplash failed
        if (!$image_id) {
            $image_id = self::generate_placeholder_image($post_id, $location_data);
        }
        
        // Set as featured image
        if ($image_id) {
            set_post_thumbnail($post_id, $image_id);
            return $image_id;
        }
        
        return false;
    }
    
    /**
     * Fetch image from Unsplash API
     */
    private static function fetch_from_unsplash($post_id, $location_data, $api_key) {
        // Sanitize city name before using in query
        $city_name = isset($location_data['city']) ? sanitize_text_field($location_data['city']) : 'winter';
        $query = 'winter storm snow ' . $city_name;
        
        $url = add_query_arg(array(
            'query' => $query,
            'orientation' => 'landscape',
            'per_page' => 1,
        ), self::$unsplash_api_url);
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Client-ID ' . $api_key,
            ),
            'timeout' => 15,
        ));
        
        if (is_wp_error($response)) {
            error_log('Snow Alerts: Unsplash API error - ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['results']) || !is_array($data['results']) || empty($data['results'])) {
            error_log('Snow Alerts: No Unsplash images found for query: ' . $query);
            return false;
        }
        
        $photo = $data['results'][0];
        $image_url = isset($photo['urls']['regular']) ? $photo['urls']['regular'] : null;
        
        if (!$image_url) {
            return false;
        }
        
        // Download and attach image
        $image_id = self::download_and_attach_image($image_url, $post_id, $location_data);
        
        return $image_id;
    }
    
    /**
     * Generate placeholder image
     */
    private static function generate_placeholder_image($post_id, $location_data) {
        $city_name = isset($location_data['city']) ? $location_data['city'] : 'Location';
        $state_name = isset($location_data['state']) ? $location_data['state'] : '';
        
        $text = $city_name;
        if (!empty($state_name)) {
            $text .= ', ' . $state_name;
        }
        
        // Use via.placeholder.com for placeholder images
        $width = 1200;
        $height = 630;
        $bg_color = '1e3a8a'; // Dark blue (winter theme)
        $text_color = 'ffffff'; // White
        
        $url = sprintf(
            'https://via.placeholder.com/%dx%d/%s/%s?text=%s',
            $width,
            $height,
            $bg_color,
            $text_color,
            urlencode('Winter Storm - ' . $text)
        );
        
        // Download and attach image
        $image_id = self::download_and_attach_image($url, $post_id, $location_data);
        
        return $image_id;
    }
    
    /**
     * Download image and attach to post
     */
    private static function download_and_attach_image($image_url, $post_id, $location_data) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        
        // Download image to temp file
        $temp_file = download_url($image_url);
        
        if (is_wp_error($temp_file)) {
            error_log('Snow Alerts: Image download failed - ' . $temp_file->get_error_message());
            return false;
        }
        
        // Generate filename
        $city_name = isset($location_data['city']) ? sanitize_title($location_data['city']) : 'winter-storm';
        $filename = $city_name . '-' . time() . '.jpg';
        
        // Prepare file array
        $file_array = array(
            'name' => $filename,
            'tmp_name' => $temp_file,
        );
        
        // Upload to media library
        $image_id = media_handle_sideload($file_array, $post_id);
        
        // Clean up temp file
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
        
        if (is_wp_error($image_id)) {
            error_log('Snow Alerts: Image upload failed - ' . $image_id->get_error_message());
            return false;
        }
        
        // Set image alt text and title
        $city = isset($location_data['city']) ? $location_data['city'] : 'Location';
        $state = isset($location_data['state']) ? $location_data['state'] : '';
        
        $alt_text = 'Winter storm in ' . $city;
        if (!empty($state)) {
            $alt_text .= ', ' . $state;
        }
        
        update_post_meta($image_id, '_wp_attachment_image_alt', $alt_text);
        
        $attachment_data = array(
            'ID' => $image_id,
            'post_title' => $alt_text,
        );
        wp_update_post($attachment_data);
        
        // Store image ID in post meta
        update_post_meta($post_id, '_snow_alerts_featured_image_id', $image_id);
        
        return $image_id;
    }
    
    /**
     * Optimize existing image (optional WebP conversion)
     */
    public static function optimize_image($image_id) {
        // Check if GD library is available
        if (!function_exists('imagecreatefromjpeg')) {
            return false;
        }
        
        $file_path = get_attached_file($image_id);
        if (!$file_path || !file_exists($file_path)) {
            return false;
        }
        
        // Get image info
        $image_info = getimagesize($file_path);
        if (!$image_info) {
            return false;
        }
        
        $mime_type = $image_info['mime'];
        
        // Only process JPEG and PNG
        if ($mime_type !== 'image/jpeg' && $mime_type !== 'image/png') {
            return false;
        }
        
        // Create image resource
        if ($mime_type === 'image/jpeg') {
            $image = imagecreatefromjpeg($file_path);
        } else {
            $image = imagecreatefrompng($file_path);
        }
        
        if (!$image) {
            return false;
        }
        
        // Save optimized version
        $quality = 85; // Good balance between quality and file size
        
        if ($mime_type === 'image/jpeg') {
            imagejpeg($image, $file_path, $quality);
        } else {
            imagepng($image, $file_path, 8); // Compression level 8
        }
        
        imagedestroy($image);
        
        return true;
    }
    
    /**
     * Get optimal image dimensions for Google Discover
     */
    public static function get_optimal_dimensions() {
        return array(
            'width' => 1200,
            'height' => 630,
        );
    }
}
