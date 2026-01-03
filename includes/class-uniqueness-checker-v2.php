<?php
/**
 * Advanced Uniqueness Checker V2
 * 
 * Provides comprehensive duplicate detection using:
 * - Fuzzy title matching
 * - N-gram content analysis
 * - Jaccard similarity coefficient
 * - Hash-based quick lookups
 * - Weather update detection
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Uniqueness_Checker_V2 {
    
    /**
     * Similarity threshold for fuzzy title matching (85%)
     */
    const TITLE_SIMILARITY_THRESHOLD = 0.85;
    
    /**
     * Similarity threshold for content matching (70%)
     */
    const CONTENT_SIMILARITY_THRESHOLD = 0.70;
    
    /**
     * N-gram size for content analysis
     */
    const NGRAM_SIZE = 3;
    
    /**
     * Time window for checking updates (hours)
     */
    const UPDATE_CHECK_WINDOW = 48;
    
    /**
     * Common stop words to remove for better comparison
     */
    private $stop_words = array(
        'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
        'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'been',
        'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
        'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that',
        'these', 'those', 'it', 'its', 'you', 'your', 'we', 'our'
    );
    
    /**
     * Check if title is unique
     * 
     * @param string $title The title to check
     * @param string $location_name Location name
     * @param string $check_level Level of checking: 'quick', 'standard', 'deep'
     * @return bool True if unique, false if duplicate
     */
    public function is_title_unique($title, $location_name, $check_level = 'standard') {
        global $wpdb;
        
        // Generate hash for quick lookup
        $title_hash = md5(strtolower(trim($title)));
        
        // Quick hash check
        $table_name = $wpdb->prefix . 'snow_alerts_articles';
        $hash_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE title_hash = %s",
            $title_hash
        ));
        
        if ($hash_exists > 0) {
            $this->log_uniqueness_check('Title hash exists', $title, $location_name);
            return false;
        }
        
        if ($check_level === 'quick') {
            return true;
        }
        
        // Fuzzy matching against recent titles
        $recent_posts = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id FROM $table_name 
             WHERE location_name = %s 
             ORDER BY published_date DESC 
             LIMIT 20",
            $location_name
        ), ARRAY_A);
        
        if (!$recent_posts) {
            return true;
        }
        
        foreach ($recent_posts as $row) {
            if (!isset($row['post_id'])) {
                continue;
            }
            
            $post_id = $row['post_id'];
            $existing_title = get_the_title($post_id);
            
            if (!$existing_title) {
                continue;
            }
            
            $similarity = $this->calculate_title_similarity($title, $existing_title);
            
            if ($similarity >= self::TITLE_SIMILARITY_THRESHOLD) {
                $this->log_uniqueness_check(
                    sprintf('Title too similar (%.2f)', $similarity),
                    $title,
                    $location_name
                );
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if content is unique
     * 
     * @param string $content Content to check
     * @param string $location_name Location name
     * @param float $threshold Similarity threshold (default 0.70)
     * @return bool True if unique, false if duplicate
     */
    public function is_content_unique($content, $location_name, $threshold = null) {
        global $wpdb;
        
        if ($threshold === null) {
            $threshold = self::CONTENT_SIMILARITY_THRESHOLD;
        }
        
        // Generate hash for quick lookup
        $content_hash = md5(strtolower(strip_tags($content)));
        
        // Quick hash check
        $table_name = $wpdb->prefix . 'snow_alerts_articles';
        $hash_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE content_hash = %s",
            $content_hash
        ));
        
        if ($hash_exists > 0) {
            $this->log_uniqueness_check('Content hash exists', 'N/A', $location_name);
            return false;
        }
        
        // Get recent posts for comparison
        $recent_posts = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id FROM $table_name 
             WHERE location_name = %s 
             ORDER BY published_date DESC 
             LIMIT 10",
            $location_name
        ), ARRAY_A);
        
        if (!$recent_posts) {
            return true;
        }
        
        // Generate n-grams for new content
        $new_ngrams = $this->generate_ngrams($content, self::NGRAM_SIZE);
        
        foreach ($recent_posts as $row) {
            if (!isset($row['post_id'])) {
                continue;
            }
            
            $post_id = $row['post_id'];
            $existing_content = get_post_field('post_content', $post_id);
            
            if (!$existing_content) {
                continue;
            }
            
            // Generate n-grams for existing content
            $existing_ngrams = $this->generate_ngrams($existing_content, self::NGRAM_SIZE);
            
            // Calculate Jaccard similarity
            $similarity = $this->calculate_jaccard_similarity($new_ngrams, $existing_ngrams);
            
            if ($similarity >= $threshold) {
                $this->log_uniqueness_check(
                    sprintf('Content too similar (%.2f)', $similarity),
                    'N/A',
                    $location_name
                );
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check for weather update
     * 
     * @param string $location_name Location name
     * @param string $state State abbreviation
     * @param array $new_weather_data New weather data
     * @return array|false Update info or false if no previous article
     */
    public function check_for_weather_update($location_name, $state, $new_weather_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'snow_alerts_articles';
        
        // Check for articles in the last 24-48 hours
        $hours_ago = self::UPDATE_CHECK_WINDOW;
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-$hours_ago hours"));
        
        $previous_article = $wpdb->get_row($wpdb->prepare(
            "SELECT post_id, weather_data, published_date 
             FROM $table_name 
             WHERE location_name = %s 
             AND state = %s 
             AND published_date >= %s 
             ORDER BY published_date DESC 
             LIMIT 1",
            $location_name,
            $state,
            $cutoff_date
        ), ARRAY_A);
        
        if (!$previous_article) {
            return false;
        }
        
        if (!isset($previous_article['post_id']) || !isset($previous_article['weather_data'])) {
            return false;
        }
        
        // Decode previous weather data
        $previous_weather = array();
        if (isset($previous_article['weather_data']) && $previous_article['weather_data']) {
            $decoded = json_decode($previous_article['weather_data'], true);
            if ($decoded !== null) {
                $previous_weather = $decoded;
            }
        }
        
        // Detect significant changes
        $update_type = $this->detect_weather_changes($previous_weather, $new_weather_data);
        
        if ($update_type === false) {
            return false;
        }
        
        return array(
            'has_update' => true,
            'previous_post_id' => $previous_article['post_id'],
            'update_type' => $update_type,
            'previous_weather' => $previous_weather,
            'previous_published' => isset($previous_article['published_date']) ? $previous_article['published_date'] : ''
        );
    }
    
    /**
     * Generate unique title variation
     * 
     * @param string $base_title Base title
     * @param int $attempt Attempt number
     * @param array $update_context Update context (optional)
     * @return string Unique title variation
     */
    public function generate_unique_title_variation($base_title, $attempt, $update_context = null) {
        if ($update_context && isset($update_context['has_update']) && $update_context['has_update']) {
            // Update-specific prefixes
            $prefixes = array(
                'UPDATED: ',
                'Latest Update: ',
                'Breaking Update: ',
                'Weather Update: ',
                'Updated Forecast: ',
                'New Information: ',
                'Update Alert: ',
                'Fresh Update: ',
                'Current Update: ',
                'Developing: '
            );
            
            $prefix_index = $attempt % count($prefixes);
            return $prefixes[$prefix_index] . $base_title;
        }
        
        // Regular variations
        $variations = array(
            '%s: Latest Updates',
            'Breaking: %s',
            '%s - Weather Alert',
            'Alert: %s',
            '%s: What to Expect',
            '%s - Current Conditions',
            'Weather Watch: %s',
            '%s Alert',
            'Important: %s',
            '%s - Full Forecast'
        );
        
        if ($attempt < count($variations)) {
            return sprintf($variations[$attempt], $base_title);
        }
        
        // Timestamp fallback for ultimate uniqueness
        $timestamp = date('g:i A');
        return sprintf('%s (Updated %s)', $base_title, $timestamp);
    }
    
    /**
     * Calculate title similarity using Levenshtein distance
     * 
     * @param string $title1 First title
     * @param string $title2 Second title
     * @return float Similarity score (0.0 to 1.0)
     */
    private function calculate_title_similarity($title1, $title2) {
        // Normalize titles
        $title1 = strtolower(trim($title1));
        $title2 = strtolower(trim($title2));
        
        // Remove stop words
        $title1 = $this->remove_stop_words($title1);
        $title2 = $this->remove_stop_words($title2);
        
        // Calculate Levenshtein distance
        $lev = levenshtein($title1, $title2);
        $max_len = max(strlen($title1), strlen($title2));
        
        if ($max_len === 0) {
            return 1.0;
        }
        
        return 1.0 - ($lev / $max_len);
    }
    
    /**
     * Generate n-grams from text
     * 
     * @param string $text Text to analyze
     * @param int $n N-gram size
     * @return array Array of n-grams
     */
    private function generate_ngrams($text, $n) {
        // Normalize text
        $text = strtolower(strip_tags($text));
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Split into words
        $words = explode(' ', trim($text));
        
        // Remove stop words
        $words = array_filter($words, function($word) {
            return !in_array($word, $this->stop_words) && strlen($word) > 2;
        });
        
        $words = array_values($words);
        
        // Generate n-grams
        $ngrams = array();
        $count = count($words);
        
        for ($i = 0; $i <= $count - $n; $i++) {
            $ngram = implode(' ', array_slice($words, $i, $n));
            $ngrams[] = $ngram;
        }
        
        return array_unique($ngrams);
    }
    
    /**
     * Calculate Jaccard similarity coefficient
     * 
     * @param array $set1 First set
     * @param array $set2 Second set
     * @return float Similarity score (0.0 to 1.0)
     */
    private function calculate_jaccard_similarity($set1, $set2) {
        if (empty($set1) && empty($set2)) {
            return 1.0;
        }
        
        if (empty($set1) || empty($set2)) {
            return 0.0;
        }
        
        $intersection = array_intersect($set1, $set2);
        $union = array_unique(array_merge($set1, $set2));
        
        return count($intersection) / count($union);
    }
    
    /**
     * Remove stop words from text
     * 
     * @param string $text Text to process
     * @return string Text without stop words
     */
    private function remove_stop_words($text) {
        $words = explode(' ', $text);
        $filtered = array();
        
        foreach ($words as $word) {
            if (!in_array(strtolower($word), $this->stop_words)) {
                $filtered[] = $word;
            }
        }
        
        return implode(' ', $filtered);
    }
    
    /**
     * Detect weather changes
     * 
     * @param array $previous_weather Previous weather data
     * @param array $new_weather New weather data
     * @return string|false Update type or false if no significant change
     */
    private function detect_weather_changes($previous_weather, $new_weather) {
        // Check snow amount increase
        $prev_snow = isset($previous_weather['snow_amount']) ? floatval($previous_weather['snow_amount']) : 0;
        $new_snow = isset($new_weather['snow_amount']) ? floatval($new_weather['snow_amount']) : 0;
        
        if ($new_snow > $prev_snow * 1.2) {
            return 'increased_snow';
        }
        
        // Check alert type upgrade
        $prev_alert = isset($previous_weather['alert_type']) ? $previous_weather['alert_type'] : '';
        $new_alert = isset($new_weather['alert_type']) ? $new_weather['alert_type'] : '';
        
        $alert_levels = array('advisory' => 1, 'watch' => 2, 'warning' => 3, 'emergency' => 4);
        
        $prev_level = isset($alert_levels[$prev_alert]) ? $alert_levels[$prev_alert] : 0;
        $new_level = isset($alert_levels[$new_alert]) ? $alert_levels[$new_alert] : 0;
        
        if ($new_level > $prev_level) {
            return 'alert_upgrade';
        }
        
        // Check timing changes
        $prev_start = isset($previous_weather['start_time']) ? $previous_weather['start_time'] : '';
        $new_start = isset($new_weather['start_time']) ? $new_weather['start_time'] : '';
        
        if ($prev_start && $new_start && $prev_start !== $new_start) {
            return 'forecast_update';
        }
        
        // No significant change
        return false;
    }
    
    /**
     * Log uniqueness check failure
     * 
     * @param string $reason Failure reason
     * @param string $title Title checked
     * @param string $location Location name
     */
    private function log_uniqueness_check($reason, $title, $location) {
        if (function_exists('error_log')) {
            error_log(sprintf(
                '[Snow Alerts] Uniqueness check failed: %s | Location: %s | Title: %s',
                $reason,
                $location,
                substr($title, 0, 50)
            ));
        }
    }
}
