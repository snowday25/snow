<?php
/**
 * Template Manager
 * 
 * Manages article templates, title variations, and description variations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Template_Manager {
    
    /**
     * @var array Cached templates
     */
    private $templates_cache = null;
    private $title_variations_cache = null;
    private $description_variations_cache = null;
    
    /**
     * Load article templates
     */
    public function load_templates() {
        if ($this->templates_cache !== null) {
            return $this->templates_cache;
        }
        
        $json_file = SNOW_ALERTS_PLUGIN_DIR . 'data/article-templates.json';
        
        if (!file_exists($json_file)) {
            return $this->get_default_templates();
        }
        
        $json_content = file_get_contents($json_file);
        $templates = json_decode($json_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($templates)) {
            return $this->get_default_templates();
        }
        
        $this->templates_cache = $templates;
        return $templates;
    }
    
    /**
     * Load title variations
     */
    public function load_title_variations() {
        if ($this->title_variations_cache !== null) {
            return $this->title_variations_cache;
        }
        
        $json_file = SNOW_ALERTS_PLUGIN_DIR . 'data/title-variations.json';
        
        if (!file_exists($json_file)) {
            return $this->get_default_title_variations();
        }
        
        $json_content = file_get_contents($json_file);
        $variations = json_decode($json_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($variations)) {
            return $this->get_default_title_variations();
        }
        
        $this->title_variations_cache = $variations;
        return $variations;
    }
    
    /**
     * Load description variations
     */
    public function load_description_variations() {
        if ($this->description_variations_cache !== null) {
            return $this->description_variations_cache;
        }
        
        $json_file = SNOW_ALERTS_PLUGIN_DIR . 'data/description-variations.json';
        
        if (!file_exists($json_file)) {
            return $this->get_default_description_variations();
        }
        
        $json_content = file_get_contents($json_file);
        $variations = json_decode($json_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($variations)) {
            return $this->get_default_description_variations();
        }
        
        $this->description_variations_cache = $variations;
        return $variations;
    }
    
    /**
     * Get default templates
     */
    private function get_default_templates() {
        return array(
            'blizzard' => array(
                'titles' => array(
                    '{location} Braces for Major Blizzard: {inches}" of Snow Expected',
                    'Blizzard Warning Issued for {location} - {inches}" Snow Forecast',
                    'Major Snowstorm Threatens {location} with {inches}" Accumulation',
                    'Winter Storm Alert: {location} Could See {inches}" of Snow',
                    '{location} Residents Prepare as Blizzard Approaches',
                ),
                'intro' => 'A major blizzard is bearing down on {location}, with forecasters predicting {inches} inches of snow and dangerous conditions.',
                'keywords' => array('blizzard', 'winter storm', 'snow emergency', 'travel advisory'),
            ),
            'heavy_snow' => array(
                'titles' => array(
                    'Heavy Snow Warning for {location}: {inches}" Expected',
                    '{location} Forecast: {inches}" of Snow on the Way',
                    'Winter Weather Advisory: {location} to See {inches}" Snow',
                    'Significant Snowfall Expected in {location} - {inches}" Forecast',
                    '{location} Snow Alert: {inches}" Accumulation Likely',
                ),
                'intro' => '{location} is under a heavy snow warning as {inches} inches of accumulation are expected over the next 24-48 hours.',
                'keywords' => array('heavy snow', 'winter weather', 'snow advisory', 'accumulation'),
            ),
            'lake_effect' => array(
                'titles' => array(
                    'Lake Effect Snow Hits {location}: {inches}" Possible',
                    '{location} Faces Lake Effect Snow Event - {inches}" Expected',
                    'Intense Lake Effect Snow Targets {location}',
                    '{location} Under Lake Effect Snow Warning',
                    'Lake Effect Snowstorm Impacts {location} Region',
                ),
                'intro' => 'Lake effect snow is pounding {location}, with localized bands potentially dropping {inches} inches in some areas.',
                'keywords' => array('lake effect', 'snow bands', 'localized snow', 'heavy accumulation'),
            ),
            'nor_easter' => array(
                'titles' => array(
                    "Nor'easter Targets {location} with {inches}\" of Snow",
                    '{location} Prepares for Major Nor\'easter',
                    'Powerful Nor\'easter to Dump {inches}" on {location}',
                    '{location} Braces for Nor\'easter Impact',
                    'Winter Nor\'easter Threatens {location}',
                ),
                'intro' => 'A powerful nor\'easter is moving toward {location}, bringing the potential for {inches} inches of snow and strong winds.',
                'keywords' => array('nor\'easter', 'coastal storm', 'winter storm', 'blizzard conditions'),
            ),
            'general_snow' => array(
                'titles' => array(
                    '{location} Snow Forecast: {inches}" on the Way',
                    'Winter Weather Coming to {location}',
                    '{location} Set for Significant Snowfall',
                    'Snow Alert for {location}: {inches}" Expected',
                    '{location} Weather: Major Snow Event Approaching',
                ),
                'intro' => 'Winter weather is headed to {location}, with forecasters calling for {inches} inches of snow.',
                'keywords' => array('snow forecast', 'winter weather', 'snow event', 'weather alert'),
            ),
        );
    }
    
    /**
     * Get default title variations
     */
    private function get_default_title_variations() {
        return array(
            'action_verbs' => array('Braces', 'Prepares', 'Faces', 'Expects', 'Anticipates', 'Readies'),
            'intensity' => array('Major', 'Significant', 'Heavy', 'Intense', 'Severe', 'Dangerous'),
            'time_frames' => array('This Weekend', 'Tonight', 'Tomorrow', 'This Week', 'Incoming'),
            'impacts' => array('Travel Hazardous', 'Schools Close', 'Emergency Declared', 'Roads Treacherous'),
        );
    }
    
    /**
     * Get default description variations
     */
    private function get_default_description_variations() {
        return array(
            'urgent' => '{location} is bracing for a major winter storm. Get the latest forecast, safety tips, and travel updates.',
            'informative' => 'Complete snow forecast for {location} including accumulation totals, timing, and what to expect.',
            'local' => 'Local {location} weather alert: {inches}" of snow expected. Stay informed with our comprehensive coverage.',
            'safety' => '{location} residents should prepare now. Critical snow safety information and emergency resources.',
            'update' => 'Updated {location} snow forecast: Latest models show {inches}" possible. Live updates and alerts.',
        );
    }
    
    /**
     * Get template for storm type
     */
    public function get_template($storm_type) {
        $templates = $this->load_templates();
        
        if (isset($templates[$storm_type])) {
            return $templates[$storm_type];
        }
        
        // Default to general_snow
        return isset($templates['general_snow']) ? $templates['general_snow'] : array();
    }
    
    /**
     * Generate title from template
     */
    public function generate_title($location_name, $storm_type = 'general_snow', $snow_amount = 0) {
        $template = $this->get_template($storm_type);
        
        if (!isset($template['titles']) || !is_array($template['titles']) || empty($template['titles'])) {
            return $location_name . ' Snow Alert: ' . $snow_amount . '" Expected';
        }
        
        $title_template = $template['titles'][array_rand($template['titles'])];
        
        // Replace placeholders
        $title = str_replace('{location}', $location_name, $title_template);
        $title = str_replace('{inches}', number_format($snow_amount, 1), $title);
        
        return $title;
    }
    
    /**
     * Generate description from template
     */
    public function generate_description($location_name, $snow_amount = 0) {
        $variations = $this->load_description_variations();
        
        if (empty($variations)) {
            return 'Snow forecast for ' . $location_name;
        }
        
        $desc_template = $variations[array_rand($variations)];
        
        // Replace placeholders
        $description = str_replace('{location}', $location_name, $desc_template);
        $description = str_replace('{inches}', number_format($snow_amount, 1), $description);
        
        return $description;
    }
    
    /**
     * Detect storm type from weather data
     */
    public function detect_storm_type($weather_data, $location) {
        $snow_amount = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        $state = isset($location['state']) ? $location['state'] : '';
        
        // Blizzard (12+ inches)
        if ($snow_amount >= 12) {
            return 'blizzard';
        }
        
        // Lake effect (Great Lakes states)
        $lake_states = array('MI', 'NY', 'OH', 'PA', 'WI', 'IN');
        if (in_array($state, $lake_states) && $snow_amount >= 6) {
            return 'lake_effect';
        }
        
        // Nor'easter (East Coast)
        $east_coast = array('ME', 'NH', 'VT', 'MA', 'RI', 'CT', 'NY', 'NJ', 'PA', 'DE', 'MD');
        if (in_array($state, $east_coast) && $snow_amount >= 8) {
            return 'nor_easter';
        }
        
        // Heavy snow (6+ inches)
        if ($snow_amount >= 6) {
            return 'heavy_snow';
        }
        
        // General snow
        return 'general_snow';
    }
}
