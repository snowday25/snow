<?php
/**
 * Uniqueness Checker Class
 *
 * Prevents duplicate content generation
 *
 * @package Snow_Alerts
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Uniqueness_Checker {
    
    /**
     * Check if title is duplicate
     *
     * @param string $title Title to check
     * @return bool True if duplicate
     */
    public static function is_duplicate_title($title) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_alerts_articles';
        $title_hash = hash('sha256', $title);
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE title_hash = %s",
            $title_hash
        ));
        
        return $count > 0;
    }
    
    /**
     * Check if content is duplicate or too similar
     *
     * @param string $content Content to check
     * @return bool True if duplicate
     */
    public static function is_duplicate_content($content) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_alerts_articles';
        $content_hash = hash('sha256', $content);
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE content_hash = %s",
            $content_hash
        ));
        
        return $count > 0;
    }
    
    /**
     * Check if city had recent article
     *
     * @param string $city City name
     * @param string $state State abbreviation
     * @param int $hours Hours to check back
     * @return bool True if recent article exists
     */
    public static function has_recent_article($city, $state, $hours = 24) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_alerts_articles';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE city = %s AND state = %s AND created_at >= %s",
            $city,
            $state,
            date('Y-m-d H:i:s', strtotime("-{$hours} hours"))
        ));
        
        return $count > 0;
    }
    
    /**
     * Get similarity percentage between two texts (simple version)
     *
     * @param string $text1 First text
     * @param string $text2 Second text
     * @return float Similarity percentage
     */
    public static function get_similarity($text1, $text2) {
        similar_text($text1, $text2, $percent);
        return $percent;
    }
}
