<?php
/**
 * Headline Optimizer
 * 
 * Optimizes headlines with power words and emotion triggers
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Headline_Optimizer {
    
    /**
     * Power words for headlines
     */
    private $power_words = array(
        'Breaking', 'Alert', 'Warning', 'Urgent', 'Critical', 'Major', 'Severe', 
        'Dangerous', 'Intense', 'Massive', 'Historic', 'Unprecedented', 'Extreme',
        'Emergency', 'Important'
    );
    
    /**
     * Emotion triggers
     */
    private $emotions = array(
        'fear' => array('Dangerous', 'Warning', 'Critical', 'Emergency'),
        'urgency' => array('Breaking', 'Alert', 'Urgent', 'Now'),
        'curiosity' => array('What', 'How', 'Why', 'When'),
        'safety' => array('Prepare', 'Protect', 'Stay Safe', 'Be Ready'),
        'authority' => array('Experts', 'Officials', 'Meteorologists'),
        'local' => array('Your Area', 'Local', 'Nearby', 'Regional'),
        'timing' => array('Tonight', 'Tomorrow', 'This Weekend', 'Incoming'),
        'magnitude' => array('Major', 'Massive', 'Significant', 'Historic'),
    );
    
    /**
     * Optimize headline for CTR
     */
    public function optimize($title, $location_name, $snow_amount) {
        // Add power word if not present
        $has_power_word = false;
        
        foreach ($this->power_words as $word) {
            if (stripos($title, $word) !== false) {
                $has_power_word = true;
                break;
            }
        }
        
        if (!$has_power_word && mt_rand(0, 1)) {
            $power_word = $this->power_words[array_rand($this->power_words)];
            $title = $power_word . ': ' . $title;
        }
        
        // Ensure location is prominent
        if (stripos($title, $location_name) === false) {
            $title = $location_name . ' - ' . $title;
        }
        
        // Ensure snow amount is mentioned
        if ($snow_amount > 0 && !preg_match('/\d+/', $title)) {
            $title .= ' - ' . number_format($snow_amount, 0) . '" Expected';
        }
        
        return $title;
    }
    
    /**
     * Generate meta description
     */
    public function generate_meta_description($location_name, $snow_amount, $format = 'standard') {
        $templates = array(
            'urgent' => '{location} is bracing for {inches}" of snow. Get the latest forecast, safety tips, and travel updates.',
            'informative' => 'Complete snow forecast for {location}: {inches}" expected. Timing, impacts, and what you need to know.',
            'local' => 'Local {location} weather alert: {inches}" of snow forecast. Stay informed with comprehensive coverage.',
            'safety' => '{location} snow alert: {inches}" expected. Essential preparation and safety information inside.',
        );
        
        $template = isset($templates[$format]) ? $templates[$format] : $templates['informative'];
        
        $description = str_replace('{location}', $location_name, $template);
        $description = str_replace('{inches}', number_format($snow_amount, 0), $description);
        
        // Ensure under 160 characters
        if (strlen($description) > 160) {
            $description = substr($description, 0, 157) . '...';
        }
        
        return $description;
    }
    
    /**
     * Get emotion-based variation
     */
    public function get_emotion_variation($emotion_type = 'urgency') {
        if (!isset($this->emotions[$emotion_type])) {
            $emotion_type = 'urgency';
        }
        
        $words = $this->emotions[$emotion_type];
        return $words[array_rand($words)];
    }
}
