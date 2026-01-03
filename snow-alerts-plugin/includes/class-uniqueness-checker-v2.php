<?php
/**
 * Advanced Uniqueness Checker V2
 * 
 * Prevents duplicate content with fuzzy matching and n-gram similarity
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Uniqueness_Checker_V2 {
    
    /**
     * @var Snow_Alerts_Database
     */
    private $database;
    
    /**
     * Fuzzy matching threshold (85%)
     */
    private $title_similarity_threshold = 0.85;
    
    /**
     * Content similarity threshold (70%)
     */
    private $content_similarity_threshold = 0.70;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->database = new Snow_Alerts_Database();
    }
    
    /**
     * Check if content is unique
     */
    public function is_unique($title, $content, $location_name = '', $location_state = '') {
        // Quick hash check first
        $title_hash = $this->generate_hash($title);
        $content_hash = $this->generate_hash($content);
        
        $existing = $this->database->get_article_by_hash($title_hash, $content_hash);
        
        if ($existing) {
            return array(
                'unique' => false,
                'reason' => 'exact_match',
                'existing_article_id' => isset($existing['id']) ? $existing['id'] : 0,
            );
        }
        
        // Fuzzy title matching
        $similar_title = $this->find_similar_title($title);
        
        if ($similar_title) {
            return array(
                'unique' => false,
                'reason' => 'similar_title',
                'similarity' => isset($similar_title['similarity']) ? $similar_title['similarity'] : 0,
                'existing_article_id' => isset($similar_title['id']) ? $similar_title['id'] : 0,
            );
        }
        
        // N-gram content similarity
        $similar_content = $this->find_similar_content($content, $location_name, $location_state);
        
        if ($similar_content) {
            return array(
                'unique' => false,
                'reason' => 'similar_content',
                'similarity' => isset($similar_content['similarity']) ? $similar_content['similarity'] : 0,
                'existing_article_id' => isset($similar_content['id']) ? $similar_content['id'] : 0,
            );
        }
        
        return array(
            'unique' => true,
            'title_hash' => $title_hash,
            'content_hash' => $content_hash,
        );
    }
    
    /**
     * Generate hash
     */
    public function generate_hash($text) {
        return hash('sha256', strtolower(trim($text)));
    }
    
    /**
     * Find similar title (fuzzy matching)
     */
    private function find_similar_title($title) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_articles';
        
        // Get recent articles (last 30 days)
        $results = $wpdb->get_results(
            "SELECT id, post_id FROM $table 
             WHERE generated_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
             ORDER BY generated_at DESC
             LIMIT 500",
            ARRAY_A
        );
        
        if (!$results) {
            return null;
        }
        
        $normalized_title = $this->normalize_title($title);
        
        foreach ($results as $article) {
            $post_id = isset($article['post_id']) ? $article['post_id'] : 0;
            $existing_title = get_the_title($post_id);
            
            if (empty($existing_title)) {
                continue;
            }
            
            $normalized_existing = $this->normalize_title($existing_title);
            $similarity = $this->calculate_similarity($normalized_title, $normalized_existing);
            
            if ($similarity >= $this->title_similarity_threshold) {
                return array(
                    'id' => isset($article['id']) ? $article['id'] : 0,
                    'post_id' => $post_id,
                    'title' => $existing_title,
                    'similarity' => $similarity,
                );
            }
        }
        
        return null;
    }
    
    /**
     * Normalize title for comparison
     */
    private function normalize_title($title) {
        // Convert to lowercase
        $title = strtolower($title);
        
        // Remove location names (they can vary)
        $title = preg_replace('/\b[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*,?\s+[A-Z]{2}\b/i', '', $title);
        
        // Remove numbers (snow amounts can vary)
        $title = preg_replace('/\d+(\.\d+)?/', '', $title);
        
        // Remove special characters
        $title = preg_replace('/[^a-z\s]/', '', $title);
        
        // Normalize whitespace
        $title = preg_replace('/\s+/', ' ', $title);
        
        return trim($title);
    }
    
    /**
     * Calculate similarity between two strings
     */
    private function calculate_similarity($str1, $str2) {
        if (empty($str1) || empty($str2)) {
            return 0;
        }
        
        // Levenshtein distance for short strings
        if (strlen($str1) < 100 && strlen($str2) < 100) {
            $lev = levenshtein($str1, $str2);
            $max_len = max(strlen($str1), strlen($str2));
            
            if ($max_len === 0) {
                return 1;
            }
            
            return 1 - ($lev / $max_len);
        }
        
        // Jaccard similarity for longer strings
        $words1 = array_unique(explode(' ', $str1));
        $words2 = array_unique(explode(' ', $str2));
        
        $intersection = count(array_intersect($words1, $words2));
        $union = count(array_unique(array_merge($words1, $words2)));
        
        if ($union === 0) {
            return 0;
        }
        
        return $intersection / $union;
    }
    
    /**
     * Find similar content (n-gram similarity)
     */
    private function find_similar_content($content, $location_name, $location_state) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_articles';
        
        // Get recent articles for same location
        $query = "SELECT id, post_id FROM $table 
                 WHERE location_name = %s 
                 AND location_state = %s 
                 AND generated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                 ORDER BY generated_at DESC
                 LIMIT 50";
        
        $results = $wpdb->get_results(
            $wpdb->prepare($query, $location_name, $location_state),
            ARRAY_A
        );
        
        if (!$results) {
            return null;
        }
        
        $ngrams_new = $this->generate_ngrams($content, 3);
        
        foreach ($results as $article) {
            $post_id = isset($article['post_id']) ? $article['post_id'] : 0;
            $existing_content = get_post_field('post_content', $post_id);
            
            if (empty($existing_content)) {
                continue;
            }
            
            $ngrams_existing = $this->generate_ngrams($existing_content, 3);
            $similarity = $this->calculate_jaccard_similarity($ngrams_new, $ngrams_existing);
            
            if ($similarity >= $this->content_similarity_threshold) {
                return array(
                    'id' => isset($article['id']) ? $article['id'] : 0,
                    'post_id' => $post_id,
                    'similarity' => $similarity,
                );
            }
        }
        
        return null;
    }
    
    /**
     * Generate n-grams
     */
    private function generate_ngrams($text, $n = 3) {
        // Remove HTML tags
        $text = wp_strip_all_tags($text);
        
        // Normalize
        $text = strtolower($text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Split into words
        $words = explode(' ', $text);
        
        $ngrams = array();
        $word_count = count($words);
        
        for ($i = 0; $i <= $word_count - $n; $i++) {
            $ngram = implode(' ', array_slice($words, $i, $n));
            $ngrams[] = $ngram;
        }
        
        return array_unique($ngrams);
    }
    
    /**
     * Calculate Jaccard similarity
     */
    private function calculate_jaccard_similarity($set1, $set2) {
        if (empty($set1) || empty($set2)) {
            return 0;
        }
        
        $intersection = count(array_intersect($set1, $set2));
        $union = count(array_unique(array_merge($set1, $set2)));
        
        if ($union === 0) {
            return 0;
        }
        
        return $intersection / $union;
    }
    
    /**
     * Check for weather change (update detection)
     */
    public function has_weather_changed($location_name, $location_state, $new_snow_amount, $hours = 24) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'snow_articles';
        
        $query = "SELECT snow_amount FROM $table 
                 WHERE location_name = %s 
                 AND location_state = %s 
                 AND generated_at > DATE_SUB(NOW(), INTERVAL %d HOUR)
                 ORDER BY generated_at DESC
                 LIMIT 1";
        
        $existing_amount = $wpdb->get_var(
            $wpdb->prepare($query, $location_name, $location_state, $hours)
        );
        
        if ($existing_amount === null) {
            return true; // No recent article
        }
        
        $diff = abs($new_snow_amount - floatval($existing_amount));
        
        // Significant change is 2+ inches
        return $diff >= 2;
    }
}
