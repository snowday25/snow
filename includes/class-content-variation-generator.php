<?php
/**
 * Content Variation Generator
 * 
 * Generates content in 8 different formats:
 * 1. update - Breaking news style with "What's Changed"
 * 2. detailed_timeline - Hour-by-hour breakdown
 * 3. safety_focused - Safety and preparedness guide
 * 4. impact_analysis - Transportation, business, school impacts
 * 5. qa_format - Question and answer style
 * 6. listicle - "10 Things to Know" format
 * 7. comparison - Before/after comparison
 * 8. standard - Regular news article
 */

if (!defined('ABSPATH')) {
    exit;
}

class Snow_Alerts_Content_Variation_Generator {
    
    /**
     * Available content formats
     */
    private $formats = array(
        'update',
        'detailed_timeline',
        'safety_focused',
        'impact_analysis',
        'qa_format',
        'listicle',
        'comparison',
        'standard'
    );
    
    /**
     * Generate content variation
     * 
     * @param array $location_data Location data
     * @param array $weather_data Weather data
     * @param string $format Format type
     * @param int $previous_article_id Previous article ID (optional)
     * @return array Array with 'title', 'content', 'format'
     */
    public function generate_variation($location_data, $weather_data, $format = 'standard', $previous_article_id = null) {
        if (!in_array($format, $this->formats)) {
            $format = 'standard';
        }
        
        $method = 'generate_' . $format . '_format';
        
        if (method_exists($this, $method)) {
            return $this->$method($location_data, $weather_data, $previous_article_id);
        }
        
        return $this->generate_standard_format($location_data, $weather_data, $previous_article_id);
    }
    
    /**
     * Generate UPDATE format
     */
    private function generate_update_format($location_data, $weather_data, $previous_article_id = null) {
        $location_name = isset($location_data['name']) ? $location_data['name'] : 'Unknown';
        $state = isset($location_data['state_name']) ? $location_data['state_name'] : '';
        $snow_amount = isset($weather_data['snow_amount']) ? $weather_data['snow_amount'] : 0;
        
        $badges = array('UPDATED', 'Breaking Update', 'Latest Information', 'Fresh Update');
        $badge = $badges[array_rand($badges)];
        
        $title = sprintf('%s: %s, %s Snow Forecast Updated', $badge, $location_name, $state);
        
        $content = sprintf('<div class="snow-alert-update">');
        $content .= sprintf('<h2>%s Inches of Snow Expected in %s</h2>', $snow_amount, $location_name);
        
        // What's Changed section
        $content .= '<h3>What\'s Changed</h3>';
        $content .= '<ul>';
        
        if ($previous_article_id) {
            $content .= '<li>Updated snow accumulation forecast</li>';
            $content .= '<li>Revised timing of snow arrival</li>';
            $content .= '<li>Modified impact assessment</li>';
            $content .= sprintf('<li><a href="%s">View previous forecast</a></li>', get_permalink($previous_article_id));
        } else {
            $content .= '<li>Latest model data incorporated</li>';
            $content .= '<li>Forecast confidence increased</li>';
        }
        
        $content .= '</ul>';
        
        // Main content
        $content .= '<h3>Current Forecast</h3>';
        $content .= sprintf('<p>The latest forecast for %s, %s shows significant winter weather approaching. ', $location_name, $state);
        $content .= sprintf('Meteorologists are now predicting up to %s inches of snow accumulation. ', $snow_amount);
        $content .= 'This represents a change from previous forecasts based on updated atmospheric modeling.</p>';
        
        $content .= '<h3>Timeline</h3>';
        $content .= '<p>Snow is expected to begin in the coming hours, with the heaviest accumulation during the overnight period. ';
        $content .= 'Residents should prepare for difficult travel conditions and potential power outages.</p>';
        
        $content .= '<h3>Impacts</h3>';
        $content .= '<ul>';
        $content .= '<li>Travel will become hazardous</li>';
        $content .= '<li>School delays and cancellations likely</li>';
        $content .= '<li>Reduced visibility during heaviest snowfall</li>';
        $content .= '</ul>';
        
        $content .= '</div>';
        
        return array(
            'title' => $title,
            'content' => $content,
            'format' => 'update'
        );
    }
    
    /**
     * Generate DETAILED TIMELINE format
     */
    private function generate_detailed_timeline_format($location_data, $weather_data, $previous_article_id = null) {
        $location_name = isset($location_data['name']) ? $location_data['name'] : 'Unknown';
        $state = isset($location_data['state_name']) ? $location_data['state_name'] : '';
        $snow_amount = isset($weather_data['snow_amount']) ? $weather_data['snow_amount'] : 0;
        
        $title = sprintf('Hour-by-Hour: %s, %s Snow Timeline', $location_name, $state);
        
        $content = sprintf('<div class="snow-timeline">');
        $content .= sprintf('<h2>%s Snow Event: Complete Timeline</h2>', $location_name);
        
        $content .= '<div class="timeline-intro">';
        $content .= sprintf('<p>A comprehensive hour-by-hour breakdown of the developing snow event in %s, %s. ', $location_name, $state);
        $content .= sprintf('Total expected accumulation: %s inches.</p>', $snow_amount);
        $content .= '</div>';
        
        // Hour-by-hour breakdown
        $hours = array(
            '6:00 AM' => 'Cloudy skies develop, temperatures drop',
            '9:00 AM' => 'First flurries possible, light precipitation begins',
            '12:00 PM' => 'Snow intensity increases, accumulation starts',
            '3:00 PM' => 'Steady snowfall, visibility decreasing',
            '6:00 PM' => 'Heavy snow expected, difficult travel conditions',
            '9:00 PM' => 'Peak snowfall rates, near-whiteout conditions possible',
            '12:00 AM' => 'Snow continues, significant accumulation',
            '3:00 AM' => 'Snow begins to taper',
            '6:00 AM' => 'Light snow or flurries, improvement begins'
        );
        
        $content .= '<div class="hour-timeline">';
        foreach ($hours as $time => $description) {
            $content .= sprintf('<div class="timeline-item">');
            $content .= sprintf('<h3>%s</h3>', $time);
            $content .= sprintf('<p>%s</p>', $description);
            $content .= '</div>';
        }
        $content .= '</div>';
        
        $content .= '<h3>Key Takeaways</h3>';
        $content .= '<ul>';
        $content .= '<li>Plan ahead for deteriorating conditions</li>';
        $content .= '<li>Avoid travel during peak snowfall hours</li>';
        $content .= '<li>Allow extra time for morning commute</li>';
        $content .= '</ul>';
        
        $content .= '</div>';
        
        return array(
            'title' => $title,
            'content' => $content,
            'format' => 'detailed_timeline'
        );
    }
    
    /**
     * Generate SAFETY FOCUSED format
     */
    private function generate_safety_focused_format($location_data, $weather_data, $previous_article_id = null) {
        $location_name = isset($location_data['name']) ? $location_data['name'] : 'Unknown';
        $state = isset($location_data['state_name']) ? $location_data['state_name'] : '';
        $snow_amount = isset($weather_data['snow_amount']) ? $weather_data['snow_amount'] : 0;
        
        $title = sprintf('Safety Guide: Preparing for %s Inches of Snow in %s, %s', $snow_amount, $location_name, $state);
        
        $content = '<div class="snow-safety-guide">';
        $content .= sprintf('<h2>Winter Storm Safety: %s Edition</h2>', $location_name);
        
        $content .= '<p class="intro">With significant snowfall expected, here\'s your comprehensive safety and preparedness guide.</p>';
        
        // Home Safety
        $content .= '<h3>🏠 Home Safety Checklist</h3>';
        $content .= '<ul>';
        $content .= '<li>Check heating system is functioning properly</li>';
        $content .= '<li>Stock emergency supplies (food, water, medications)</li>';
        $content .= '<li>Charge all electronic devices and power banks</li>';
        $content .= '<li>Have flashlights and batteries ready</li>';
        $content .= '<li>Clear gutters and downspouts</li>';
        $content .= '<li>Insulate pipes to prevent freezing</li>';
        $content .= '</ul>';
        
        // Travel Safety
        $content .= '<h3>🚗 Travel Safety Guidelines</h3>';
        $content .= '<ul>';
        $content .= '<li>Avoid unnecessary travel during storm</li>';
        $content .= '<li>If you must drive, clear all snow from vehicle</li>';
        $content .= '<li>Reduce speed and increase following distance</li>';
        $content .= '<li>Keep emergency kit in car (blanket, snacks, phone charger)</li>';
        $content .= '<li>Tell someone your route and expected arrival time</li>';
        $content .= '<li>Never use cruise control on snow or ice</li>';
        $content .= '</ul>';
        
        // Emergency Preparedness
        $content .= '<h3>⚠️ Emergency Preparedness</h3>';
        $content .= '<ul>';
        $content .= '<li>Have a backup heating source if power goes out</li>';
        $content .= '<li>Keep first aid kit accessible</li>';
        $content .= '<li>Know how to shut off utilities if needed</li>';
        $content .= '<li>Have non-electric can opener for canned food</li>';
        $content .= '<li>Keep prescriptions filled</li>';
        $content .= '<li>Have cash on hand (ATMs may be inaccessible)</li>';
        $content .= '</ul>';
        
        // Winter Weather Basics
        $content .= '<h3>❄️ Snow Removal Safety</h3>';
        $content .= '<ul>';
        $content .= '<li>Dress in layers when shoveling</li>';
        $content .= '<li>Take frequent breaks to avoid overexertion</li>';
        $content .= '<li>Push snow rather than lifting when possible</li>';
        $content .= '<li>Use proper shoveling technique to prevent injury</li>';
        $content .= '<li>Clear snow from dryer vents and furnace exhausts</li>';
        $content .= '</ul>';
        
        $content .= sprintf('<p class="safety-footer">Stay safe during this %s inch snowfall in %s. Monitor local news for updates.</p>', $snow_amount, $location_name);
        
        $content .= '</div>';
        
        return array(
            'title' => $title,
            'content' => $content,
            'format' => 'safety_focused'
        );
    }
    
    /**
     * Generate IMPACT ANALYSIS format
     */
    private function generate_impact_analysis_format($location_data, $weather_data, $previous_article_id = null) {
        $location_name = isset($location_data['name']) ? $location_data['name'] : 'Unknown';
        $state = isset($location_data['state_name']) ? $location_data['state_name'] : '';
        $snow_amount = isset($weather_data['snow_amount']) ? $weather_data['snow_amount'] : 0;
        $county = isset($location_data['county']) ? $location_data['county'] : '';
        
        $title = sprintf('Impact Analysis: How %s Inches of Snow Will Affect %s, %s', $snow_amount, $location_name, $state);
        
        $content = '<div class="impact-analysis">';
        $content .= sprintf('<h2>Comprehensive Impact Assessment: %s Winter Storm</h2>', $location_name);
        
        // Transportation Impact
        $content .= '<h3>🚦 Transportation Impact</h3>';
        $content .= '<p><strong>Severity: High</strong></p>';
        $content .= '<ul>';
        $content .= '<li>Road conditions will deteriorate rapidly</li>';
        $content .= '<li>Highway speeds reduced by 50% or more</li>';
        $content .= '<li>Snow emergency routes may be declared</li>';
        $content .= '<li>Public transportation delays expected</li>';
        $content .= sprintf('<li>%s County road crews on standby</li>', $county);
        $content .= '<li>Possible flight delays at regional airports</li>';
        $content .= '</ul>';
        
        // Business Impact
        $content .= '<h3>💼 Business & Commerce Impact</h3>';
        $content .= '<p><strong>Severity: Moderate to High</strong></p>';
        $content .= '<ul>';
        $content .= '<li>Retail business may close early</li>';
        $content .= '<li>Remote work recommended where possible</li>';
        $content .= '<li>Delivery services experiencing delays</li>';
        $content .= '<li>Restaurants switching to takeout only</li>';
        $content .= '<li>Supply chain disruptions possible</li>';
        $content .= '</ul>';
        
        // School Impact
        $content .= '<h3>🏫 Education & Schools</h3>';
        $content .= '<p><strong>Likelihood of Closures: Very High</strong></p>';
        $content .= '<ul>';
        $content .= sprintf('<li>%s school districts monitoring conditions</li>', $location_name);
        $content .= '<li>Decision on closures expected by 5 AM</li>';
        $content .= '<li>Virtual learning may be implemented</li>';
        $content .= '<li>After-school activities likely cancelled</li>';
        $content .= '<li>College campuses may suspend classes</li>';
        $content .= '</ul>';
        
        // Utilities Impact
        $content .= '<h3>⚡ Utilities & Infrastructure</h3>';
        $content .= '<p><strong>Risk Level: Moderate</strong></p>';
        $content .= '<ul>';
        $content .= '<li>Power outages possible from heavy snow on lines</li>';
        $content .= '<li>Utility crews pre-positioned</li>';
        $content .= '<li>Water service interruptions unlikely but possible</li>';
        $content .= '<li>Natural gas demand increasing</li>';
        $content .= '</ul>';
        
        // Economic Impact
        $content .= '<h3>💰 Economic Impact</h3>';
        $content .= '<ul>';
        $content .= '<li>Lost productivity from missed work</li>';
        $content .= '<li>Increased spending on snow removal</li>';
        $content .= '<li>Higher heating costs</li>';
        $content .= '<li>Boost for hardware and grocery stores</li>';
        $content .= '<li>Tourism and hospitality affected</li>';
        $content .= '</ul>';
        
        $content .= sprintf('<p class="impact-summary">The %s inch snowfall will have widespread impacts across %s and %s County. Residents should plan accordingly.</p>', $snow_amount, $location_name, $county);
        
        $content .= '</div>';
        
        return array(
            'title' => $title,
            'content' => $content,
            'format' => 'impact_analysis'
        );
    }
    
    /**
     * Generate Q&A format
     */
    private function generate_qa_format_format($location_data, $weather_data, $previous_article_id = null) {
        $location_name = isset($location_data['name']) ? $location_data['name'] : 'Unknown';
        $state = isset($location_data['state_name']) ? $location_data['state_name'] : '';
        $snow_amount = isset($weather_data['snow_amount']) ? $weather_data['snow_amount'] : 0;
        
        $title = sprintf('%s, %s Snow: Your Questions Answered', $location_name, $state);
        
        $content = '<div class="qa-format">';
        $content .= sprintf('<h2>%s Snow Event: Frequently Asked Questions</h2>', $location_name);
        
        $content .= '<p class="intro">Everything you need to know about the approaching winter storm.</p>';
        
        // Q&A pairs
        $content .= '<div class="qa-item">';
        $content .= '<h3>Q: When will the snow start?</h3>';
        $content .= '<p><strong>A:</strong> Snow is expected to begin in the late morning to early afternoon hours. ';
        $content .= 'Light flurries may start earlier, but accumulating snow will develop as the day progresses.</p>';
        $content .= '</div>';
        
        $content .= '<div class="qa-item">';
        $content .= '<h3>Q: How much snow will we get?</h3>';
        $content .= sprintf('<p><strong>A:</strong> Current forecasts indicate %s inches of total accumulation for %s. ', $snow_amount, $location_name);
        $content .= 'Some areas may see slightly higher or lower amounts depending on exact storm track.</p>';
        $content .= '</div>';
        
        $content .= '<div class="qa-item">';
        $content .= '<h3>Q: Should I avoid traveling?</h3>';
        $content .= '<p><strong>A:</strong> Yes, if possible. Road conditions will deteriorate quickly once snow begins. ';
        $content .= 'If you must travel, allow extra time, reduce speed, and ensure your vehicle is winter-ready.</p>';
        $content .= '</div>';
        
        $content .= '<div class="qa-item">';
        $content .= '<h3>Q: Will schools be closed?</h3>';
        $content .= '<p><strong>A:</strong> School closures are very likely given the expected snowfall. ';
        $content .= 'Decisions are typically made early morning. Check your school district\'s website and social media for official announcements.</p>';
        $content .= '</div>';
        
        $content .= '<div class="qa-item">';
        $content .= '<h3>Q: How should I prepare?</h3>';
        $content .= '<p><strong>A:</strong> Stock up on essential supplies, ensure you have medications, charge devices, and prepare for possible power outages. ';
        $content .= 'Have food that doesn\'t require cooking and keep emergency supplies accessible.</p>';
        $content .= '</div>';
        
        $content .= '<div class="qa-item">';
        $content .= '<h3>Q: When will the snow end?</h3>';
        $content .= '<p><strong>A:</strong> Snow should taper off by early morning hours. ';
        $content .= 'However, cleanup and travel impacts will continue throughout the following day.</p>';
        $content .= '</div>';
        
        $content .= '<div class="qa-item">';
        $content .= '<h3>Q: What about power outages?</h3>';
        $content .= '<p><strong>A:</strong> Heavy, wet snow can cause power outages. Have flashlights, batteries, and a backup heating plan ready. ';
        $content .= 'Report outages to your utility company immediately.</p>';
        $content .= '</div>';
        
        $content .= '<div class="qa-item">';
        $content .= '<h3>Q: Where can I get updates?</h3>';
        $content .= '<p><strong>A:</strong> Monitor local news, weather apps, and official government social media accounts for the latest information. ';
        $content .= 'Sign up for emergency alerts in your area.</p>';
        $content .= '</div>';
        
        $content .= '</div>';
        
        return array(
            'title' => $title,
            'content' => $content,
            'format' => 'qa_format'
        );
    }
    
    /**
     * Generate LISTICLE format
     */
    private function generate_listicle_format($location_data, $weather_data, $previous_article_id = null) {
        $location_name = isset($location_data['name']) ? $location_data['name'] : 'Unknown';
        $state = isset($location_data['state_name']) ? $location_data['state_name'] : '';
        $snow_amount = isset($weather_data['snow_amount']) ? $weather_data['snow_amount'] : 0;
        
        $title = sprintf('10 Things to Know About the %s, %s Snowstorm', $location_name, $state);
        
        $content = '<div class="listicle-format">';
        $content .= sprintf('<h2>10 Essential Facts About %s\'s Approaching Winter Storm</h2>', $location_name);
        
        $content .= '<div class="list-item">';
        $content .= '<h3>1. Significant Snowfall Expected</h3>';
        $content .= sprintf('<p>Meteorologists predict %s inches of snow for %s, %s. This is above-average snowfall and will create hazardous conditions.</p>', $snow_amount, $location_name, $state);
        $content .= '</div>';
        
        $content .= '<div class="list-item">';
        $content .= '<h3>2. Timing is Critical</h3>';
        $content .= '<p>The snow will arrive during the day and intensify through the evening. Plan to be home before the worst conditions develop.</p>';
        $content .= '</div>';
        
        $content .= '<div class="list-item">';
        $content .= '<h3>3. Travel Will Be Treacherous</h3>';
        $content .= '<p>Road conditions will go from manageable to dangerous in a matter of hours. Avoid all non-essential travel once snow begins.</p>';
        $content .= '</div>';
        
        $content .= '<div class="list-item">';
        $content .= '<h3>4. School Closures Likely</h3>';
        $content .= '<p>Given the forecast, schools throughout the region are expected to close or move to virtual learning. Check with your district early.</p>';
        $content .= '</div>';
        
        $content .= '<div class="list-item">';
        $content .= '<h3>5. Power Outages Possible</h3>';
        $content .= '<p>Heavy snow can bring down power lines. Have emergency supplies ready, including flashlights, batteries, and a backup heating source.</p>';
        $content .= '</div>';
        
        $content .= '<div class="list-item">';
        $content .= '<h3>6. Stock Up Now</h3>';
        $content .= '<p>Get groceries and essentials before the storm. Once snow starts, stores may close early or become inaccessible.</p>';
        $content .= '</div>';
        
        $content .= '<div class="list-item">';
        $content .= '<h3>7. Protect Your Pipes</h3>';
        $content .= '<p>With dropping temperatures, prevent frozen pipes by letting faucets drip and keeping your home heated.</p>';
        $content .= '</div>';
        
        $content .= '<div class="list-item">';
        $content .= '<h3>8. Prepare Your Vehicle</h3>';
        $content .= '<p>If you must drive, ensure your car has a full tank of gas, working windshield wipers, and an emergency kit with blankets and snacks.</p>';
        $content .= '</div>';
        
        $content .= '<div class="list-item">';
        $content .= '<h3>9. Check on Neighbors</h3>';
        $content .= '<p>Elderly neighbors and those living alone may need help preparing. A quick check-in could make a big difference.</p>';
        $content .= '</div>';
        
        $content .= '<div class="list-item">';
        $content .= '<h3>10. Stay Informed</h3>';
        $content .= '<p>Weather conditions can change. Keep monitoring forecasts and be ready to adjust your plans as new information becomes available.</p>';
        $content .= '</div>';
        
        $content .= '</div>';
        
        return array(
            'title' => $title,
            'content' => $content,
            'format' => 'listicle'
        );
    }
    
    /**
     * Generate COMPARISON format
     */
    private function generate_comparison_format($location_data, $weather_data, $previous_article_id = null) {
        $location_name = isset($location_data['name']) ? $location_data['name'] : 'Unknown';
        $state = isset($location_data['state_name']) ? $location_data['state_name'] : '';
        $snow_amount = isset($weather_data['snow_amount']) ? $weather_data['snow_amount'] : 0;
        
        $title = sprintf('Before vs. After: %s, %s Prepares for %s" Snow', $location_name, $state, $snow_amount);
        
        $content = '<div class="comparison-format">';
        $content .= sprintf('<h2>%s: Before the Storm vs. During the Storm</h2>', $location_name);
        
        $content .= '<p class="intro">See how conditions will change as the winter storm moves through our area.</p>';
        
        // Weather Comparison
        $content .= '<h3>☀️ ❄️ Weather Conditions</h3>';
        $content .= '<div class="comparison-grid">';
        $content .= '<div class="before">';
        $content .= '<h4>Before the Storm</h4>';
        $content .= '<ul>';
        $content .= '<li>Partly cloudy skies</li>';
        $content .= '<li>Temperatures in low 30s</li>';
        $content .= '<li>Light winds</li>';
        $content .= '<li>Good visibility</li>';
        $content .= '</ul>';
        $content .= '</div>';
        $content .= '<div class="after">';
        $content .= '<h4>During the Storm</h4>';
        $content .= '<ul>';
        $content .= '<li>Heavy snow falling</li>';
        $content .= '<li>Temperatures dropping to 20s</li>';
        $content .= '<li>Gusty winds 15-25 mph</li>';
        $content .= '<li>Visibility under 1/4 mile</li>';
        $content .= '</ul>';
        $content .= '</div>';
        $content .= '</div>';
        
        // Road Conditions
        $content .= '<h3>🚗 Road Conditions</h3>';
        $content .= '<div class="comparison-grid">';
        $content .= '<div class="before">';
        $content .= '<h4>Before the Storm</h4>';
        $content .= '<ul>';
        $content .= '<li>Clear, dry pavement</li>';
        $content .= '<li>Normal speed limits</li>';
        $content .= '<li>All roads open</li>';
        $content .= '<li>Easy parking</li>';
        $content .= '</ul>';
        $content .= '</div>';
        $content .= '<div class="after">';
        $content .= '<h4>During the Storm</h4>';
        $content .= '<ul>';
        $content .= sprintf('<li>%s inches of snow on roads</li>', $snow_amount);
        $content .= '<li>Reduced speeds required</li>';
        $content .= '<li>Some road closures</li>';
        $content .= '<li>Parking bans in effect</li>';
        $content .= '</ul>';
        $content .= '</div>';
        $content .= '</div>';
        
        // Daily Life
        $content .= '<h3>🏡 Daily Life</h3>';
        $content .= '<div class="comparison-grid">';
        $content .= '<div class="before">';
        $content .= '<h4>Before the Storm</h4>';
        $content .= '<ul>';
        $content .= '<li>Normal business hours</li>';
        $content .= '<li>Schools in session</li>';
        $content .= '<li>Full public transit</li>';
        $content .= '<li>Regular activities</li>';
        $content .= '</ul>';
        $content .= '</div>';
        $content .= '<div class="after">';
        $content .= '<h4>During the Storm</h4>';
        $content .= '<ul>';
        $content .= '<li>Early closings common</li>';
        $content .= '<li>Schools closed/virtual</li>';
        $content .= '<li>Limited transit service</li>';
        $content .= '<li>Events cancelled</li>';
        $content .= '</ul>';
        $content .= '</div>';
        $content .= '</div>';
        
        $content .= sprintf('<p class="comparison-summary">The transformation from normal conditions to winter storm impacts will happen quickly in %s. Be prepared for significant changes.</p>', $location_name);
        
        $content .= '</div>';
        
        return array(
            'title' => $title,
            'content' => $content,
            'format' => 'comparison'
        );
    }
    
    /**
     * Generate STANDARD format
     */
    private function generate_standard_format($location_data, $weather_data, $previous_article_id = null) {
        $location_name = isset($location_data['name']) ? $location_data['name'] : 'Unknown';
        $state = isset($location_data['state_name']) ? $location_data['state_name'] : '';
        $snow_amount = isset($weather_data['snow_amount']) ? $weather_data['snow_amount'] : 0;
        $county = isset($location_data['county']) ? $location_data['county'] : '';
        
        $title = sprintf('%s, %s Braces for %s Inches of Snow', $location_name, $state, $snow_amount);
        
        $content = '<div class="standard-format">';
        $content .= sprintf('<h2>Winter Storm Approaches %s</h2>', $location_name);
        
        $content .= sprintf('<p>%s, %s is preparing for a significant winter weather event as meteorologists forecast %s inches of snow in the coming hours. ', $location_name, $state, $snow_amount);
        $content .= 'Residents are urged to take precautions and prepare for hazardous conditions.</p>';
        
        $content .= '<h3>Forecast Details</h3>';
        $content .= '<p>The National Weather Service has issued alerts for the region. Snow is expected to begin in the late morning, ';
        $content .= 'intensifying throughout the afternoon and evening. The heaviest snowfall rates are anticipated during the overnight hours.</p>';
        
        $content .= '<h3>Expected Impacts</h3>';
        $content .= '<p>Transportation will be significantly affected by this storm. Road conditions will deteriorate rapidly, ';
        $content .= 'making travel dangerous. Local authorities are advising against all non-essential travel once the snow begins.</p>';
        
        $content .= sprintf('<p>Schools in %s and throughout %s County are monitoring the situation closely. ', $location_name, $county);
        $content .= 'Many districts are expected to announce closures or transitions to remote learning.</p>';
        
        $content .= '<h3>Preparation Steps</h3>';
        $content .= '<p>Officials recommend that residents stock up on essential supplies, including food, water, and medications. ';
        $content .= 'Ensure you have flashlights, batteries, and a battery-powered radio in case of power outages. ';
        $content .= 'Keep electronic devices fully charged.</p>';
        
        $content .= '<p>If you must travel, make sure your vehicle is equipped with an emergency kit including blankets, non-perishable food, ';
        $content .= 'water, and a first-aid kit. Let someone know your route and expected arrival time.</p>';
        
        $content .= '<h3>Stay Informed</h3>';
        $content .= sprintf('<p>Residents of %s should continue to monitor local weather forecasts and official communications for updates. ', $location_name);
        $content .= 'Conditions can change rapidly during winter storms.</p>';
        
        $content .= '</div>';
        
        return array(
            'title' => $title,
            'content' => $content,
            'format' => 'standard'
        );
    }
}
