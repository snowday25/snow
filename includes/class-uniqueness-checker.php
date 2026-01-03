<?php
/**
 * Uniqueness Checker Class
 * 
 * Handles checking uniqueness of titles and content
 *
 * @package Snow_Alerts
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Uniqueness_Checker {
    
    /**
     * Check if a title is unique
     *
     * @param string $title Title to check
     * @return bool True if unique, false if exists
     */
    public static function is_title_unique($title) {
        global $wpdb;
        
        // Check in posts table
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_status != 'trash' LIMIT 1",
                $title
            )
        );
        
        return empty($existing);
    }
    
    /**
     * Check if content is sufficiently unique
     *
     * @param string $content Content to check
     * @param int $similarity_threshold Similarity threshold (0-100)
     * @return bool True if unique enough, false if too similar
     */
    public static function is_content_unique($content, $similarity_threshold = 85) {
        global $wpdb;
        
        // Get recent posts (last 100)
        $recent_posts = $wpdb->get_results(
            "SELECT post_content FROM {$wpdb->posts} 
             WHERE post_status = 'publish' 
             AND post_type = 'post' 
             ORDER BY post_date DESC 
             LIMIT 100"
        );
        
        if (empty($recent_posts)) {
            return true;
        }
        
        // Check similarity with each recent post
        foreach ($recent_posts as $post) {
            if (empty($post->post_content)) {
                continue;
            }
            
            $similarity = self::calculate_similarity($content, $post->post_content);
            
            if ($similarity >= $similarity_threshold) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Calculate similarity between two strings
     *
     * @param string $str1 First string
     * @param string $str2 Second string
     * @return float Similarity percentage (0-100)
     */
    private static function calculate_similarity($str1, $str2) {
        // Strip HTML tags
        $str1 = strip_tags($str1);
        $str2 = strip_tags($str2);
        
        // Convert to lowercase
        $str1 = strtolower($str1);
        $str2 = strtolower($str2);
        
        // Use similar_text for basic similarity
        similar_text($str1, $str2, $percent);
        
        return $percent;
    }
    
    /**
     * Get similar existing titles
     *
     * @param string $title Title to check
     * @param int $limit Number of similar titles to return
     * @return array Array of similar titles
     */
    public static function get_similar_titles($title, $limit = 5) {
        global $wpdb;
        
        // Get posts with similar titles using LIKE
        $search_term = '%' . $wpdb->esc_like($title) . '%';
        
        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT post_title FROM {$wpdb->posts} 
                 WHERE post_title LIKE %s 
                 AND post_status != 'trash' 
                 ORDER BY post_date DESC 
                 LIMIT %d",
                $search_term,
                $limit
            )
        );
        
        return $results;
    }
}
