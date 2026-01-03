<?php
/**
 * Content Variation Generator
 * 
 * Generates 8 different content formats to prevent duplication
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Content_Variation_Generator {
    
    /**
     * Available formats
     */
    private $formats = array(
        'standard',
        'update',
        'timeline',
        'qna',
        'listicle',
        'safety',
        'impact',
        'comparison',
    );
    
    /**
     * Get random format
     */
    public function get_random_format($exclude = array()) {
        $available = array_diff($this->formats, $exclude);
        
        if (empty($available)) {
            $available = $this->formats;
        }
        
        return $available[array_rand($available)];
    }
    
    /**
     * Generate content in specific format
     */
    public function generate($location, $weather_data, $format, $is_update = false, $parent_article = null) {
        $method = 'generate_' . $format;
        
        if (method_exists($this, $method)) {
            return $this->{$method}($location, $weather_data, $is_update, $parent_article);
        }
        
        return $this->generate_standard($location, $weather_data, $is_update, $parent_article);
    }
    
    /**
     * Standard format
     */
    private function generate_standard($location, $weather_data, $is_update, $parent_article) {
        $name = isset($location['name']) ? $location['name'] : '';
        $state = isset($location['state']) ? $location['state'] : '';
        $snow = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        $content = "<p>A significant winter storm is expected to impact {$name}, {$state}, with forecasters predicting up to " . number_format($snow, 1) . " inches of snow accumulation.</p>\n\n";
        
        $content .= "<h2>What to Expect</h2>\n";
        $content .= "<p>The storm system will move through the region bringing heavy snowfall, reduced visibility, and hazardous travel conditions. Residents should prepare for disruptions to daily activities and take necessary precautions.</p>\n\n";
        
        return $content;
    }
    
    /**
     * Update format
     */
    private function generate_update($location, $weather_data, $is_update, $parent_article) {
        $name = isset($location['name']) ? $location['name'] : '';
        $state = isset($location['state']) ? $location['state'] : '';
        $snow = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        $content = "<p><strong>UPDATE:</strong> The latest forecast models show changing conditions for {$name}, {$state}.</p>\n\n";
        
        $content .= "<h2>Latest Developments</h2>\n";
        $content .= "<p>Meteorologists have updated the snow forecast to approximately " . number_format($snow, 1) . " inches. This represents a change from earlier predictions as the storm track has been refined.</p>\n\n";
        
        $content .= "<h2>What Changed</h2>\n";
        $content .= "<p>The adjustment comes as weather models have converged on a slightly different track for the low-pressure system. Residents should continue to monitor forecasts as conditions can still evolve.</p>\n\n";
        
        return $content;
    }
    
    /**
     * Timeline format
     */
    private function generate_timeline($location, $weather_data, $is_update, $parent_article) {
        $name = isset($location['name']) ? $location['name'] : '';
        $state = isset($location['state']) ? $location['state'] : '';
        $snow = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        $content = "<p>Here's a detailed timeline of the winter storm expected to impact {$name}, {$state}.</p>\n\n";
        
        $content .= "<h2>Storm Timeline</h2>\n";
        $content .= "<p><strong>Tonight (6 PM - Midnight):</strong> Snow begins to develop across the region. Initial light accumulations expected.</p>\n\n";
        
        $content .= "<p><strong>Overnight (Midnight - 6 AM):</strong> Snow intensifies. This will be the period of heaviest accumulation. Travel will become increasingly difficult.</p>\n\n";
        
        $content .= "<p><strong>Tomorrow Morning (6 AM - Noon):</strong> Snow continues but begins to taper. Significant accumulations on roadways. Morning commute severely impacted.</p>\n\n";
        
        $content .= "<p><strong>Tomorrow Afternoon (Noon - 6 PM):</strong> Snow winds down. Total accumulation expected around " . number_format($snow, 1) . " inches. Road crews working to clear main routes.</p>\n\n";
        
        return $content;
    }
    
    /**
     * Q&A format
     */
    private function generate_qna($location, $weather_data, $is_update, $parent_article) {
        $name = isset($location['name']) ? $location['name'] : '';
        $state = isset($location['state']) ? $location['state'] : '';
        $snow = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        $content = "<p>Your questions answered about the upcoming winter storm in {$name}, {$state}.</p>\n\n";
        
        $content .= "<h2>How much snow will we get?</h2>\n";
        $content .= "<p>Current forecasts indicate approximately " . number_format($snow, 1) . " inches of accumulation. However, localized bands could produce higher amounts in some areas.</p>\n\n";
        
        $content .= "<h2>When will the snow start?</h2>\n";
        $content .= "<p>Snow is expected to begin late tonight and continue through tomorrow afternoon. The heaviest period will likely be overnight and during the early morning hours.</p>\n\n";
        
        $content .= "<h2>Will schools be closed?</h2>\n";
        $content .= "<p>School districts are monitoring the situation closely. Decisions about closures or delays will be made early tomorrow morning based on road conditions and the forecast.</p>\n\n";
        
        $content .= "<h2>Is travel advised?</h2>\n";
        $content .= "<p>Travel is strongly discouraged during the storm. If you must travel, allow extra time, drive slowly, and ensure your vehicle is properly equipped for winter conditions.</p>\n\n";
        
        return $content;
    }
    
    /**
     * Listicle format
     */
    private function generate_listicle($location, $weather_data, $is_update, $parent_article) {
        $name = isset($location['name']) ? $location['name'] : '';
        $state = isset($location['state']) ? $location['state'] : '';
        $snow = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        $content = "<p>Here are 10 things you need to know about the winter storm heading for {$name}, {$state}.</p>\n\n";
        
        $content .= "<h2>1. Significant Accumulation Expected</h2>\n";
        $content .= "<p>Forecasters are calling for approximately " . number_format($snow, 1) . " inches of snow accumulation.</p>\n\n";
        
        $content .= "<h2>2. Timing Is Critical</h2>\n";
        $content .= "<p>The storm will impact the area primarily overnight and during the morning commute.</p>\n\n";
        
        $content .= "<h2>3. Travel Will Be Hazardous</h2>\n";
        $content .= "<p>Road conditions will deteriorate quickly once snow begins. Plan accordingly.</p>\n\n";
        
        $content .= "<h2>4. Stock Up on Essentials</h2>\n";
        $content .= "<p>Ensure you have adequate food, water, and medications before the storm arrives.</p>\n\n";
        
        $content .= "<h2>5. Prepare for Power Outages</h2>\n";
        $content .= "<p>Heavy snow can bring down power lines. Have flashlights and batteries ready.</p>\n\n";
        
        return $content;
    }
    
    /**
     * Safety format
     */
    private function generate_safety($location, $weather_data, $is_update, $parent_article) {
        $name = isset($location['name']) ? $location['name'] : '';
        $state = isset($location['state']) ? $location['state'] : '';
        $snow = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        $content = "<p>Stay safe during the winter storm expected to bring " . number_format($snow, 1) . " inches of snow to {$name}, {$state}.</p>\n\n";
        
        $content .= "<h2>Before the Storm</h2>\n";
        $content .= "<ul>\n";
        $content .= "<li>Stock up on essential supplies</li>\n";
        $content .= "<li>Fill prescriptions and have medications on hand</li>\n";
        $content .= "<li>Charge all electronic devices</li>\n";
        $content .= "<li>Bring pets indoors</li>\n";
        $content .= "<li>Test backup power sources</li>\n";
        $content .= "</ul>\n\n";
        
        $content .= "<h2>During the Storm</h2>\n";
        $content .= "<ul>\n";
        $content .= "<li>Stay indoors if possible</li>\n";
        $content .= "<li>Avoid unnecessary travel</li>\n";
        $content .= "<li>Keep emergency supplies accessible</li>\n";
        $content .= "<li>Monitor weather updates regularly</li>\n";
        $content .= "<li>Check on neighbors, especially elderly residents</li>\n";
        $content .= "</ul>\n\n";
        
        return $content;
    }
    
    /**
     * Impact format
     */
    private function generate_impact($location, $weather_data, $is_update, $parent_article) {
        $name = isset($location['name']) ? $location['name'] : '';
        $state = isset($location['state']) ? $location['state'] : '';
        $snow = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        $content = "<p>The winter storm forecast to bring " . number_format($snow, 1) . " inches of snow to {$name}, {$state} will have wide-ranging impacts.</p>\n\n";
        
        $content .= "<h2>Transportation Impacts</h2>\n";
        $content .= "<p>Roadways will become snow-covered and hazardous. The morning commute will be severely impacted. Airlines may cancel or delay flights. Public transportation may operate on reduced schedules.</p>\n\n";
        
        $content .= "<h2>School and Business Closures</h2>\n";
        $content .= "<p>Many schools are expected to close or delay opening. Some businesses may adjust hours or close for the day. Non-essential employees should consider working from home if possible.</p>\n\n";
        
        $content .= "<h2>Utilities and Services</h2>\n";
        $content .= "<p>Power outages are possible in areas receiving the heaviest snow. Garbage collection and other municipal services may be delayed. Emergency services will prioritize critical calls.</p>\n\n";
        
        return $content;
    }
    
    /**
     * Comparison format
     */
    private function generate_comparison($location, $weather_data, $is_update, $parent_article) {
        $name = isset($location['name']) ? $location['name'] : '';
        $state = isset($location['state']) ? $location['state'] : '';
        $snow = isset($weather_data['total_snow']) ? $weather_data['total_snow'] : 0;
        
        $content = "<p>How does the forecast " . number_format($snow, 1) . " inches for {$name}, {$state} compare to typical winter storms?</p>\n\n";
        
        $content .= "<h2>Historical Context</h2>\n";
        $content .= "<p>This storm is expected to produce above-average snowfall for the region. The seasonal average for a single storm event is typically 4-6 inches.</p>\n\n";
        
        $content .= "<h2>Recent Comparisons</h2>\n";
        $content .= "<p>The last storm of similar magnitude occurred several months ago. This event could rank among the top winter storms of the season depending on final accumulation totals.</p>\n\n";
        
        $content .= "<h2>Regional Variations</h2>\n";
        $content .= "<p>Different areas will see varying amounts. Higher elevations and areas in convergent snow bands may receive significantly more than the forecast average.</p>\n\n";
        
        return $content;
    }
}
