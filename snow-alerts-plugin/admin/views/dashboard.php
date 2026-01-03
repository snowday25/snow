<?php
/**
 * Dashboard Page Template
 *
 * @package Snow_Alerts
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap snow-alerts-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Statistics Cards -->
    <div class="snow-alerts-stats-grid">
        <div class="snow-alerts-stat-card">
            <div class="stat-icon"><span class="dashicons dashicons-edit-large"></span></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo esc_html($stats['total_articles']); ?></div>
                <div class="stat-label"><?php esc_html_e('Total Articles', 'snow-alerts'); ?></div>
            </div>
        </div>
        
        <div class="snow-alerts-stat-card">
            <div class="stat-icon"><span class="dashicons dashicons-calendar-alt"></span></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo esc_html($stats['articles_today']); ?></div>
                <div class="stat-label"><?php esc_html_e('Articles Today', 'snow-alerts'); ?></div>
            </div>
        </div>
        
        <div class="snow-alerts-stat-card">
            <div class="stat-icon"><span class="dashicons dashicons-chart-line"></span></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo esc_html($stats['articles_week']); ?></div>
                <div class="stat-label"><?php esc_html_e('Articles This Week', 'snow-alerts'); ?></div>
            </div>
        </div>
        
        <div class="snow-alerts-stat-card">
            <div class="stat-icon"><span class="dashicons dashicons-admin-site"></span></div>
            <div class="stat-content">
                <div class="stat-value"><?php echo esc_html($city_count); ?></div>
                <div class="stat-label"><?php esc_html_e('Monitored Cities', 'snow-alerts'); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Manual Generation Card -->
    <div class="snow-alerts-card">
        <h2><?php esc_html_e('Manual Article Generation', 'snow-alerts'); ?></h2>
        <p><?php esc_html_e('Generate articles immediately for cities with active snow forecasts.', 'snow-alerts'); ?></p>
        
        <div class="manual-generate-controls">
            <label for="generate-count"><?php esc_html_e('Number of articles:', 'snow-alerts'); ?></label>
            <input type="number" id="generate-count" value="5" min="1" max="20" class="small-text" />
            <button type="button" id="generate-now-btn" class="button button-primary">
                <span class="dashicons dashicons-update"></span>
                <?php esc_html_e('Generate Now', 'snow-alerts'); ?>
            </button>
        </div>
        
        <div id="generate-results" class="generate-results" style="display: none;"></div>
        
        <?php if ($next_run): ?>
            <p class="next-run-info">
                <span class="dashicons dashicons-clock"></span>
                <?php
                printf(
                    esc_html__('Next scheduled run: %s', 'snow-alerts'),
                    '<strong>' . esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_run)) . '</strong>'
                );
                ?>
            </p>
        <?php else: ?>
            <p class="next-run-info">
                <span class="dashicons dashicons-warning"></span>
                <?php esc_html_e('Automatic generation is not scheduled. Enable it in Settings.', 'snow-alerts'); ?>
            </p>
        <?php endif; ?>
    </div>
    
    <!-- API Status Card -->
    <div class="snow-alerts-card">
        <h2><?php esc_html_e('API Status', 'snow-alerts'); ?></h2>
        
        <div class="api-status-grid">
            <div class="api-status-item">
                <span class="api-name">WeatherAPI.com</span>
                <?php if (in_array('weatherapi', $configured_apis)): ?>
                    <span class="status-badge status-active"><?php esc_html_e('Active', 'snow-alerts'); ?></span>
                <?php else: ?>
                    <span class="status-badge status-inactive"><?php esc_html_e('Not Configured', 'snow-alerts'); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="api-status-item">
                <span class="api-name">OpenAI</span>
                <?php if (in_array('openai', $configured_apis)): ?>
                    <span class="status-badge status-active"><?php esc_html_e('Active', 'snow-alerts'); ?></span>
                <?php else: ?>
                    <span class="status-badge status-inactive"><?php esc_html_e('Not Configured', 'snow-alerts'); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="api-status-item">
                <span class="api-name">Weather.gov (NWS)</span>
                <span class="status-badge status-active"><?php esc_html_e('Always Available', 'snow-alerts'); ?></span>
            </div>
            
            <div class="api-status-item">
                <span class="api-name">Windy.com</span>
                <?php if (in_array('windy', $configured_apis)): ?>
                    <span class="status-badge status-active"><?php esc_html_e('Active', 'snow-alerts'); ?></span>
                <?php else: ?>
                    <span class="status-badge status-optional"><?php esc_html_e('Optional', 'snow-alerts'); ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="api-calls-summary">
            <p>
                <strong><?php esc_html_e('API Calls:', 'snow-alerts'); ?></strong>
                <?php echo esc_html($stats['total_api_calls']); ?> <?php esc_html_e('total', 'snow-alerts'); ?>
                (<span style="color: green;"><?php echo esc_html($stats['successful_api_calls']); ?> <?php esc_html_e('successful', 'snow-alerts'); ?></span>,
                <span style="color: red;"><?php echo esc_html($stats['failed_api_calls']); ?> <?php esc_html_e('failed', 'snow-alerts'); ?></span>)
            </p>
        </div>
    </div>
    
    <!-- Recent Articles -->
    <div class="snow-alerts-card">
        <h2><?php esc_html_e('Recent Articles', 'snow-alerts'); ?></h2>
        
        <?php if (!empty($recent_articles)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Title', 'snow-alerts'); ?></th>
                        <th><?php esc_html_e('City', 'snow-alerts'); ?></th>
                        <th><?php esc_html_e('State', 'snow-alerts'); ?></th>
                        <th><?php esc_html_e('Snowfall', 'snow-alerts'); ?></th>
                        <th><?php esc_html_e('Created', 'snow-alerts'); ?></th>
                        <th><?php esc_html_e('Actions', 'snow-alerts'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_articles as $article): ?>
                        <tr>
                            <td>
                                <?php
                                $post_title = get_the_title($article->post_id);
                                echo esc_html($post_title ? $post_title : __('(No title)', 'snow-alerts'));
                                ?>
                            </td>
                            <td><?php echo esc_html($article->city); ?></td>
                            <td><?php echo esc_html($article->state); ?></td>
                            <td>
                                <?php
                                if ($article->snowfall_amount) {
                                    echo esc_html(number_format($article->snowfall_amount, 1)) . ' in';
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($article->created_at))); ?></td>
                            <td>
                                <a href="<?php echo esc_url(get_permalink($article->post_id)); ?>" class="button button-small" target="_blank">
                                    <?php esc_html_e('View', 'snow-alerts'); ?>
                                </a>
                                <a href="<?php echo esc_url(get_edit_post_link($article->post_id)); ?>" class="button button-small">
                                    <?php esc_html_e('Edit', 'snow-alerts'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php esc_html_e('No articles generated yet.', 'snow-alerts'); ?></p>
        <?php endif; ?>
    </div>
    
    <!-- Recent API Activity -->
    <div class="snow-alerts-card">
        <h2><?php esc_html_e('Recent API Activity', 'snow-alerts'); ?></h2>
        
        <?php if (!empty($recent_logs)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('API', 'snow-alerts'); ?></th>
                        <th><?php esc_html_e('Endpoint', 'snow-alerts'); ?></th>
                        <th><?php esc_html_e('Status', 'snow-alerts'); ?></th>
                        <th><?php esc_html_e('Response', 'snow-alerts'); ?></th>
                        <th><?php esc_html_e('Time', 'snow-alerts'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_logs as $log): ?>
                        <tr>
                            <td><?php echo esc_html($log->api_name); ?></td>
                            <td><small><?php echo esc_html(strlen($log->endpoint) > 50 ? substr($log->endpoint, 0, 50) . '...' : $log->endpoint); ?></small></td>
                            <td>
                                <?php if ($log->success): ?>
                                    <span class="status-badge status-success"><?php esc_html_e('Success', 'snow-alerts'); ?></span>
                                <?php else: ?>
                                    <span class="status-badge status-error"><?php esc_html_e('Failed', 'snow-alerts'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($log->response_code ? $log->response_code : '—'); ?></td>
                            <td><small><?php echo esc_html(date_i18n(get_option('time_format'), strtotime($log->created_at))); ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php esc_html_e('No API activity logged yet.', 'snow-alerts'); ?></p>
        <?php endif; ?>
    </div>
</div>
