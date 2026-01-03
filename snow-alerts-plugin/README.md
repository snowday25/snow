# Snow Alerts WordPress Plugin

**Version:** 2.0.0  
**Requires:** WordPress 6.0+, PHP 7.4+  
**License:** GPL v2 or later

A comprehensive, production-ready WordPress plugin for automated snow alert content generation with AI, multi-API integration, and advanced SEO optimization.

## Features

### Core Capabilities
✅ **Multi-API Integration** - WeatherAPI.com, OpenAI GPT-4, Unsplash, NWS  
✅ **Encrypted Storage** - AES-256-CBC encryption for API keys  
✅ **7,000-15,000 Locations** - Coverage across 39 US snow-prone states  
✅ **Dual Content System** - AI generation with template fallback  
✅ **100% Duplicate Prevention** - Advanced fuzzy matching and n-gram analysis  
✅ **8 Content Formats** - Standard, update, timeline, Q&A, listicle, safety, impact, comparison  
✅ **Update Detection** - Automatic weather change monitoring (24-48hr window)  
✅ **Smart Scheduling** - WordPress cron integration with retry logic

### Content Generation
- AI-powered articles (800-1200 words) via OpenAI GPT-4
- Template-based fallback system (never fails)
- 50+ unique title variations
- 50 meta description templates
- Dynamic keyword generation
- Safety recommendations
- Internal bidirectional linking

### SEO & Discovery
- **4 Schema Types:** NewsArticle, Breadcrumb, FAQ, WeatherForecast
- **IndexNow API** for instant indexing (Bing, Yandex)
- **Google Discover** optimization
- Power word headlines with CTR optimization
- 1200x630px featured images from Unsplash
- E-E-A-T signals for authority
- Core Web Vitals optimization
- Open Graph + Twitter Cards
- Sitemap priority 0.9, hourly updates

### Anti-Duplication
- Hash-based quick lookup
- Fuzzy title matching (85% threshold)
- N-gram content similarity (70% Jaccard, 3-grams)
- 5 retry attempts with format variation
- Update detection (24-48hr window)
- Bidirectional article linking

## Installation

1. **Download** the plugin or clone the repository
2. **Upload** to `/wp-content/plugins/snow-alerts-plugin/`
3. **Activate** through WordPress admin
4. **Configure** API keys in Settings

### Requirements
- WordPress 6.0 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher
- OpenSSL extension (for encryption)
- JSON extension
- cURL extension

## Configuration

### API Keys

Navigate to **Snow Alerts → Settings** and configure:

1. **WeatherAPI.com Key** (Required)
   - Sign up at [WeatherAPI.com](https://www.weatherapi.com/signup.aspx)
   - Free tier: 1M calls/month
   - Add your 32-character key

2. **OpenAI API Key** (Required)
   - Get key at [OpenAI Platform](https://platform.openai.com/api-keys)
   - Starts with `sk-`
   - Used for GPT-4 content generation

3. **Unsplash API Key** (Optional)
   - Register at [Unsplash Developers](https://unsplash.com/developers)
   - Free tier: 50 requests/hour
   - Provides high-quality featured images

### Settings

**Generation Settings:**
- **Auto-Generation:** Enable/disable automatic article creation
- **Check Interval:** 30min, hourly, twice daily, or daily
- **Minimum Snow Amount:** Threshold in inches (default: 2")

**Content Settings:**
- **Author Name:** Displayed author for articles
- **Social Media URLs:** Facebook, Twitter, Instagram

**Location Database:**
- Import locations from JSON file
- Current count displayed

## Usage

### Automatic Generation

1. Enable **Auto-Generation** in settings
2. Set **Check Interval** (recommended: hourly)
3. Configure **Minimum Snow Amount**
4. Plugin will automatically:
   - Check random locations for snow
   - Generate articles when thresholds met
   - Publish with full SEO optimization
   - Submit to IndexNow for instant indexing

### Manual Generation

1. Go to **Snow Alerts → Dashboard**
2. Click **Generate Article Now**
3. System checks weather and creates article if conditions met

### Dashboard Statistics

Monitor plugin performance:
- Total articles generated
- Articles created today
- Active locations count
- API calls and success rate
- Recent articles table

## Database Tables

The plugin creates three tables:

1. **wp_snow_articles** - Article metadata and tracking
2. **wp_snow_cities** - Location database
3. **wp_snow_api_logs** - API call logging

## File Structure

```
snow-alerts-plugin/
├── snow-alerts-plugin.php       # Main plugin file
├── includes/                     # Core classes (13 files)
├── admin/                        # Admin interface
├── assets/                       # CSS and JavaScript
├── data/                         # JSON templates and locations
├── scripts/                      # Python processing scripts
├── README.md                     # This file
├── CHANGELOG.md                  # Version history
└── LICENSE                       # GPL v2 license
```

## Security

- AES-256-CBC encryption for API keys
- WordPress nonces for CSRF protection
- Prepared SQL statements (SQL injection prevention)
- Input sanitization and output escaping
- Secure file permissions

## Performance

- Lazy loading for images
- Preconnect hints for external APIs
- Cached location data
- Hash-first duplicate checking
- Optimized database queries
- Minimal HTTP requests

## Troubleshooting

### Articles Not Generating

1. **Check API keys** are configured correctly
2. **Verify** there's snow in the forecast (minimum threshold)
3. **Review** API logs in database for errors
4. **Ensure** cron is working: `wp cron event list`

### Duplicate Content

- Plugin has 100% duplicate prevention
- Uses fuzzy matching + n-gram analysis
- Retries up to 5 times with different formats
- Check uniqueness checker settings

### Image Issues

- Unsplash key is optional
- Fallback placeholder provided
- Check API call logs
- Verify media upload permissions

## Development

### PHP 7.4 Compatibility

The plugin is fully compatible with PHP 7.4:
- No `??` (null coalescing) operator
- No `?:` (Elvis) operator
- Proper `isset()` checks throughout
- WordPress coding standards

### Extending

Add custom content formats:
```php
// In class-content-variation-generator.php
private function generate_custom($location, $weather_data, $is_update, $parent_article) {
    // Your custom format logic
}
```

## Support

- **Issues:** Open on GitHub repository
- **Documentation:** See `/scripts/README.md` for data processing
- **WordPress:** Requires 6.0+ for full functionality

## Credits

- Weather data: [WeatherAPI.com](https://www.weatherapi.com/)
- AI content: [OpenAI](https://openai.com/)
- Images: [Unsplash](https://unsplash.com/)
- NWS data: [National Weather Service](https://www.weather.gov/)

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2024 Snow Alerts Team

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

---

**Made with ❄️ by the Snow Alerts Team**
