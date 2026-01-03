<?php
/**
 * Headline Optimizer Class
 * 
 * Optimizes headlines and meta descriptions for maximum CTR
 * using power words, emotion triggers, and best practices.
 */

class Snow_Alerts_Headline_Optimizer {
    
    /**
     * Power words for different severity levels
     */
    private static $power_words = array(
        'severe' => array('Extreme', 'Crippling', 'Historic', 'Devastating', 'Critical'),
        'moderate' => array('Major', 'Significant', 'Powerful', 'Massive', 'Dangerous'),
        'light' => array('Notable', 'Developing', 'Approaching', 'Breaking', 'Alert'),
    );
    
    /**
     * Emotion triggers
     */
    private static $emotion_triggers = array(
        'Brace Yourself',
        'Get Ready',
        'Prepare Now',
        'Stay Safe',
        'Be Aware',
        'Take Action',
        'Don\'t Miss',
        'What You Need to Know',
    );
    
    /**
     * Headline structure templates
     */
    private static $headline_structures = array(
        '{power} {location}: {event} - {trigger}',
        '{trigger}: {power} {event} Hits {location}',
        '{location} Faces {power} {event} - {trigger}',
        '{power} {event} Alert for {location} - {trigger}',
    );
    
    /**
     * Meta description templates
     */
    private static $meta_description_templates = array(
        'urgency' => '{location} residents urged to prepare as {event} approaches. {details} Stay informed with latest updates.',
        'specific' => '{amount} inches of snow forecast for {location}, {state}. {event} expected to impact the area. {details}',
        'question' => 'Will {location} see major snowfall? {event} brings {details} Get the latest forecast and safety tips.',
        'local' => '{location}, {state}: {event} to impact the region. {details} Residents should prepare now.',
    );
    
    /**
     * Optimize headline for maximum CTR
     */
    public static function optimize_headline($base_headline, $location_data, $severity) {
        // Validate severity
        if (!isset(self::$power_words[$severity])) {
            $severity = 'moderate';
        }
        
        // Get random power word for severity level
        $power_words = self::$power_words[$severity];
        $power_word = $power_words[array_rand($power_words)];
        
        // Get random emotion trigger
        $trigger = self::$emotion_triggers[array_rand(self::$emotion_triggers)];
        
        // Get location name
        $location = isset($location_data['city']) ? $location_data['city'] : 'Area';
        
        // Extract event from base headline (e.g., "Winter Storm", "Snow Alert")
        $event = self::extract_event($base_headline);
        
        // Choose random headline structure
        $structure = self::$headline_structures[array_rand(self::$headline_structures)];
        
        // Build headline
        $headline = str_replace(
            array('{power}', '{location}', '{event}', '{trigger}'),
            array($power_word, $location, $event, $trigger),
            $structure
        );
        
        // Optimize length for mobile (50-60 characters ideal)
        $headline = self::optimize_length($headline, 60);
        
        return $headline;
    }
    
    /**
     * Extract event type from headline
     */
    private static function extract_event($headline) {
        // Common event patterns
        $patterns = array(
            '/winter storm/i' => 'Winter Storm',
            '/snowstorm/i' => 'Snowstorm',
            '/blizzard/i' => 'Blizzard',
            '/snow alert/i' => 'Snow Alert',
            '/snow/i' => 'Snow Event',
        );
        
        foreach ($patterns as $pattern => $event) {
            if (preg_match($pattern, $headline)) {
                return $event;
            }
        }
        
        return 'Weather Alert';
    }
    
    /**
     * Create optimized meta description
     */
    public static function create_meta_description($location_data, $weather_data, $template_type) {
        // Validate template type
        if (!isset(self::$meta_description_templates[$template_type])) {
            $template_type = 'urgency';
        }
        
        $template = self::$meta_description_templates[$template_type];
        
        // Get location information
        $location = isset($location_data['city']) ? $location_data['city'] : 'the area';
        $state = isset($location_data['state']) ? $location_data['state'] : '';
        
        // Get weather details
        $event = isset($weather_data['event_type']) ? $weather_data['event_type'] : 'winter storm';
        $amount = isset($weather_data['snow_amount']) ? $weather_data['snow_amount'] : '6-12';
        $details = isset($weather_data['details']) ? $weather_data['details'] : 'Significant snowfall expected';
        
        // Build meta description
        $description = str_replace(
            array('{location}', '{state}', '{event}', '{amount}', '{details}'),
            array($location, $state, $event, $amount, $details),
            $template
        );
        
        // Optimize length (150-160 characters for Google)
        $description = self::optimize_length($description, 160);
        
        return $description;
    }
    
    /**
     * Optimize text length
     */
    private static function optimize_length($text, $max_length) {
        if (strlen($text) <= $max_length) {
            return $text;
        }
        
        // Truncate at word boundary
        $text = substr($text, 0, $max_length - 3);
        $last_space = strrpos($text, ' ');
        
        if ($last_space !== false) {
            $text = substr($text, 0, $last_space);
        }
        
        return $text . '...';
    }
    
    /**
     * Determine severity from weather data
     */
    public static function determine_severity($weather_data) {
        $snow_amount = self::extract_snow_amount($weather_data);
        
        if ($snow_amount >= 12) {
            return 'severe';
        }
        
        if ($snow_amount >= 6) {
            return 'moderate';
        }
        
        return 'light';
    }
    
    /**
     * Extract snow amount from weather data
     */
    private static function extract_snow_amount($weather_data) {
        // Try to get snow amount from data
        if (isset($weather_data['snow_amount'])) {
            $amount = $weather_data['snow_amount'];
            
            // Extract numeric value
            if (is_numeric($amount)) {
                return floatval($amount);
            }
            
            // Parse string like "6-12 inches"
            if (preg_match('/(\d+)/', $amount, $matches)) {
                return intval($matches[1]);
            }
        }
        
        // Default to moderate severity
        return 6;
    }
    
    /**
     * Add power word to existing headline
     */
    public static function add_power_word($headline, $severity) {
        if (!isset(self::$power_words[$severity])) {
            $severity = 'moderate';
        }
        
        $power_words = self::$power_words[$severity];
        $power_word = $power_words[array_rand($power_words)];
        
        // Add power word at the beginning
        return $power_word . ' ' . $headline;
    }
    
    /**
     * Get all power words
     */
    public static function get_power_words() {
        return self::$power_words;
    }
    
    /**
     * Get all emotion triggers
     */
    public static function get_emotion_triggers() {
        return self::$emotion_triggers;
    }
}
