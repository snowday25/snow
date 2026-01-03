<?php
/**
 * Settings Page Template
 *
 * @package Snow_Alerts
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$schedule_enabled = get_option('snow_alerts_schedule_enabled', 'no');
$schedule_interval = get_option('snow_alerts_schedule_interval', 'hourly');
$articles_per_run = get_option('snow_alerts_articles_per_run', 5);
$min_snowfall = get_option('snow_alerts_min_snowfall', 2);
$word_count_min = get_option('snow_alerts_word_count_min', 800);
$word_count_max = get_option('snow_alerts_word_count_max', 1200);

// Get configured APIs
$has_weatherapi = Snow_Alerts_API_Manager::has_api_key('weatherapi');
$has_openai = Snow_Alerts_API_Manager::has_api_key('openai');
$has_windy = Snow_Alerts_API_Manager::has_api_key('windy');
?>

<div class="wrap snow-alerts-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="snow-alerts-grid">
        <!-- API Configuration -->
        <div class="snow-alerts-card">
            <h2><?php esc_html_e('API Configuration', 'snow-alerts'); ?></h2>
            <p><?php esc_html_e('Configure your API keys. All keys are encrypted before storage.', 'snow-alerts'); ?></p>
            
            <!-- WeatherAPI -->
            <div class="api-config-item">
                <h3>WeatherAPI.com <span class="required">*</span></h3>
                <p><?php esc_html_e('Get your API key from', 'snow-alerts'); ?> <a href="https://www.weatherapi.com/" target="_blank">weatherapi.com</a></p>
                <input type="password" id="weatherapi-key" class="regular-text" placeholder="<?php esc_attr_e('Enter API key', 'snow-alerts'); ?>" />
                <button type="button" class="button save-api-key" data-api="weatherapi">
                    <?php esc_html_e('Save Key', 'snow-alerts'); ?>
                </button>
                <button type="button" class="button test-api-key" data-api="weatherapi">
                    <?php esc_html_e('Test Connection', 'snow-alerts'); ?>
                </button>
                <span class="status-indicator">
                    <?php if ($has_weatherapi): ?>
                        <span class="dashicons dashicons-yes-alt" style="color: green;"></span> <?php esc_html_e('Configured', 'snow-alerts'); ?>
                    <?php else: ?>
                        <span class="dashicons dashicons-warning" style="color: orange;"></span> <?php esc_html_e('Not configured', 'snow-alerts'); ?>
                    <?php endif; ?>
                </span>
            </div>
            
            <!-- OpenAI -->
            <div class="api-config-item">
                <h3>OpenAI <span class="required">*</span></h3>
                <p><?php esc_html_e('Get your API key from', 'snow-alerts'); ?> <a href="https://platform.openai.com/" target="_blank">platform.openai.com</a></p>
                <input type="password" id="openai-key" class="regular-text" placeholder="<?php esc_attr_e('Enter API key (starts with sk-)', 'snow-alerts'); ?>" />
                <button type="button" class="button save-api-key" data-api="openai">
                    <?php esc_html_e('Save Key', 'snow-alerts'); ?>
                </button>
                <button type="button" class="button test-api-key" data-api="openai">
                    <?php esc_html_e('Test Connection', 'snow-alerts'); ?>
                </button>
                <span class="status-indicator">
                    <?php if ($has_openai): ?>
                        <span class="dashicons dashicons-yes-alt" style="color: green;"></span> <?php esc_html_e('Configured', 'snow-alerts'); ?>
                    <?php else: ?>
                        <span class="dashicons dashicons-warning" style="color: orange;"></span> <?php esc_html_e('Not configured', 'snow-alerts'); ?>
                    <?php endif; ?>
                </span>
            </div>
            
            <!-- Windy (optional) -->
            <div class="api-config-item">
                <h3>Windy.com</h3>
                <p><?php esc_html_e('Optional - for interactive weather maps. Get key from', 'snow-alerts'); ?> <a href="https://api.windy.com/" target="_blank">api.windy.com</a></p>
                <input type="password" id="windy-key" class="regular-text" placeholder="<?php esc_attr_e('Enter API key (optional)', 'snow-alerts'); ?>" />
                <button type="button" class="button save-api-key" data-api="windy">
                    <?php esc_html_e('Save Key', 'snow-alerts'); ?>
                </button>
                <span class="status-indicator">
                    <?php if ($has_windy): ?>
                        <span class="dashicons dashicons-yes-alt" style="color: green;"></span> <?php esc_html_e('Configured', 'snow-alerts'); ?>
                    <?php else: ?>
                        <span class="dashicons dashicons-minus" style="color: gray;"></span> <?php esc_html_e('Optional', 'snow-alerts'); ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        
        <!-- Plugin Settings -->
        <div class="snow-alerts-card">
            <h2><?php esc_html_e('Plugin Settings', 'snow-alerts'); ?></h2>
            
            <form method="post" action="">
                <?php wp_nonce_field('snow_alerts_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="snow_alerts_schedule_enabled"><?php esc_html_e('Enable Automation', 'snow-alerts'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="snow_alerts_schedule_enabled" id="snow_alerts_schedule_enabled" value="yes" <?php checked($schedule_enabled, 'yes'); ?> />
                                <?php esc_html_e('Automatically generate articles on schedule', 'snow-alerts'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="snow_alerts_schedule_interval"><?php esc_html_e('Schedule Interval', 'snow-alerts'); ?></label>
                        </th>
                        <td>
                            <select name="snow_alerts_schedule_interval" id="snow_alerts_schedule_interval">
                                <option value="every_30_minutes" <?php selected($schedule_interval, 'every_30_minutes'); ?>><?php esc_html_e('Every 30 Minutes', 'snow-alerts'); ?></option>
                                <option value="hourly" <?php selected($schedule_interval, 'hourly'); ?>><?php esc_html_e('Hourly', 'snow-alerts'); ?></option>
                                <option value="every_2_hours" <?php selected($schedule_interval, 'every_2_hours'); ?>><?php esc_html_e('Every 2 Hours', 'snow-alerts'); ?></option>
                                <option value="every_6_hours" <?php selected($schedule_interval, 'every_6_hours'); ?>><?php esc_html_e('Every 6 Hours', 'snow-alerts'); ?></option>
                                <option value="twicedaily" <?php selected($schedule_interval, 'twicedaily'); ?>><?php esc_html_e('Twice Daily', 'snow-alerts'); ?></option>
                                <option value="daily" <?php selected($schedule_interval, 'daily'); ?>><?php esc_html_e('Daily', 'snow-alerts'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="snow_alerts_articles_per_run"><?php esc_html_e('Articles Per Run', 'snow-alerts'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="snow_alerts_articles_per_run" id="snow_alerts_articles_per_run" value="<?php echo esc_attr($articles_per_run); ?>" min="1" max="20" class="small-text" />
                            <p class="description"><?php esc_html_e('Number of articles to generate per scheduled run (1-20)', 'snow-alerts'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="snow_alerts_min_snowfall"><?php esc_html_e('Minimum Snowfall', 'snow-alerts'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="snow_alerts_min_snowfall" id="snow_alerts_min_snowfall" value="<?php echo esc_attr($min_snowfall); ?>" min="0" step="0.5" class="small-text" />
                            <span><?php esc_html_e('inches', 'snow-alerts'); ?></span>
                            <p class="description"><?php esc_html_e('Minimum snowfall amount to trigger article generation', 'snow-alerts'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e('Word Count Range', 'snow-alerts'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="snow_alerts_word_count_min" value="<?php echo esc_attr($word_count_min); ?>" min="400" max="2000" class="small-text" />
                            <span><?php esc_html_e('to', 'snow-alerts'); ?></span>
                            <input type="number" name="snow_alerts_word_count_max" value="<?php echo esc_attr($word_count_max); ?>" min="400" max="2000" class="small-text" />
                            <span><?php esc_html_e('words', 'snow-alerts'); ?></span>
                            <p class="description"><?php esc_html_e('Target word count for generated articles', 'snow-alerts'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="snow_alerts_settings_submit" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'snow-alerts'); ?>" />
                </p>
            </form>
        </div>
    </div>
    
    <div class="snow-alerts-info">
        <h3><?php esc_html_e('Important Information', 'snow-alerts'); ?></h3>
        <ul>
            <li><?php esc_html_e('All API keys are encrypted using AES-256-CBC encryption before storage.', 'snow-alerts'); ?></li>
            <li><?php esc_html_e('WeatherAPI and OpenAI keys are required for the plugin to function.', 'snow-alerts'); ?></li>
            <li><?php esc_html_e('The plugin monitors 120+ US cities with populations over 100,000.', 'snow-alerts'); ?></li>
            <li><?php esc_html_e('Articles are only generated for cities with significant snow forecasts.', 'snow-alerts'); ?></li>
            <li><?php esc_html_e('Duplicate content prevention ensures unique articles every time.', 'snow-alerts'); ?></li>
        </ul>
    </div>
</div>
