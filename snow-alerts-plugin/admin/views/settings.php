<?php
/**
 * Settings Page View
 */

if (!defined('ABSPATH')) {
    exit;
}

$api_manager = new Snow_Alerts_API_Manager();
$location_manager = new Snow_Alerts_Location_Manager();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors('snow_alerts_messages'); ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('snow_alerts_save_settings', 'snow_alerts_settings_nonce'); ?>
        
        <h2 class="title">API Configuration</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="weatherapi_key">WeatherAPI.com Key</label>
                </th>
                <td>
                    <input type="text" id="weatherapi_key" name="weatherapi_key" 
                           value="<?php echo esc_attr($api_manager->get_api_key('weatherapi')); ?>" 
                           class="regular-text" placeholder="32-character alphanumeric key">
                    <p class="description">
                        Get your free API key at <a href="https://www.weatherapi.com/signup.aspx" target="_blank">WeatherAPI.com</a>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="openai_key">OpenAI API Key</label>
                </th>
                <td>
                    <input type="text" id="openai_key" name="openai_key" 
                           value="<?php echo esc_attr($api_manager->get_api_key('openai')); ?>" 
                           class="regular-text" placeholder="sk-...">
                    <p class="description">
                        Get your API key at <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="unsplash_key">Unsplash API Key (Optional)</label>
                </th>
                <td>
                    <input type="text" id="unsplash_key" name="unsplash_key" 
                           value="<?php echo esc_attr($api_manager->get_api_key('unsplash')); ?>" 
                           class="regular-text">
                    <p class="description">
                        Get your access key at <a href="https://unsplash.com/developers" target="_blank">Unsplash Developers</a>
                    </p>
                </td>
            </tr>
        </table>
        
        <h2 class="title">Generation Settings</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="auto_generation_enabled">Auto-Generation</label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="auto_generation_enabled" name="auto_generation_enabled" value="1" 
                               <?php checked(get_option('snow_alerts_auto_generation_enabled', false), 1); ?>>
                        Enable automatic article generation
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="check_interval">Check Interval</label>
                </th>
                <td>
                    <select id="check_interval" name="check_interval">
                        <option value="thirty_minutes" <?php selected(get_option('snow_alerts_check_interval', 'hourly'), 'thirty_minutes'); ?>>Every 30 Minutes</option>
                        <option value="hourly" <?php selected(get_option('snow_alerts_check_interval', 'hourly'), 'hourly'); ?>>Hourly</option>
                        <option value="twicedaily" <?php selected(get_option('snow_alerts_check_interval', 'hourly'), 'twicedaily'); ?>>Twice Daily</option>
                        <option value="daily" <?php selected(get_option('snow_alerts_check_interval', 'hourly'), 'daily'); ?>>Daily</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="minimum_snow_amount">Minimum Snow Amount</label>
                </th>
                <td>
                    <input type="number" id="minimum_snow_amount" name="minimum_snow_amount" 
                           value="<?php echo esc_attr(get_option('snow_alerts_minimum_snow_amount', 2)); ?>" 
                           min="0" step="0.5" class="small-text"> inches
                </td>
            </tr>
        </table>
        
        <h2 class="title">Content Settings</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="author_name">Author Name</label>
                </th>
                <td>
                    <input type="text" id="author_name" name="author_name" 
                           value="<?php echo esc_attr(get_option('snow_alerts_author_name', get_bloginfo('name'))); ?>" 
                           class="regular-text">
                </td>
            </tr>
        </table>
        
        <h2 class="title">Social Media URLs</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="facebook_url">Facebook URL</label>
                </th>
                <td>
                    <input type="url" id="facebook_url" name="facebook_url" 
                           value="<?php echo esc_attr(get_option('snow_alerts_facebook_url', '')); ?>" 
                           class="regular-text" placeholder="https://facebook.com/yourpage">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="twitter_url">Twitter URL</label>
                </th>
                <td>
                    <input type="url" id="twitter_url" name="twitter_url" 
                           value="<?php echo esc_attr(get_option('snow_alerts_twitter_url', '')); ?>" 
                           class="regular-text" placeholder="https://twitter.com/yourhandle">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="instagram_url">Instagram URL</label>
                </th>
                <td>
                    <input type="url" id="instagram_url" name="instagram_url" 
                           value="<?php echo esc_attr(get_option('snow_alerts_instagram_url', '')); ?>" 
                           class="regular-text" placeholder="https://instagram.com/yourprofile">
                </td>
            </tr>
        </table>
        
        <h2 class="title">Location Database</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="import_locations">Import Locations</label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" id="import_locations" name="import_locations" value="1">
                        Import locations from JSON file
                    </label>
                    <p class="description">
                        Current location count: <?php echo esc_html($location_manager->get_location_count()); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <?php submit_button('Save Settings'); ?>
    </form>
</div>
