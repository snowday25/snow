<?php
/**
 * Dashboard View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors('snow_alerts_messages'); ?>
    
    <div class="snow-alerts-dashboard">
        
        <h2>Statistics</h2>
        <div class="snow-alerts-stats">
            <div class="stat-box">
                <div class="stat-label">Total Articles</div>
                <div class="stat-value"><?php echo esc_html(isset($stats['total_articles']) ? $stats['total_articles'] : 0); ?></div>
            </div>
            
            <div class="stat-box">
                <div class="stat-label">Articles Today</div>
                <div class="stat-value"><?php echo esc_html(isset($stats['articles_today']) ? $stats['articles_today'] : 0); ?></div>
            </div>
            
            <div class="stat-box">
                <div class="stat-label">Total Locations</div>
                <div class="stat-value"><?php echo esc_html(isset($stats['total_locations']) ? $stats['total_locations'] : 0); ?></div>
            </div>
            
            <div class="stat-box">
                <div class="stat-label">API Calls Today</div>
                <div class="stat-value"><?php echo esc_html(isset($stats['api_calls_today']) ? $stats['api_calls_today'] : 0); ?></div>
            </div>
            
            <div class="stat-box">
                <div class="stat-label">API Success Rate</div>
                <div class="stat-value"><?php echo esc_html(isset($stats['success_rate']) ? $stats['success_rate'] : 0); ?>%</div>
            </div>
        </div>
        
        <h2>Manual Generation</h2>
        <form method="post" action="">
            <?php wp_nonce_field('snow_alerts_manual_generate', 'snow_alerts_generate_nonce'); ?>
            <p>Generate a new article manually. This will check a random location for snow and create an article if conditions are met.</p>
            <?php submit_button('Generate Article Now', 'primary', 'submit', false); ?>
        </form>
        
        <h2>Recent Articles</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Location</th>
                    <th>Snow Amount</th>
                    <th>Format</th>
                    <th>Generated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_articles)): ?>
                    <?php foreach ($recent_articles as $article): ?>
                        <?php 
                        $post_id = isset($article['post_id']) ? $article['post_id'] : 0;
                        $post_title = get_the_title($post_id);
                        ?>
                        <tr>
                            <td><?php echo esc_html(isset($article['id']) ? $article['id'] : ''); ?></td>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>" target="_blank">
                                        <?php echo esc_html($post_title); ?>
                                    </a>
                                </strong>
                            </td>
                            <td>
                                <?php 
                                echo esc_html(isset($article['location_name']) ? $article['location_name'] : ''); 
                                echo ', ';
                                echo esc_html(isset($article['location_state']) ? $article['location_state'] : '');
                                ?>
                            </td>
                            <td><?php echo esc_html(isset($article['snow_amount']) ? number_format($article['snow_amount'], 1) : '0'); ?>"</td>
                            <td><?php echo esc_html(isset($article['content_format']) ? ucfirst($article['content_format']) : 'Standard'); ?></td>
                            <td><?php echo esc_html(isset($article['generated_at']) ? date('M j, Y g:i a', strtotime($article['generated_at'])) : ''); ?></td>
                            <td>
                                <a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>">Edit</a> |
                                <a href="<?php echo esc_url(get_permalink($post_id)); ?>" target="_blank">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No articles generated yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <h2>System Information</h2>
        <table class="wp-list-table widefat">
            <tbody>
                <tr>
                    <th>Plugin Version</th>
                    <td><?php echo esc_html(SNOW_ALERTS_VERSION); ?></td>
                </tr>
                <tr>
                    <th>WordPress Version</th>
                    <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                </tr>
                <tr>
                    <th>PHP Version</th>
                    <td><?php echo esc_html(phpversion()); ?></td>
                </tr>
                <tr>
                    <th>Auto-Generation</th>
                    <td><?php echo get_option('snow_alerts_auto_generation_enabled', false) ? 'Enabled' : 'Disabled'; ?></td>
                </tr>
                <tr>
                    <th>Check Interval</th>
                    <td><?php echo esc_html(ucfirst(str_replace('_', ' ', get_option('snow_alerts_check_interval', 'hourly')))); ?></td>
                </tr>
            </tbody>
        </table>
        
    </div>
</div>

<style>
.snow-alerts-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
}

.stat-label {
    font-size: 14px;
    color: #646970;
    margin-bottom: 10px;
}

.stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #1d2327;
}
</style>
