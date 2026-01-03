<?php
/**
 * Template Manager Class
 * 
 * Handles template-based article generation as fallback for AI
 *
 * @package Snow_Alerts
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Template_Manager {
    
    private static $templates = null;
    private static $title_variations = null;
    private static $description_variations = null;
    
    /**
     * Load article templates from JSON file
     *
     * @return array Templates array
     */
    public static function load_templates() {
        if (self::$templates !== null) {
            return self::$templates;
        }
        
        $template_file = SNOW_ALERTS_PLUGIN_DIR . 'data/article-templates.json';
        
        if (file_exists($template_file)) {
            $json = file_get_contents($template_file);
            $templates = json_decode($json, true);
            
            if ($templates && is_array($templates)) {
                self::$templates = $templates;
                return self::$templates;
            }
        }
        
        // Fallback to default templates
        error_log('Snow Alerts: Using fallback templates');
        self::$templates = self::get_default_templates();
        return self::$templates;
    }
    
    /**
     * Load title variations from JSON file
     *
     * @return array Title variations
     */
    public static function load_title_variations() {
        if (self::$title_variations !== null) {
            return self::$title_variations;
        }
        
        $variations_file = SNOW_ALERTS_PLUGIN_DIR . 'data/title-variations.json';
        
        if (file_exists($variations_file)) {
            $json = file_get_contents($variations_file);
            $variations = json_decode($json, true);
            
            if ($variations && is_array($variations)) {
                self::$title_variations = $variations;
                return self::$title_variations;
            }
        }
        
        // Fallback to default variations
        self::$title_variations = self::get_default_title_variations();
        return self::$title_variations;
    }
    
    /**
     * Load description variations from JSON file
     *
     * @return array Description variations
     */
    public static function load_description_variations() {
        if (self::$description_variations !== null) {
            return self::$description_variations;
        }
        
        $descriptions_file = SNOW_ALERTS_PLUGIN_DIR . 'data/description-variations.json';
        
        if (file_exists($descriptions_file)) {
            $json = file_get_contents($descriptions_file);
            $descriptions = json_decode($json, true);
            
            if ($descriptions && is_array($descriptions)) {
                self::$description_variations = $descriptions;
                return self::$description_variations;
            }
        }
        
        // Fallback to default descriptions
        self::$description_variations = self::get_default_description_variations();
        return self::$description_variations;
    }
    
    /**
     * Determine storm type from weather data
     *
     * @param array $weather_data Weather data
     * @return string Storm type identifier
     */
    public static function determine_storm_type($weather_data) {
        $default_type = 'winter_storm_warning';
        
        if (!is_array($weather_data)) {
            return $default_type;
        }
        
        // Check for storm type in event field
        if (isset($weather_data['event'])) {
            $event = strtolower($weather_data['event']);
            
            if (strpos($event, 'blizzard') !== false) {
                return 'blizzard_warning';
            }
            if (strpos($event, 'ice storm') !== false) {
                return 'ice_storm_warning';
            }
            if (strpos($event, 'heavy snow') !== false) {
                return 'heavy_snow_warning';
            }
            if (strpos($event, 'winter weather advisory') !== false) {
                return 'winter_weather_advisory';
            }
            if (strpos($event, 'winter storm') !== false) {
                return 'winter_storm_warning';
            }
        }
        
        // Check snow amount
        $snow_amount = self::calculate_total_snow($weather_data);
        
        if ($snow_amount >= 12) {
            return 'blizzard_warning';
        } elseif ($snow_amount >= 6) {
            return 'heavy_snow_warning';
        } elseif ($snow_amount >= 3) {
            return 'winter_storm_warning';
        }
        
        return 'winter_weather_advisory';
    }
    
    /**
     * Generate article from template
     *
     * @param array $city_data City data
     * @param array $weather_data Weather data
     * @return array|false Article data or false on failure
     */
    public static function generate_from_template($city_data, $weather_data) {
        $templates = self::load_templates();
        $storm_type = self::determine_storm_type($weather_data);
        
        if (!isset($templates[$storm_type])) {
            error_log('Snow Alerts: Storm type not found in templates: ' . $storm_type);
            return false;
        }
        
        $template = $templates[$storm_type];
        
        // Generate unique title
        $title = self::generate_unique_title($template, $city_data, $weather_data);
        
        if (!$title) {
            error_log('Snow Alerts: Failed to generate unique title');
            return false;
        }
        
        // Generate meta description
        $meta_description = self::generate_meta_description($template, $city_data, $weather_data);
        
        // Generate content
        $content = self::generate_content($template, $city_data, $weather_data);
        
        // Generate keywords
        $keywords = self::generate_keywords($city_data, $weather_data);
        
        return array(
            'title' => $title,
            'content' => $content,
            'meta_description' => $meta_description,
            'keywords' => $keywords,
            'storm_type' => $storm_type
        );
    }
    
    /**
     * Generate unique title with variations
     *
     * @param array $template Template data
     * @param array $city_data City data
     * @param array $weather_data Weather data
     * @return string|false Unique title or false
     */
    public static function generate_unique_title($template, $city_data, $weather_data) {
        $max_attempts = 50;
        $attempt = 0;
        
        while ($attempt < $max_attempts) {
            // Choose a random title template
            if (isset($template['titles']) && is_array($template['titles']) && count($template['titles']) > 0) {
                $title_template = $template['titles'][array_rand($template['titles'])];
            } else {
                $title_template = '{city}, {state}: {weather_event} Expected {date}';
            }
            
            // Apply variations if attempt > 0
            if ($attempt > 0) {
                $title_template = self::apply_title_variations($title_template);
            }
            
            // Replace placeholders
            $title = self::replace_placeholders($title_template, $city_data, $weather_data);
            
            // Check uniqueness
            if (Snow_Alerts_Uniqueness_Checker::is_title_unique($title)) {
                return $title;
            }
            
            $attempt++;
        }
        
        // If still not unique, add timestamp
        $title_template = isset($template['titles']) && is_array($template['titles']) ? $template['titles'][0] : '{city}, {state}: Winter Storm Alert';
        $title = self::replace_placeholders($title_template, $city_data, $weather_data);
        $title .= ' - ' . date('g:i A');
        
        return $title;
    }
    
    /**
     * Apply title variations to make it unique
     *
     * @param string $title_template Original title template
     * @return string Modified title template
     */
    private static function apply_title_variations($title_template) {
        $variations = self::load_title_variations();
        
        $modification = rand(1, 6);
        
        switch ($modification) {
            case 1:
                // Add prefix
                if (isset($variations['prefixes']) && is_array($variations['prefixes'])) {
                    $prefix = $variations['prefixes'][array_rand($variations['prefixes'])];
                    $title_template = $prefix . ' ' . $title_template;
                }
                break;
            case 2:
                // Add suffix
                if (isset($variations['suffixes']) && is_array($variations['suffixes'])) {
                    $suffix = $variations['suffixes'][array_rand($variations['suffixes'])];
                    $title_template = $title_template . ' ' . $suffix;
                }
                break;
            case 3:
                // Replace action word
                if (isset($variations['action_words']) && is_array($variations['action_words'])) {
                    $action = $variations['action_words'][array_rand($variations['action_words'])];
                    $title_template = str_replace('Expected', $action, $title_template);
                    $title_template = str_replace('Forecast', $action, $title_template);
                }
                break;
            case 4:
                // Add intensity modifier
                if (isset($variations['intensity_modifiers']) && is_array($variations['intensity_modifiers'])) {
                    $modifier = $variations['intensity_modifiers'][array_rand($variations['intensity_modifiers'])];
                    $title_template = str_replace('{weather_event}', $modifier . ' {weather_event}', $title_template);
                }
                break;
            case 5:
                // Replace time reference
                if (isset($variations['time_references']) && is_array($variations['time_references'])) {
                    $time_ref = $variations['time_references'][array_rand($variations['time_references'])];
                    $title_template = str_replace('{date}', $time_ref, $title_template);
                }
                break;
            case 6:
                // Combine prefix and suffix
                if (isset($variations['prefixes']) && is_array($variations['prefixes']) &&
                    isset($variations['suffixes']) && is_array($variations['suffixes'])) {
                    $prefix = $variations['prefixes'][array_rand($variations['prefixes'])];
                    $suffix = $variations['suffixes'][array_rand($variations['suffixes'])];
                    $title_template = $prefix . ' ' . $title_template . ' ' . $suffix;
                }
                break;
        }
        
        return $title_template;
    }
    
    /**
     * Generate meta description
     *
     * @param array $template Template data
     * @param array $city_data City data
     * @param array $weather_data Weather data
     * @return string Meta description
     */
    public static function generate_meta_description($template, $city_data, $weather_data) {
        $descriptions = self::load_description_variations();
        
        // Try template descriptions first
        if (isset($template['meta_descriptions']) && is_array($template['meta_descriptions']) && count($template['meta_descriptions']) > 0) {
            $desc_template = $template['meta_descriptions'][array_rand($template['meta_descriptions'])];
        } elseif (isset($descriptions['templates']) && is_array($descriptions['templates']) && count($descriptions['templates']) > 0) {
            // Use variation descriptions
            $desc_template = $descriptions['templates'][array_rand($descriptions['templates'])];
        } else {
            // Fallback description
            $desc_template = '{city}, {state} is preparing for {weather_event}. Get the latest updates on snowfall amounts, timing, and safety information.';
        }
        
        $description = self::replace_placeholders($desc_template, $city_data, $weather_data);
        
        // Ensure it's within SEO limits (150-160 characters)
        if (strlen($description) > 160) {
            $description = substr($description, 0, 157) . '...';
        }
        
        return $description;
    }
    
    /**
     * Generate article content
     *
     * @param array $template Template data
     * @param array $city_data City data
     * @param array $weather_data Weather data
     * @return string HTML content
     */
    public static function generate_content($template, $city_data, $weather_data) {
        $content_template = '';
        
        if (isset($template['content_templates']) && is_array($template['content_templates']) && count($template['content_templates']) > 0) {
            $content_template = $template['content_templates'][array_rand($template['content_templates'])];
        } else {
            $content_template = self::get_default_content_template();
        }
        
        return self::replace_placeholders($content_template, $city_data, $weather_data);
    }
    
    /**
     * Replace placeholders with actual data
     *
     * @param string $template Template string
     * @param array $city_data City data
     * @param array $weather_data Weather data
     * @return string Processed string
     */
    public static function replace_placeholders($template, $city_data, $weather_data) {
        $placeholders = array();
        
        // City data placeholders
        $placeholders['{city}'] = isset($city_data['city']) ? $city_data['city'] : 'Unknown City';
        $placeholders['{state}'] = isset($city_data['state']) ? $city_data['state'] : 'XX';
        $placeholders['{state_name}'] = isset($city_data['state_name']) ? $city_data['state_name'] : 'Unknown State';
        $placeholders['{county}'] = isset($city_data['county']) ? $city_data['county'] : 'Unknown County';
        $placeholders['{population}'] = isset($city_data['population']) ? number_format($city_data['population']) : '0';
        
        // Weather data placeholders
        $snow_amount = self::calculate_total_snow($weather_data);
        $placeholders['{amount}'] = $snow_amount . ' inches';
        
        $dates = self::extract_dates($weather_data);
        $placeholders['{date}'] = $dates['date'];
        $placeholders['{start_date}'] = $dates['start_date'];
        $placeholders['{end_date}'] = $dates['end_date'];
        
        $event = isset($weather_data['event']) ? $weather_data['event'] : 'Winter Storm';
        $placeholders['{weather_event}'] = $event;
        $placeholders['{event}'] = $event;
        
        // Additional placeholders
        $placeholders['{day_of_week}'] = date('l');
        $placeholders['{current_time}'] = date('g:i A');
        $placeholders['{current_date}'] = date('F j, Y');
        
        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }
    
    /**
     * Calculate total snow amount from weather data
     *
     * @param array $weather_data Weather data
     * @return int Snow amount in inches
     */
    public static function calculate_total_snow($weather_data) {
        if (!is_array($weather_data)) {
            return 0;
        }
        
        // Check various fields for snow amount
        if (isset($weather_data['snow_amount'])) {
            return intval($weather_data['snow_amount']);
        }
        
        if (isset($weather_data['snowfall'])) {
            return intval($weather_data['snowfall']);
        }
        
        if (isset($weather_data['amount'])) {
            return intval($weather_data['amount']);
        }
        
        // Parse from description if available
        if (isset($weather_data['description'])) {
            preg_match('/(\d+)\s*(inches?|in|")/i', $weather_data['description'], $matches);
            if (isset($matches[1])) {
                return intval($matches[1]);
            }
        }
        
        // Default based on event type
        if (isset($weather_data['event'])) {
            $event = strtolower($weather_data['event']);
            if (strpos($event, 'blizzard') !== false) {
                return 12;
            }
            if (strpos($event, 'heavy snow') !== false) {
                return 8;
            }
            if (strpos($event, 'winter storm') !== false) {
                return 6;
            }
        }
        
        return 4; // Default amount
    }
    
    /**
     * Extract and format dates from weather data
     *
     * @param array $weather_data Weather data
     * @return array Formatted dates
     */
    public static function extract_dates($weather_data) {
        $result = array(
            'date' => date('F j, Y'),
            'start_date' => date('F j, Y'),
            'end_date' => date('F j, Y', strtotime('+1 day'))
        );
        
        if (!is_array($weather_data)) {
            return $result;
        }
        
        if (isset($weather_data['start_date'])) {
            $result['start_date'] = date('F j, Y', strtotime($weather_data['start_date']));
            $result['date'] = $result['start_date'];
        }
        
        if (isset($weather_data['end_date'])) {
            $result['end_date'] = date('F j, Y', strtotime($weather_data['end_date']));
        }
        
        if (isset($weather_data['date'])) {
            $result['date'] = date('F j, Y', strtotime($weather_data['date']));
        }
        
        return $result;
    }
    
    /**
     * Generate SEO keywords
     *
     * @param array $city_data City data
     * @param array $weather_data Weather data
     * @return string Comma-separated keywords
     */
    public static function generate_keywords($city_data, $weather_data) {
        $keywords = array();
        
        if (isset($city_data['city'])) {
            $keywords[] = $city_data['city'] . ' weather';
            $keywords[] = $city_data['city'] . ' snow';
        }
        
        if (isset($city_data['state'])) {
            $keywords[] = $city_data['state'] . ' weather';
        }
        
        if (isset($weather_data['event'])) {
            $keywords[] = $weather_data['event'];
        }
        
        $keywords[] = 'winter storm';
        $keywords[] = 'snow forecast';
        $keywords[] = 'weather alert';
        
        return implode(', ', $keywords);
    }
    
    /**
     * Get default templates
     *
     * @return array Default templates
     */
    private static function get_default_templates() {
        return array(
            'winter_storm_warning' => array(
                'titles' => array(
                    '{city}, {state}: Winter Storm Warning Issued for {date}',
                ),
                'meta_descriptions' => array(
                    '{city} prepares for winter storm. Get updates on timing and snowfall amounts.',
                ),
                'content_templates' => array(
                    self::get_default_content_template()
                )
            )
        );
    }
    
    /**
     * Get default title variations
     *
     * @return array Default variations
     */
    private static function get_default_title_variations() {
        return array(
            'prefixes' => array('Breaking:', 'Alert:', 'Update:'),
            'suffixes' => array('- Latest Updates', '- What to Expect'),
            'action_words' => array('Braces for', 'Prepares for'),
            'intensity_modifiers' => array('Major', 'Significant'),
            'time_references' => array('This Week', 'Tonight'),
            'weather_events' => array('Winter Storm', 'Snow Event')
        );
    }
    
    /**
     * Get default description variations
     *
     * @return array Default descriptions
     */
    private static function get_default_description_variations() {
        return array(
            'templates' => array(
                '{city}, {state} is preparing for {weather_event}. Get the latest updates on snowfall amounts, timing, and safety information.'
            )
        );
    }
    
    /**
     * Get default content template
     *
     * @return string Default content template
     */
    private static function get_default_content_template() {
        return '<h2>Winter Storm Alert for {city}, {state}</h2>
<p>A winter storm warning has been issued for {city}, {state_name}, with significant snowfall expected {date}. Residents should prepare for hazardous travel conditions and potential power outages.</p>

<h3>Expected Snowfall</h3>
<p>Meteorologists are forecasting approximately {amount} of snow accumulation in the {city} area. The storm is expected to begin {start_date} and continue through {end_date}.</p>

<h3>Safety Recommendations</h3>
<ul>
<li>Avoid unnecessary travel during the storm</li>
<li>Stock up on essential supplies</li>
<li>Ensure your home heating system is working properly</li>
<li>Keep emergency supplies in your vehicle</li>
<li>Check on elderly neighbors and relatives</li>
</ul>

<h3>Stay Informed</h3>
<p>Monitor local weather forecasts and official alerts for the latest information about this winter storm. Stay safe and prepared during this weather event.</p>';
    }
}
