<?php
/**
 * Content Generator
 * 
 * Generates article content using AI (OpenAI GPT-4) with template fallback
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Content_Generator {
    
    /**
     * @var Snow_Alerts_API_Manager
     */
    private $api_manager;
    
    /**
     * @var Snow_Alerts_Template_Manager
     */
    private $template_manager;
    
    /**
     * @var Snow_Alerts_Database
     */
    private $database;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->api_manager = new Snow_Alerts_API_Manager();
        $this->template_manager = new Snow_Alerts_Template_Manager();
        $this->database = new Snow_Alerts_Database();
    }
    
    /**
     * Generate article content
     */
    public function generate($location, $weather_data, $format = 'standard') {
        // Try AI first
        $content = $this->generate_with_ai($location, $weather_data, $format);
        
        if ($content && isset($content['success']) && $content['success']) {
            return $content;
        }
        
        // Fallback to templates
        return $this->generate_with_template($location, $weather_data, $format);
    }
    
    /**
     * Generate content with OpenAI
     */
    private function generate_with_ai($location, $weather_data, $format) {
        $api_key = $this->api_manager->get_api_key('openai');
        
        if (empty($api_key)) {
            return array('success' => false, 'error' => 'OpenAI API key not configured');
        }
        
        $location_name = isset($location['name']) ? $location['name'] : '';
        $location_state = isset($location['state']) ? $location['state'] : '';
        $snow_amount = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        $prompt = $this->build_ai_prompt($location_name, $location_state, $weather_data, $format);
        
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $body = array(
            'model' => 'gpt-4',
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => 'You are an expert weather journalist who writes engaging, accurate snow forecast articles.'
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'temperature' => 0.7,
            'max_tokens' => 2000,
        );
        
        $start_time = microtime(true);
        $response = wp_remote_post($url, array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($body),
        ));
        $execution_time = microtime(true) - $start_time;
        
        if (is_wp_error($response)) {
            $this->database->log_api_call(
                'openai',
                $url,
                $body,
                $response->get_error_message(),
                0,
                false,
                $response->get_error_message(),
                $execution_time
            );
            
            return array('success' => false, 'error' => $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($status_code !== 200) {
            $this->database->log_api_call(
                'openai',
                $url,
                $body,
                $response_body,
                $status_code,
                false,
                'HTTP ' . $status_code,
                $execution_time
            );
            
            return array('success' => false, 'error' => 'OpenAI API error: ' . $status_code);
        }
        
        $data = json_decode($response_body, true);
        
        if (!$data || json_last_error() !== JSON_ERROR_NONE) {
            return array('success' => false, 'error' => 'Invalid JSON from OpenAI');
        }
        
        $this->database->log_api_call(
            'openai',
            $url,
            $body,
            $response_body,
            $status_code,
            true,
            '',
            $execution_time
        );
        
        $content = isset($data['choices']) && isset($data['choices'][0]) && isset($data['choices'][0]['message']) && isset($data['choices'][0]['message']['content']) ? $data['choices'][0]['message']['content'] : '';
        
        if (empty($content)) {
            return array('success' => false, 'error' => 'Empty response from OpenAI');
        }
        
        return array(
            'success' => true,
            'content' => $content,
            'source' => 'openai',
            'format' => $format,
        );
    }
    
    /**
     * Build AI prompt
     */
    private function build_ai_prompt($location_name, $location_state, $weather_data, $format) {
        $snow_amount = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        $current = isset($weather_data['current']) ? $weather_data['current'] : array();
        
        $temp = isset($current['temperature']) ? $current['temperature'] : 0;
        $wind = isset($current['wind_speed']) ? $current['wind_speed'] : 0;
        
        $prompt = "Write a comprehensive, SEO-optimized snow alert article for {$location_name}, {$location_state}.\n\n";
        $prompt .= "Weather Details:\n";
        $prompt .= "- Total Snow Expected: {$snow_amount} inches\n";
        $prompt .= "- Current Temperature: {$temp}°F\n";
        $prompt .= "- Wind Speed: {$wind} mph\n\n";
        
        $prompt .= "Article Requirements:\n";
        $prompt .= "- Length: 800-1200 words\n";
        $prompt .= "- Format: {$format}\n";
        $prompt .= "- Include: Forecast details, timing, safety tips, travel advisories\n";
        $prompt .= "- Tone: Professional, informative, locally relevant\n";
        $prompt .= "- Structure: Clear sections with subheadings\n";
        $prompt .= "- SEO: Natural keyword usage, engaging introduction\n\n";
        
        $prompt .= "Please write the complete article content (body only, no title).";
        
        return $prompt;
    }
    
    /**
     * Generate content with templates
     */
    private function generate_with_template($location, $weather_data, $format) {
        $location_name = isset($location['name']) ? $location['name'] : '';
        $location_state = isset($location['state']) ? $location['state'] : '';
        $snow_amount = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        $storm_type = $this->template_manager->detect_storm_type($weather_data, $location);
        $template = $this->template_manager->get_template($storm_type);
        
        $intro = isset($template['intro']) ? $template['intro'] : '';
        $intro = str_replace('{location}', $location_name, $intro);
        $intro = str_replace('{inches}', number_format($snow_amount, 1), $intro);
        
        $content = "<p>{$intro}</p>\n\n";
        
        // Forecast section
        $content .= "<h2>Forecast Details</h2>\n";
        $content .= "<p>The latest forecast models show {$location_name}, {$location_state} receiving approximately " . number_format($snow_amount, 1) . " inches of snow over the next 24-48 hours. ";
        $content .= "Residents should prepare for hazardous travel conditions and potential disruptions to daily activities.</p>\n\n";
        
        // Timing
        $content .= "<h2>When to Expect the Snow</h2>\n";
        $content .= "<p>Snow is expected to begin late tonight and continue through tomorrow afternoon. ";
        $content .= "The heaviest accumulation will likely occur during the overnight and early morning hours, ";
        $content .= "making the morning commute particularly challenging.</p>\n\n";
        
        // Current conditions
        if (isset($weather_data['current'])) {
            $current = $weather_data['current'];
            $temp = isset($current['temperature']) ? $current['temperature'] : 0;
            $wind = isset($current['wind_speed']) ? $current['wind_speed'] : 0;
            
            $content .= "<h2>Current Conditions</h2>\n";
            $content .= "<p>As of now, {$location_name} is experiencing temperatures around " . round($temp) . "°F ";
            $content .= "with winds at " . round($wind) . " mph. ";
            $content .= "These conditions are expected to deteriorate as the storm system moves into the area.</p>\n\n";
        }
        
        // Safety tips
        $content .= "<h2>Safety Recommendations</h2>\n";
        $content .= "<p>Local authorities recommend the following safety measures:</p>\n";
        $content .= "<ul>\n";
        $content .= "<li>Avoid unnecessary travel during the storm</li>\n";
        $content .= "<li>Stock up on essential supplies including food, water, and medications</li>\n";
        $content .= "<li>Keep emergency kits in vehicles if travel is necessary</li>\n";
        $content .= "<li>Charge all electronic devices and have backup power sources ready</li>\n";
        $content .= "<li>Check on elderly neighbors and family members</li>\n";
        $content .= "<li>Clear snow from driveways and walkways promptly to prevent ice formation</li>\n";
        $content .= "</ul>\n\n";
        
        // Travel advisory
        $content .= "<h2>Travel Advisory</h2>\n";
        $content .= "<p>The {$location_state} Department of Transportation urges extreme caution for anyone who must travel. ";
        $content .= "Road conditions will deteriorate rapidly once snow begins. ";
        $content .= "Reduce speed, increase following distance, and allow extra time to reach your destination. ";
        $content .= "If possible, delay travel until after road crews have had time to treat and clear roadways.</p>\n\n";
        
        // School and business impacts
        $content .= "<h2>Potential Impacts</h2>\n";
        $content .= "<p>Residents should be prepared for potential school closures and delays. ";
        $content .= "Many businesses may adjust their operating hours or close entirely during the storm. ";
        $content .= "Power outages are possible in areas receiving the heaviest snowfall, particularly if wet, heavy snow accumulates on power lines and tree branches.</p>\n\n";
        
        // Stay informed
        $content .= "<h2>Stay Informed</h2>\n";
        $content .= "<p>Continue to monitor local weather forecasts and official emergency management channels for the latest updates. ";
        $content .= "Conditions can change rapidly during winter storms, and forecasts will be updated as new information becomes available. ";
        $content .= "The National Weather Service will issue warnings and advisories as conditions warrant.</p>\n\n";
        
        // Conclusion
        $content .= "<p>Residents of {$location_name} and surrounding areas should take this forecast seriously and make preparations now. ";
        $content .= "While snow is not uncommon in this region, accumulations of this magnitude can create dangerous conditions. ";
        $content .= "By preparing in advance and following safety guidelines, residents can weather this storm safely.</p>";
        
        return array(
            'success' => true,
            'content' => $content,
            'source' => 'template',
            'format' => $format,
        );
    }
}
