<?php
/**
 * Example Usage of Snow Alerts Plugin
 * 
 * This file demonstrates how to use the Snow Alerts plugin
 * to publish articles with full SEO optimization.
 * 
 * DO NOT include this file in production. This is for reference only.
 */

// This would typically be in your theme's functions.php or a custom plugin

/**
 * Example 1: Publish a standard snow alert article
 */
function example_publish_snow_alert() {
    // Article data
    $article_data = array(
        'title' => 'Winter Storm in New York',
        'content' => '<p>A major winter storm is approaching New York City, bringing significant snowfall and dangerous conditions.</p>
                      <p>The National Weather Service has issued a winter storm warning for the region, expecting 12-18 inches of snow between Tuesday evening and Thursday morning.</p>
                      <p>Residents are urged to prepare now and avoid unnecessary travel during the storm.</p>',
        'excerpt' => 'Major winter storm to bring 12-18 inches of snow to New York City area.',
        'format' => 'standard',
    );
    
    // Location data
    $location_data = array(
        'city' => 'New York',
        'state' => 'New York',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    );
    
    // Weather data
    $weather_data = array(
        'event_type' => 'Winter Storm',
        'snow_amount' => 15, // inches (average of 12-18)
        'alert_start' => '2024-01-02T18:00:00-05:00',
        'alert_end' => '2024-01-04T06:00:00-05:00',
        'details' => 'Heavy snowfall expected with hazardous travel conditions',
    );
    
    // Publish the article
    $post_id = Snow_Alerts_Scheduler::publish_article(
        $article_data,
        $location_data,
        $weather_data
    );
    
    if ($post_id) {
        echo 'Article published successfully! Post ID: ' . $post_id . "\n";
        echo 'URL: ' . get_permalink($post_id) . "\n";
    } else {
        echo 'Failed to publish article.' . "\n";
    }
    
    return $post_id;
}

/**
 * Example 2: Publish a Q&A format article
 */
function example_publish_qa_article() {
    // Article content in Q&A format
    $content = '<h3>When will the storm hit New York?</h3>
                <p>The winter storm is expected to arrive Tuesday evening around 6 PM EST and continue through Thursday morning.</p>
                
                <h3>How much snow is forecast?</h3>
                <p>The National Weather Service is forecasting 12-18 inches of snow for the New York City metro area, with higher amounts possible in some locations.</p>
                
                <h3>Should I travel during the storm?</h3>
                <p>No, authorities strongly advise against all non-essential travel during the storm due to hazardous road conditions and reduced visibility.</p>
                
                <h3>What should I do to prepare?</h3>
                <p>Stock up on essential supplies, charge electronic devices, and ensure you have a plan for heating and food in case of power outages.</p>';
    
    $article_data = array(
        'title' => 'New York Winter Storm FAQ',
        'content' => $content,
        'format' => 'qa', // This triggers FAQ schema generation
    );
    
    $location_data = array(
        'city' => 'New York',
        'state' => 'New York',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    );
    
    $weather_data = array(
        'event_type' => 'Winter Storm',
        'snow_amount' => 15,
        'details' => 'Major winter storm with heavy snowfall',
    );
    
    $post_id = Snow_Alerts_Scheduler::publish_article(
        $article_data,
        $location_data,
        $weather_data
    );
    
    return $post_id;
}

/**
 * Example 3: Update an existing article
 */
function example_update_article($post_id) {
    $article_data = array(
        'title' => 'Winter Storm Update: New York',
        'content' => '<p>Updated forecast shows storm intensifying, now expecting 18-24 inches of snow.</p>',
    );
    
    $location_data = array(
        'city' => 'New York',
        'state' => 'New York',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    );
    
    $weather_data = array(
        'event_type' => 'Winter Storm',
        'snow_amount' => 21, // Updated amount
        'alert_start' => '2024-01-02T18:00:00-05:00',
        'alert_end' => '2024-01-04T12:00:00-05:00',
        'details' => 'Storm intensifying - now expecting higher snowfall totals',
    );
    
    $updated = Snow_Alerts_Scheduler::update_article(
        $post_id,
        $article_data,
        $location_data,
        $weather_data
    );
    
    return $updated;
}

/**
 * Example 4: Manually optimize a headline
 */
function example_optimize_headline() {
    $base_headline = 'Winter Storm Expected in Boston';
    
    $location_data = array(
        'city' => 'Boston',
        'state' => 'Massachusetts',
    );
    
    $weather_data = array(
        'snow_amount' => 14, // Will determine severity as 'severe'
    );
    
    $severity = Snow_Alerts_Headline_Optimizer::determine_severity($weather_data);
    
    $optimized = Snow_Alerts_Headline_Optimizer::optimize_headline(
        $base_headline,
        $location_data,
        $severity
    );
    
    echo 'Original: ' . $base_headline . "\n";
    echo 'Optimized: ' . $optimized . "\n";
    echo 'Severity: ' . $severity . "\n";
}

/**
 * Example 5: Generate a meta description
 */
function example_generate_meta_description() {
    $location_data = array(
        'city' => 'Boston',
        'state' => 'Massachusetts',
    );
    
    $weather_data = array(
        'event_type' => 'Major Winter Storm',
        'snow_amount' => '12-18',
        'details' => 'Travel will be extremely dangerous with whiteout conditions expected',
    );
    
    // Try different template types
    $templates = array('urgency', 'specific', 'question', 'local');
    
    foreach ($templates as $template) {
        $meta = Snow_Alerts_Headline_Optimizer::create_meta_description(
            $location_data,
            $weather_data,
            $template
        );
        
        echo $template . ': ' . $meta . "\n";
    }
}

/**
 * Example 6: Generate featured image
 */
function example_generate_featured_image($post_id) {
    $location_data = array(
        'city' => 'Chicago',
        'state' => 'Illinois',
    );
    
    $weather_data = array(
        'event_type' => 'Blizzard',
        'snow_amount' => 18,
    );
    
    $image_id = Snow_Alerts_Image_Optimizer::generate_featured_image(
        $post_id,
        $location_data,
        $weather_data
    );
    
    if ($image_id) {
        echo 'Featured image created! Image ID: ' . $image_id . "\n";
        echo 'URL: ' . wp_get_attachment_url($image_id) . "\n";
    }
}

/**
 * Example 7: Hook into article publication
 */
function custom_action_on_article_publish($post_id, $location_data) {
    // Custom action when article is published
    error_log('Snow Alert published for ' . $location_data['city']);
    
    // You could send notifications, update external systems, etc.
}
add_action('snow_alerts_article_published', 'custom_action_on_article_publish', 10, 2);

/**
 * Example 8: Get all schemas for validation
 */
function example_get_schemas($post_id) {
    $schemas = Snow_Alerts_SEO_Optimizer::get_all_schemas($post_id);
    
    foreach ($schemas as $type => $schema) {
        echo "\n" . strtoupper($type) . " SCHEMA:\n";
        echo wp_json_encode($schema, JSON_PRETTY_PRINT) . "\n";
    }
    
    return $schemas;
}

/**
 * Example 9: Schedule article for future publication
 */
function example_schedule_article() {
    $article_data = array(
        'title' => 'Weekend Storm Forecast',
        'content' => '<p>A storm system is expected to impact the region this weekend.</p>',
    );
    
    $location_data = array(
        'city' => 'Denver',
        'state' => 'Colorado',
        'latitude' => 39.7392,
        'longitude' => -104.9903,
    );
    
    $weather_data = array(
        'event_type' => 'Winter Storm',
        'snow_amount' => 8,
    );
    
    // Schedule for 3 hours from now
    $publish_time = time() + (3 * HOUR_IN_SECONDS);
    
    $scheduled = Snow_Alerts_Scheduler::schedule_article(
        $article_data,
        $location_data,
        $weather_data,
        $publish_time
    );
    
    if ($scheduled) {
        echo 'Article scheduled for ' . date('Y-m-d H:i:s', $publish_time) . "\n";
    }
}

/**
 * Example 10: Batch publish multiple articles
 */
function example_batch_publish_articles($locations) {
    $post_ids = array();
    
    foreach ($locations as $location) {
        $article_data = array(
            'title' => 'Winter Storm Alert for ' . $location['city'],
            'content' => '<p>Winter storm approaching ' . $location['city'] . '.</p>',
        );
        
        $weather_data = array(
            'event_type' => 'Winter Storm',
            'snow_amount' => isset($location['snow_amount']) ? $location['snow_amount'] : 10,
        );
        
        $post_id = Snow_Alerts_Scheduler::publish_article(
            $article_data,
            $location,
            $weather_data
        );
        
        if ($post_id) {
            $post_ids[] = $post_id;
        }
        
        // Small delay to avoid overwhelming the server
        sleep(1);
    }
    
    return $post_ids;
}

// Example usage:
/*
$locations = array(
    array('city' => 'New York', 'state' => 'New York', 'latitude' => 40.7128, 'longitude' => -74.0060, 'snow_amount' => 15),
    array('city' => 'Boston', 'state' => 'Massachusetts', 'latitude' => 42.3601, 'longitude' => -71.0589, 'snow_amount' => 12),
    array('city' => 'Philadelphia', 'state' => 'Pennsylvania', 'latitude' => 39.9526, 'longitude' => -75.1652, 'snow_amount' => 10),
);

$published = example_batch_publish_articles($locations);
*/
