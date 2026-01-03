# Snow Alerts Plugin for WordPress

A fully functional WordPress plugin that automatically generates unique, AI-powered articles about snow forecasts and winter storm alerts for 120+ major US cities.

## Features

### 🌨️ Multi-API Integration
- **WeatherAPI.com** - 7-day snow forecasts
- **Weather.gov (NWS)** - Official weather alerts
- **OpenAI GPT-3.5** - Unique article generation
- **Windy.com** - Interactive weather maps (optional)

### 🔒 Security First
- AES-256-CBC encryption for API keys
- WordPress nonce verification
- Input sanitization & output escaping
- No hardcoded credentials
- Secure WordPress HTTP API usage

### 📝 Content Generation
- **Unique Titles** - AI-generated, max 60 characters
- **800-1200 Words** - Fully customizable range
- **SEO Optimized** - Meta descriptions, keywords
- **Schema.org Markup** - Article + WeatherForecast schemas
- **Duplicate Prevention** - Hash-based uniqueness checking
- **Internal Linking** - Related articles automatically linked

### 🎯 SEO & Discovery
- Schema.org structured data
- Open Graph tags for social sharing
- Twitter Cards support
- Google Discover optimization
- Automatic sitemap compatibility
- Keyword generation

### 🏙️ City Coverage
- 120+ US cities with 100,000+ population
- Focus on snow-prone regions
- Coordinates for accurate forecasting
- Smart city selection algorithm

### ⚙️ Automation
- Scheduled article generation
- Configurable intervals (30 min to daily)
- Minimum snowfall thresholds
- Automatic API rate limiting
- Weather data caching (1 hour)

### 📊 Admin Dashboard
- Real-time statistics
- Recent articles table
- API activity logs
- Manual generation button
- API status indicators

## Requirements

- **WordPress** 5.8 or higher
- **PHP** 7.4 or higher
- **MySQL** 5.6 or higher
- **API Keys** (required):
  - WeatherAPI.com (free tier available)
  - OpenAI Platform API
- **Optional**:
  - Windy.com API key

## Installation

### 1. Upload Plugin

#### Via WordPress Admin:
1. Download the `snow-alerts-plugin.zip`
2. Go to **WordPress Admin** → **Plugins** → **Add New**
3. Click **Upload Plugin**
4. Choose the ZIP file and click **Install Now**
5. Click **Activate**

#### Via FTP:
1. Unzip `snow-alerts-plugin.zip`
2. Upload `snow-alerts-plugin` folder to `/wp-content/plugins/`
3. Go to **WordPress Admin** → **Plugins**
4. Activate **Snow Alerts Plugin**

### 2. Configure API Keys

#### Get WeatherAPI.com Key (Required):
1. Sign up at [weatherapi.com](https://www.weatherapi.com/)
2. Get your free API key from dashboard
3. Go to **WordPress Admin** → **Snow Alerts** → **Settings**
4. Enter WeatherAPI key
5. Click **Save Key** and **Test Connection**

#### Get OpenAI API Key (Required):
1. Sign up at [platform.openai.com](https://platform.openai.com/)
2. Go to **API Keys** section
3. Create a new API key (starts with `sk-`)
4. Go to **Snow Alerts** → **Settings** in WordPress
5. Enter OpenAI key
6. Click **Save Key** and **Test Connection**

#### Get Windy API Key (Optional):
1. Sign up at [api.windy.com](https://api.windy.com/)
2. Get your API key
3. Enter in **Snow Alerts** → **Settings**

### 3. Configure Settings

Go to **Snow Alerts** → **Settings**:

- **Enable Automation**: Turn on/off scheduled generation
- **Schedule Interval**: How often to generate articles
  - Every 30 minutes
  - Hourly
  - Every 2 hours
  - Every 6 hours
  - Twice daily
  - Daily
- **Articles Per Run**: Number of articles per schedule (1-20)
- **Minimum Snowfall**: Threshold in inches (default: 2.0)
- **Word Count Range**: Min/max words (default: 800-1200)

Click **Save Settings** to apply changes.

## Usage

### Manual Generation

1. Go to **Snow Alerts** → **Dashboard**
2. Enter number of articles to generate (1-20)
3. Click **Generate Now**
4. Wait for processing (may take 2-5 minutes)
5. View newly created articles in the table

### Automated Generation

Once configured, the plugin will:
1. Run on your chosen schedule
2. Check 120+ cities for snow forecasts
3. Identify cities with significant snowfall (>2 inches default)
4. Generate unique articles only for cities with active snow
5. Skip cities that had articles in past 7 days
6. Publish directly to your WordPress site

### Viewing Articles

Generated articles are published as regular WordPress posts with:
- Category: "Snow Alerts" (auto-created)
- Tags: City, state, alert types
- Custom fields for tracking
- Embedded Windy weather maps
- Related articles links

## Database Tables

The plugin creates three custom tables:

### `wp_snow_alerts_articles`
Tracks all generated articles with metadata:
- Post ID
- City and state
- Title/content hashes
- Alert dates
- Snowfall dates and amounts
- Creation timestamp

### `wp_snow_alerts_logs`
API activity logging:
- API name (WeatherAPI, OpenAI, NWS)
- Endpoint called
- Request/response data
- Success/failure status
- Timestamps

### `wp_snow_alerts_weather_cache`
Weather data caching (1 hour TTL):
- City and state
- Complete weather data
- Cache timestamps
- Expiration times

## File Structure

```
snow-alerts-plugin/
├── snow-alerts-plugin.php           # Main plugin file
├── README.md                        # This file
├── includes/
│   ├── class-database.php           # Database management
│   ├── class-api-manager.php        # API key encryption
│   ├── class-city-manager.php       # City management
│   ├── class-content-generator.php  # AI content creation
│   ├── class-seo-optimizer.php      # SEO & Schema markup
│   ├── class-uniqueness-checker.php # Duplicate prevention
│   ├── class-weather-fetcher.php    # Weather API calls
│   └── class-scheduler.php          # Cron scheduling
├── admin/
│   ├── class-settings-page.php      # Settings interface
│   ├── class-dashboard.php          # Admin dashboard
│   └── views/
│       ├── settings.php             # Settings template
│       └── dashboard.php            # Dashboard template
├── assets/
│   ├── css/
│   │   ├── admin.css               # Admin styles
│   │   └── public.css              # Frontend styles
│   └── js/
│       ├── admin.js                # Admin JavaScript
│       └── public.js               # Frontend JavaScript
├── data/
│   └── us-cities-100k.json         # 120+ US cities database
└── templates/                       # (Future expansion)
```

## PHP 7.4 Compatibility

This plugin is fully compatible with PHP 7.4+ using proper syntax:

✅ **Uses `isset()` ternary** instead of `??` null coalescing operator
```php
// Correct (PHP 7.4 compatible):
$value = isset($array['key']) ? $array['key'] : 'default';

// NOT used (requires PHP 7.0+):
$value = $array['key'] ?? 'default';
```

✅ **Uses full ternary** instead of `?:` Elvis operator
```php
// Correct:
$value = $condition ? $condition : 'default';

// NOT used:
$value = $condition ?: 'default';
```

## Troubleshooting

### Plugin Won't Activate
- Check PHP version (7.4+ required)
- Verify WordPress version (5.8+ required)
- Check file permissions (should be 644 for files, 755 for directories)

### No Articles Generated
1. **Check API Keys**: Go to Settings, verify all keys are saved
2. **Test Connections**: Use "Test Connection" buttons
3. **Check Cities**: Ensure `data/us-cities-100k.json` exists
4. **Enable Scheduling**: Turn on "Enable Automation" in Settings
5. **Check Logs**: View API Activity in Dashboard for errors

### API Connection Errors
- **WeatherAPI**: Verify key is valid, check rate limits (free tier: 1M calls/month)
- **OpenAI**: Ensure key starts with `sk-`, check billing/quota
- **NWS**: No key needed, should always work (may be slow)

### Duplicate Content
The plugin uses SHA-256 hashing to prevent duplicates. If you see duplicates:
- Wait for different weather conditions
- Increase minimum snowfall threshold
- Extend the days between articles for same city

### Performance Issues
- **Increase Cache Time**: Edit `class-weather-fetcher.php` line 45 (default: 3600s)
- **Reduce Articles Per Run**: Lower in Settings (default: 5)
- **Increase Schedule Interval**: Use less frequent schedule

## Hooks & Filters

### Actions
```php
// After article generated
do_action('snow_alerts_article_generated', $post_id, $city, $weather_data);

// Before API call
do_action('snow_alerts_before_api_call', $api_name, $endpoint);

// After API call
do_action('snow_alerts_after_api_call', $api_name, $response);
```

### Filters
```php
// Modify word count
$word_count = apply_filters('snow_alerts_word_count', array('min' => 800, 'max' => 1200));

// Modify minimum snowfall
$min_snowfall = apply_filters('snow_alerts_min_snowfall', 2.0);

// Modify generated title
$title = apply_filters('snow_alerts_generated_title', $title, $city);

// Modify generated content
$content = apply_filters('snow_alerts_generated_content', $content, $city, $weather_data);
```

## Uninstallation

### Via WordPress Admin:
1. Deactivate plugin
2. Delete plugin

### What Gets Removed:
- All plugin files
- Options starting with `snow_alerts_`
- Scheduled cron jobs

### What Stays:
- Database tables (for data preservation)
- Generated posts/articles

### Complete Removal (Database):
Add this to `wp-config.php` before uninstalling:
```php
define('SNOW_ALERTS_REMOVE_DATA', true);
```

Then uninstall via WordPress admin. This will also drop all database tables.

## Security Considerations

### API Key Storage
- All API keys encrypted with AES-256-CBC
- Uses WordPress authentication keys as encryption base
- Keys never stored in plaintext
- Keys never appear in database exports

### Input Validation
- All user inputs sanitized
- WordPress nonces on all forms
- AJAX requests verified
- SQL injection prevention via `$wpdb->prepare()`

### Output Escaping
- All outputs escaped (`esc_html`, `esc_attr`, `esc_url`)
- Content sanitized with `wp_kses_post`
- No eval() or dangerous functions

## Support

### Documentation
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WeatherAPI Documentation](https://www.weatherapi.com/docs/)
- [OpenAI API Documentation](https://platform.openai.com/docs/)

### Common Issues
See **Troubleshooting** section above

## License

GPL v2 or later

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

## Credits

- Weather data: WeatherAPI.com, Weather.gov (NOAA)
- Content generation: OpenAI GPT-3.5
- Interactive maps: Windy.com
- Built for WordPress

## Changelog

### Version 1.0.0 (2024-01-03)
- Initial release
- Multi-API integration (WeatherAPI, NWS, OpenAI)
- 120+ US cities coverage
- Automated article generation
- SEO optimization with Schema.org
- Duplicate prevention system
- Admin dashboard with statistics
- Encrypted API key storage
- PHP 7.4+ compatibility
- WordPress 5.8+ compatibility

---

**Note**: This is a production-ready plugin. All API keys are required for operation. The free tiers of WeatherAPI and OpenAI should be sufficient for moderate usage (5-10 articles per day).
