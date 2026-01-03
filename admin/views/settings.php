<?php
/**
 * Admin Settings Page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Save settings if form is submitted
if (isset($_POST['snow_alerts_settings_submit']) && check_admin_referer('snow_alerts_settings_nonce')) {
    // Save API keys
    if (isset($_POST['snow_alerts_unsplash_api_key'])) {
        update_option('snow_alerts_unsplash_api_key', sanitize_text_field($_POST['snow_alerts_unsplash_api_key']));
    }
    
    // Save SEO settings
    if (isset($_POST['snow_alerts_enable_indexnow'])) {
        update_option('snow_alerts_enable_indexnow', true);
    } else {
        update_option('snow_alerts_enable_indexnow', false);
    }
    
    if (isset($_POST['snow_alerts_author_name'])) {
        update_option('snow_alerts_author_name', sanitize_text_field($_POST['snow_alerts_author_name']));
    }
    
    if (isset($_POST['snow_alerts_facebook_url'])) {
        update_option('snow_alerts_facebook_url', esc_url_raw($_POST['snow_alerts_facebook_url']));
    }
    
    if (isset($_POST['snow_alerts_twitter_url'])) {
        update_option('snow_alerts_twitter_url', esc_url_raw($_POST['snow_alerts_twitter_url']));
    }
    
    if (isset($_POST['snow_alerts_instagram_url'])) {
        update_option('snow_alerts_instagram_url', esc_url_raw($_POST['snow_alerts_instagram_url']));
    }
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully.', 'snow-alerts') . '</p></div>';
}

// Get current settings
$unsplash_key = get_option('snow_alerts_unsplash_api_key', '');
$enable_indexnow = get_option('snow_alerts_enable_indexnow', true);
$author_name = get_option('snow_alerts_author_name', 'Weather Team');
$facebook_url = get_option('snow_alerts_facebook_url', '');
$twitter_url = get_option('snow_alerts_twitter_url', '');
$instagram_url = get_option('snow_alerts_instagram_url', '');
$indexnow_key = get_option('snow_alerts_indexnow_key', '');
?>

<div class="wrap">
    <h1><?php echo esc_html__('Snow Alerts Settings', 'snow-alerts'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('snow_alerts_settings_nonce'); ?>
        
        <h2><?php echo esc_html__('API Keys', 'snow-alerts'); ?></h2>
        <p class="description">
            <?php echo esc_html__('Configure API keys for external services to enhance functionality.', 'snow-alerts'); ?>
        </p>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="snow_alerts_unsplash_api_key">
                        <?php echo esc_html__('Unsplash API Key', 'snow-alerts'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" 
                           id="snow_alerts_unsplash_api_key" 
                           name="snow_alerts_unsplash_api_key" 
                           value="<?php echo esc_attr($unsplash_key); ?>" 
                           class="regular-text" />
                    <p class="description">
                        <?php echo esc_html__('Optional. Get your free API key from unsplash.com/developers. Used for high-quality featured images.', 'snow-alerts'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <h2><?php echo esc_html__('SEO Settings', 'snow-alerts'); ?></h2>
        <p class="description">
            <?php echo esc_html__('Configure SEO features for better search engine visibility and faster indexing.', 'snow-alerts'); ?>
        </p>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <?php echo esc_html__('IndexNow', 'snow-alerts'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox" 
                               id="snow_alerts_enable_indexnow" 
                               name="snow_alerts_enable_indexnow" 
                               value="1" 
                               <?php checked($enable_indexnow, true); ?> />
                        <?php echo esc_html__('Enable IndexNow API for instant indexing', 'snow-alerts'); ?>
                    </label>
                    <p class="description">
                        <?php echo esc_html__('Automatically submit new articles to Bing, Yandex, and other search engines for near-instant indexing.', 'snow-alerts'); ?>
                    </p>
                    <?php if (!empty($indexnow_key)) : ?>
                        <p class="description">
                            <strong><?php echo esc_html__('IndexNow Key:', 'snow-alerts'); ?></strong>
                            <code><?php echo esc_html($indexnow_key); ?></code>
                            <br>
                            <small><?php echo esc_html__('Key file location:', 'snow-alerts'); ?> 
                            <code><?php echo esc_html(home_url() . '/' . $indexnow_key . '.txt'); ?></code></small>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="snow_alerts_author_name">
                        <?php echo esc_html__('Author Name', 'snow-alerts'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" 
                           id="snow_alerts_author_name" 
                           name="snow_alerts_author_name" 
                           value="<?php echo esc_attr($author_name); ?>" 
                           class="regular-text" />
                    <p class="description">
                        <?php echo esc_html__('Default author name for articles (e.g., "Weather Team", "Editorial Staff").', 'snow-alerts'); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <h2><?php echo esc_html__('Social Media URLs (E-E-A-T Signals)', 'snow-alerts'); ?></h2>
        <p class="description">
            <?php echo esc_html__('Add your social media profiles to improve E-E-A-T (Experience, Expertise, Authoritativeness, Trustworthiness) signals.', 'snow-alerts'); ?>
        </p>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="snow_alerts_facebook_url">
                        <?php echo esc_html__('Facebook URL', 'snow-alerts'); ?>
                    </label>
                </th>
                <td>
                    <input type="url" 
                           id="snow_alerts_facebook_url" 
                           name="snow_alerts_facebook_url" 
                           value="<?php echo esc_attr($facebook_url); ?>" 
                           class="regular-text" 
                           placeholder="https://facebook.com/yourpage" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="snow_alerts_twitter_url">
                        <?php echo esc_html__('Twitter/X URL', 'snow-alerts'); ?>
                    </label>
                </th>
                <td>
                    <input type="url" 
                           id="snow_alerts_twitter_url" 
                           name="snow_alerts_twitter_url" 
                           value="<?php echo esc_attr($twitter_url); ?>" 
                           class="regular-text" 
                           placeholder="https://twitter.com/youraccount" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="snow_alerts_instagram_url">
                        <?php echo esc_html__('Instagram URL', 'snow-alerts'); ?>
                    </label>
                </th>
                <td>
                    <input type="url" 
                           id="snow_alerts_instagram_url" 
                           name="snow_alerts_instagram_url" 
                           value="<?php echo esc_attr($instagram_url); ?>" 
                           class="regular-text" 
                           placeholder="https://instagram.com/youraccount" />
                </td>
            </tr>
        </table>
        
        <h2><?php echo esc_html__('SEO Features', 'snow-alerts'); ?></h2>
        <div class="card">
            <h3><?php echo esc_html__('Active SEO Optimizations', 'snow-alerts'); ?></h3>
            <ul style="list-style: disc; padding-left: 20px;">
                <li>✅ <strong>IndexNow API</strong> - Instant indexing to Bing, Yandex, and partners</li>
                <li>✅ <strong>Enhanced Schema Markup</strong> - NewsArticle, Breadcrumb, FAQ, and WeatherForecast schemas</li>
                <li>✅ <strong>Google Discover Tags</strong> - Optimized meta tags for Google Discover</li>
                <li>✅ <strong>Optimized Headlines</strong> - 50-60 character headlines with power words</li>
                <li>✅ <strong>Meta Descriptions</strong> - 150-160 character descriptions for maximum CTR</li>
                <li>✅ <strong>Featured Images</strong> - 1200x630px images optimized for social sharing</li>
                <li>✅ <strong>E-E-A-T Signals</strong> - Author credentials and social proof</li>
                <li>✅ <strong>Core Web Vitals</strong> - Preconnect, DNS prefetch, lazy loading</li>
                <li>✅ <strong>Mobile-First Design</strong> - Responsive and fast-loading pages</li>
                <li>✅ <strong>Structured Data</strong> - JSON-LD schema for rich results</li>
            </ul>
        </div>
        
        <p class="submit">
            <input type="submit" 
                   name="snow_alerts_settings_submit" 
                   class="button button-primary" 
                   value="<?php echo esc_attr__('Save Settings', 'snow-alerts'); ?>" />
        </p>
    </form>
    
    <hr>
    
    <h2><?php echo esc_html__('Documentation', 'snow-alerts'); ?></h2>
    <div class="card">
        <h3><?php echo esc_html__('Quick Start Guide', 'snow-alerts'); ?></h3>
        <ol>
            <li><strong>Enable IndexNow:</strong> Check the IndexNow option above to enable instant search engine indexing.</li>
            <li><strong>Add Social Profiles:</strong> Enter your social media URLs to improve E-E-A-T signals.</li>
            <li><strong>Optional - Unsplash API:</strong> Get a free API key from Unsplash for high-quality images.</li>
            <li><strong>Publish Articles:</strong> Use the Snow_Alerts_Scheduler class to publish articles with full SEO.</li>
        </ol>
        
        <h3><?php echo esc_html__('Testing Your SEO', 'snow-alerts'); ?></h3>
        <ul>
            <li><strong>Schema Validation:</strong> <a href="https://search.google.com/test/rich-results" target="_blank">Google Rich Results Test</a></li>
            <li><strong>Page Speed:</strong> <a href="https://pagespeed.web.dev/" target="_blank">PageSpeed Insights</a></li>
            <li><strong>Mobile-Friendly:</strong> <a href="https://search.google.com/test/mobile-friendly" target="_blank">Mobile-Friendly Test</a></li>
        </ul>
    </div>
</div>

<style>
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.card h3 {
    margin-top: 0;
}

.card ul, .card ol {
    margin-bottom: 0;
}
</style>
