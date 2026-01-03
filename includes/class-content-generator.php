<?php
/**
 * Content Generator Class
 * 
 * Handles article generation with AI (OpenAI) and template fallback
 *
 * @package Snow_Alerts
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Content_Generator {
    
    /**
     * Generate article content
     *
     * @param array $city_data City data
     * @param array $weather_data Weather data
     * @return array|false Article data or false on failure
     */
    public static function generate_article($city_data, $weather_data) {
        $api_key = Snow_Alerts_API_Manager::get_api_key('openai');
        
        // Try OpenAI first if available
        if (!empty($api_key)) {
            $ai_result = self::generate_with_ai($api_key, $city_data, $weather_data);
            if ($ai_result) {
                return $ai_result;
            }
        }
        
        // Fallback to templates
        error_log('Snow Alerts: Using template fallback for article generation');
        return Snow_Alerts_Template_Manager::generate_from_template($city_data, $weather_data);
    }
    
    /**
     * Generate article using OpenAI API
     *
     * @param string $api_key OpenAI API key
     * @param array $city_data City data
     * @param array $weather_data Weather data
     * @return array|false Article data or false on failure
     */
    private static function generate_with_ai($api_key, $city_data, $weather_data) {
        try {
            // Prepare the prompt
            $prompt = self::build_ai_prompt($city_data, $weather_data);
            
            // Make API request
            $response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                ),
                'body' => json_encode(array(
                    'model' => 'gpt-3.5-turbo',
                    'messages' => array(
                        array(
                            'role' => 'system',
                            'content' => 'You are a professional weather journalist writing SEO-optimized snow alert articles.'
                        ),
                        array(
                            'role' => 'user',
                            'content' => $prompt
                        )
                    ),
                    'max_tokens' => 2000,
                    'temperature' => 0.7,
                )),
                'timeout' => 30,
            ));
            
            // Check for errors
            if (is_wp_error($response)) {
                error_log('Snow Alerts: OpenAI API error: ' . $response->get_error_message());
                return false;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (!isset($data['choices']) || !isset($data['choices'][0]) || !isset($data['choices'][0]['message'])) {
                error_log('Snow Alerts: Invalid OpenAI API response');
                return false;
            }
            
            $content = $data['choices'][0]['message']['content'];
            
            // Parse the AI response
            return self::parse_ai_response($content, $city_data, $weather_data);
            
        } catch (Exception $e) {
            error_log('Snow Alerts: OpenAI exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Build AI prompt
     *
     * @param array $city_data City data
     * @param array $weather_data Weather data
     * @return string Prompt text
     */
    private static function build_ai_prompt($city_data, $weather_data) {
        $city = isset($city_data['city']) ? $city_data['city'] : 'Unknown City';
        $state = isset($city_data['state_name']) ? $city_data['state_name'] : 'Unknown State';
        $event = isset($weather_data['event']) ? $weather_data['event'] : 'Winter Storm';
        
        $prompt = "Write a comprehensive news article about a {$event} affecting {$city}, {$state}. ";
        $prompt .= "Include the following structure:\n\n";
        $prompt .= "TITLE: [SEO-optimized title, 60 characters max]\n";
        $prompt .= "META_DESCRIPTION: [SEO meta description, 150-160 characters]\n";
        $prompt .= "KEYWORDS: [comma-separated SEO keywords]\n";
        $prompt .= "CONTENT: [800-1200 word article with HTML formatting using h2, h3, p, ul tags]\n\n";
        $prompt .= "Include sections on: expected snowfall, timing, safety recommendations, and local impact.\n";
        $prompt .= "Make it informative, professional, and SEO-friendly.";
        
        return $prompt;
    }
    
    /**
     * Parse AI response into structured data
     *
     * @param string $response AI response text
     * @param array $city_data City data
     * @param array $weather_data Weather data
     * @return array Article data
     */
    private static function parse_ai_response($response, $city_data, $weather_data) {
        $article = array(
            'title' => '',
            'content' => '',
            'meta_description' => '',
            'keywords' => '',
        );
        
        // Try to extract structured parts
        if (preg_match('/TITLE:\s*(.+?)(?:\n|META_DESCRIPTION:)/s', $response, $matches)) {
            $article['title'] = trim($matches[1]);
        }
        
        if (preg_match('/META_DESCRIPTION:\s*(.+?)(?:\n|KEYWORDS:)/s', $response, $matches)) {
            $article['meta_description'] = trim($matches[1]);
        }
        
        if (preg_match('/KEYWORDS:\s*(.+?)(?:\n|CONTENT:)/s', $response, $matches)) {
            $article['keywords'] = trim($matches[1]);
        }
        
        if (preg_match('/CONTENT:\s*(.+)$/s', $response, $matches)) {
            $article['content'] = trim($matches[1]);
        }
        
        // Fallback to full response as content if parsing fails
        if (empty($article['content'])) {
            $article['content'] = $response;
        }
        
        // Generate missing parts using template system
        if (empty($article['title'])) {
            $template_data = Snow_Alerts_Template_Manager::generate_from_template($city_data, $weather_data);
            if ($template_data && isset($template_data['title'])) {
                $article['title'] = $template_data['title'];
            }
        }
        
        if (empty($article['meta_description'])) {
            $templates = Snow_Alerts_Template_Manager::load_templates();
            $storm_type = Snow_Alerts_Template_Manager::determine_storm_type($weather_data);
            if (isset($templates[$storm_type])) {
                $article['meta_description'] = Snow_Alerts_Template_Manager::generate_meta_description(
                    $templates[$storm_type],
                    $city_data,
                    $weather_data
                );
            }
        }
        
        if (empty($article['keywords'])) {
            $article['keywords'] = Snow_Alerts_Template_Manager::generate_keywords($city_data, $weather_data);
        }
        
        return $article;
    }
}
