# Installation Guide - Snow Alerts Plugin

## Requirements

- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.6 or higher
- **Server:** Apache or Nginx with mod_rewrite enabled
- **PHP Extensions:** 
  - GD Library (for image optimization, optional)
  - cURL (for API calls)
  - JSON support

## Installation Steps

### 1. Upload Plugin Files

**Option A: Via WordPress Admin**
1. Download the plugin as a ZIP file
2. Go to WordPress Admin > Plugins > Add New
3. Click "Upload Plugin"
4. Choose the ZIP file and click "Install Now"
5. Activate the plugin

**Option B: Via FTP/File Manager**
1. Upload the `snow-alerts` folder to `/wp-content/plugins/`
2. Go to WordPress Admin > Plugins
3. Find "Snow Alerts" and click "Activate"

**Option C: Via WP-CLI**
```bash
cd /path/to/wordpress
wp plugin install /path/to/snow-alerts.zip --activate
```

### 2. Verify Installation

After activation, check:

1. **Settings Page:** Go to Settings > Snow Alerts
   - You should see the settings page with API Keys and SEO Settings sections

2. **IndexNow Key:** Check that a key file was created
   - Visit: `https://yourdomain.com/[key].txt`
   - The file should contain a 32-character hexadecimal string
   - The key is also displayed in Settings > Snow Alerts

3. **File Permissions:** Ensure WordPress can write files
   ```bash
   # Check uploads directory permissions
   ls -la wp-content/uploads/
   
   # Should be writable by web server
   # Typically: drwxr-xr-x or 755
   ```

### 3. Configure Settings

#### API Keys (Optional but Recommended)

**Unsplash API Key:**
1. Create account at [unsplash.com/developers](https://unsplash.com/developers)
2. Click "Your apps" > "New Application"
3. Accept terms and create application
4. Copy "Access Key" from application page
5. Paste in Settings > Snow Alerts > Unsplash API Key
6. Click "Save Settings"

Without Unsplash API key, the plugin will use placeholder images.

#### SEO Settings

**Enable IndexNow:**
1. Check the "Enable IndexNow API" checkbox (enabled by default)
2. Click "Save Settings"
3. Verify key file exists at `https://yourdomain.com/[key].txt`

**Author Name:**
1. Enter your organization name (default: "Weather Team")
2. This appears in article schema and meta tags
3. Click "Save Settings"

**Social Media URLs (E-E-A-T Signals):**
1. Enter your Facebook page URL
2. Enter your Twitter/X profile URL
3. Enter your Instagram profile URL
4. These improve E-E-A-T (Experience, Expertise, Authoritativeness, Trustworthiness)
5. Click "Save Settings"

### 4. Test the Installation

#### Test 1: Verify PHP Classes

Create a test file: `wp-content/plugins/snow-alerts/test-installation.php`

```php
<?php
require_once '../../../wp-load.php';

echo "Testing Snow Alerts Installation...\n\n";

// Test 1: Classes loaded
$classes = array(
    'Snow_Alerts_Advanced_SEO',
    'Snow_Alerts_Image_Optimizer',
    'Snow_Alerts_Headline_Optimizer',
    'Snow_Alerts_Scheduler',
    'Snow_Alerts_SEO_Optimizer',
);

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✓ {$class} loaded\n";
    } else {
        echo "✗ {$class} NOT loaded\n";
    }
}

// Test 2: IndexNow key
$key = get_option('snow_alerts_indexnow_key');
if ($key) {
    echo "\n✓ IndexNow key: {$key}\n";
} else {
    echo "\n✗ IndexNow key not found\n";
}

// Test 3: Settings
$settings = array(
    'snow_alerts_enable_indexnow' => get_option('snow_alerts_enable_indexnow', 'not set'),
    'snow_alerts_author_name' => get_option('snow_alerts_author_name', 'not set'),
);

echo "\nSettings:\n";
foreach ($settings as $key => $value) {
    echo "  {$key}: {$value}\n";
}
```

Run via browser: `https://yourdomain.com/wp-content/plugins/snow-alerts/test-installation.php`

Or via WP-CLI:
```bash
wp eval-file wp-content/plugins/snow-alerts/test-installation.php
```

#### Test 2: Publish Test Article

```php
<?php
require_once '../../../wp-load.php';

// Test article data
$article_data = array(
    'title' => 'Test Winter Storm Alert',
    'content' => '<p>This is a test article to verify the Snow Alerts plugin is working correctly.</p>',
    'format' => 'standard',
);

$location_data = array(
    'city' => 'Test City',
    'state' => 'Test State',
    'latitude' => 40.7128,
    'longitude' => -74.0060,
);

$weather_data = array(
    'event_type' => 'Winter Storm',
    'snow_amount' => 10,
    'details' => 'Test weather data',
);

$post_id = Snow_Alerts_Scheduler::publish_article(
    $article_data,
    $location_data,
    $weather_data
);

if ($post_id) {
    echo "✓ Test article published! Post ID: {$post_id}\n";
    echo "  URL: " . get_permalink($post_id) . "\n";
    echo "  Please delete this test article after verification.\n";
} else {
    echo "✗ Failed to publish test article\n";
}
```

### 5. Verify SEO Features

After publishing a test article, verify:

#### Schema Markup
1. Visit the article URL
2. Right-click > View Page Source
3. Search for `application/ld+json`
4. You should see 2-4 schema blocks:
   - NewsArticle
   - BreadcrumbList
   - FAQPage (if Q&A format)
   - WeatherForecast (if forecast data)

#### Google Rich Results Test
1. Visit [Google Rich Results Test](https://search.google.com/test/rich-results)
2. Enter your article URL
3. Click "Test URL"
4. Verify all schemas are valid (green checkmarks)

#### Meta Tags
View page source and verify:
```html
<meta name="description" content="...">
<meta name="robots" content="max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta property="article:published_time" content="...">
<meta property="article:modified_time" content="...">
<meta property="article:author" content="Weather Team">
```

#### Featured Image
1. Check article has featured image
2. Image should be 1200x630px or similar
3. View image properties for alt text

#### IndexNow Submission
Check server error logs for:
```
Snow Alerts: IndexNow submission successful to https://api.indexnow.org/indexnow
```

Or check WordPress debug log if enabled.

### 6. Performance Testing

#### Core Web Vitals
1. Visit [PageSpeed Insights](https://pagespeed.web.dev/)
2. Enter article URL
3. Run test
4. Verify metrics:
   - LCP (Largest Contentful Paint): < 2.5s ✓
   - FID (First Input Delay): < 100ms ✓
   - CLS (Cumulative Layout Shift): < 0.1 ✓

#### Mobile-Friendly Test
1. Visit [Google Mobile-Friendly Test](https://search.google.com/test/mobile-friendly)
2. Enter article URL
3. Verify "Page is mobile friendly" ✓

## Troubleshooting

### Issue: IndexNow key file not created

**Solution:**
```php
// Run in WP-CLI or admin page
Snow_Alerts_Advanced_SEO::create_indexnow_key_file();
```

Or manually create:
1. Get key: `echo get_option('snow_alerts_indexnow_key');`
2. Create file: `[key].txt` in site root
3. Add key as content

### Issue: Featured images not generating

**Possible causes:**
1. Uploads directory not writable
   ```bash
   chmod 755 wp-content/uploads/
   ```

2. Unsplash API key invalid
   - Verify key at unsplash.com/account
   - Or remove key to use placeholders

3. GD library not installed
   ```bash
   php -m | grep -i gd
   ```

### Issue: Schema validation errors

**Common fixes:**
1. Ensure dates in ISO 8601 format
2. Verify latitude/longitude are numeric
3. Check image is at least 1200px wide
4. Test with [Google Rich Results Test](https://search.google.com/test/rich-results)

### Issue: Settings not saving

**Possible causes:**
1. User lacks permissions
   - Must have 'manage_options' capability
   
2. Nonce verification failing
   - Clear browser cache and cookies
   
3. Database errors
   - Check WordPress debug log

## Uninstallation

### Clean Uninstall

1. **Deactivate Plugin:**
   - Go to Plugins > Installed Plugins
   - Click "Deactivate" under Snow Alerts

2. **Remove IndexNow Key File:**
   ```bash
   # Get key
   KEY=$(wp option get snow_alerts_indexnow_key)
   # Remove file
   rm "$KEY.txt"
   ```

3. **Clean Database (Optional):**
   ```sql
   -- Remove plugin options
   DELETE FROM wp_options WHERE option_name LIKE 'snow_alerts_%';
   
   -- Remove post meta
   DELETE FROM wp_postmeta WHERE meta_key LIKE '_snow_alerts_%';
   ```

4. **Delete Plugin:**
   - Go to Plugins > Installed Plugins
   - Click "Delete" under Snow Alerts
   - Confirm deletion

## Next Steps

1. **Read Documentation:** See [README.md](README.md) for usage guide
2. **Review Examples:** Check [examples.php](examples.php) for code samples
3. **Run Tests:** Execute [tests.php](tests.php) to verify functionality
4. **Configure Automation:** Set up scheduled posts or API integrations
5. **Monitor Performance:** Track indexing speed and search rankings

## Support

For issues and questions:
- GitHub Issues: [github.com/snowday25/snow](https://github.com/snowday25/snow)
- Documentation: [README.md](README.md)

## Security Notes

- **API Keys:** Never commit API keys to version control
- **File Permissions:** Keep uploads directory secure (755 or 750)
- **Updates:** Keep WordPress and PHP up to date
- **Backups:** Regular backups recommended before updates
