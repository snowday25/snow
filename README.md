# Snow Alerts - Advanced SEO WordPress Plugin

A comprehensive WordPress plugin for snow alert articles with advanced SEO features for fast indexing, high rankings, and Google Discover visibility.

## Features

### 🚀 Advanced SEO System

- **IndexNow API Integration** - Instant indexing to Bing, Yandex, and partner search engines
- **Enhanced Schema Markup** - 4 types: NewsArticle, Breadcrumb, FAQ, WeatherForecast
- **Google Discover Optimization** - Meta tags and image requirements for Google Discover
- **Optimized Headlines** - 50-60 character headlines with power words for maximum CTR
- **Meta Descriptions** - 150-160 character descriptions optimized for search snippets
- **Featured Images** - Automatic generation of 1200x630px images
- **E-E-A-T Signals** - Experience, Expertise, Authoritativeness, Trustworthiness
- **Core Web Vitals** - Preconnect, DNS prefetch, lazy loading optimization
- **Mobile-First Design** - Responsive and fast-loading pages
- **Structured Data** - JSON-LD schema for rich search results

### 📊 SEO Components

#### 1. IndexNow API (Instant Indexing)

Automatically submits URLs to multiple search engines for near-instant indexing:

- **api.indexnow.org** - IndexNow protocol endpoint
- **bing.com/indexnow** - Microsoft Bing
- **yandex.com/indexnow** - Yandex

**How It Works:**
1. Plugin generates a unique API key on activation
2. Creates key file in site root (domain.com/[key].txt)
3. On article publish/update, submits URL with JSON payload
4. Search engines index within minutes instead of days

#### 2. Enhanced Schema Markup

Four types of structured data for rich search results:

**NewsArticle Schema:**
```json
{
  "@type": "NewsArticle",
  "headline": "Article Title",
  "author": {
    "@type": "Organization",
    "name": "Weather Team",
    "sameAs": ["https://facebook.com/...", "https://twitter.com/..."]
  },
  "temporalCoverage": "2024-01-01/2024-01-03",
  "spatialCoverage": {
    "@type": "Place",
    "geo": {
      "@type": "GeoCoordinates",
      "latitude": 40.7128,
      "longitude": -74.0060
    }
  },
  "speakable": {
    "@type": "SpeakableSpecification",
    "cssSelector": [".entry-title", ".entry-content p"]
  }
}
```

**Breadcrumb Schema:**
```json
{
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"position": 1, "name": "Home"},
    {"position": 2, "name": "Weather"},
    {"position": 3, "name": "State Name"},
    {"position": 4, "name": "Article Title"}
  ]
}
```

**FAQ Schema** (for Q&A articles):
```json
{
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Question text?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Answer text"
      }
    }
  ]
}
```

**WeatherForecast Schema:**
```json
{
  "@type": "WeatherForecast",
  "spatialCoverage": {
    "@type": "Place",
    "geo": {
      "@type": "GeoCoordinates",
      "latitude": 40.7128,
      "longitude": -74.0060
    }
  },
  "validFrom": "2024-01-01T00:00:00Z",
  "validThrough": "2024-01-03T23:59:59Z"
}
```

#### 3. Google Discover Optimization

Meta tags and requirements for Google Discover visibility:

**Required Meta Tags:**
- `max-image-preview:large` - Allow large image previews
- `max-snippet:-1` - No limit on text snippet length
- `max-video-preview:-1` - No limit on video preview duration
- `article:published_time` - Publication timestamp
- `article:modified_time` - Last modified timestamp
- `article:author` - Author name (configurable)
- `article:section` - Content section (Weather)
- `article:tag` - Article tags

**Image Requirements:**
- Minimum width: 1200px (optimal: 1200x630px)
- High quality, relevant images
- Descriptive alt text
- Fast loading with lazy loading

**Content Requirements:**
- Compelling headlines with power words
- Fresh, timely content
- Mobile-optimized display
- Fast Core Web Vitals (LCP < 2.5s)

#### 4. Headline Optimization

Automatic headline optimization for maximum click-through rate:

**Power Words by Severity:**
- **Severe:** Extreme, Crippling, Historic, Devastating, Critical
- **Moderate:** Major, Significant, Powerful, Massive, Dangerous
- **Light:** Notable, Developing, Approaching, Breaking, Alert

**Emotion Triggers:**
- Brace Yourself
- Get Ready
- Prepare Now
- Stay Safe
- Be Aware
- Take Action

**Headline Structures:**
- `{power} {location}: {event} - {trigger}`
- `{trigger}: {power} {event} Hits {location}`
- `{location} Faces {power} {event} - {trigger}`

**Example:**
- Input: "Winter Storm in New York"
- Output: "Major New York: Winter Storm - Get Ready" (58 chars)

#### 5. Featured Image Generation

Automatic generation and optimization of featured images:

**Sources:**
1. **Unsplash API** (optional, requires API key)
   - Query: "winter storm snow {city_name}"
   - Size: 1200px wide (regular)
   - Landscape orientation

2. **Placeholder Service** (fallback)
   - Size: 1200x630px
   - Winter theme colors (#1e3a8a blue)
   - Text overlay with location

**Optimization:**
- Descriptive alt text: "Winter storm in {City}, {State}"
- Title attribute for accessibility
- Compressed for fast loading
- WebP conversion support (if available)

#### 6. Core Web Vitals Optimization

Performance optimizations for Google's Core Web Vitals:

- **Preconnect:** `<link rel="preconnect" href="https://embed.windy.com">`
- **DNS Prefetch:** External resources pre-resolved
- **Lazy Loading:** Iframes and images load on demand
- **Critical CSS:** Inline critical styles for LCP
- **Defer Non-Critical:** JavaScript deferred for faster FCP

**Target Metrics:**
- LCP (Largest Contentful Paint): < 2.5s
- FID (First Input Delay): < 100ms
- CLS (Cumulative Layout Shift): < 0.1

### 🎯 E-E-A-T Signals

Experience, Expertise, Authoritativeness, Trustworthiness signals:

- **Organization Author:** Credible "Weather Team" or custom name
- **Social Proof:** Facebook, Twitter, Instagram links in schema
- **Logo:** Site logo in organization schema
- **Backstory:** Demonstrated expertise through content
- **Temporal Coverage:** Accurate date ranges for credibility
- **Spatial Coverage:** Precise geographic information

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Snow Alerts to configure

## Configuration

### API Keys

**Unsplash API Key (Optional):**
1. Create free account at [unsplash.com/developers](https://unsplash.com/developers)
2. Create new application
3. Copy Access Key
4. Enter in Settings > Snow Alerts > API Keys

### SEO Settings

**Enable IndexNow:**
- Check to enable instant indexing
- Key file automatically created on activation
- Submits to Bing, Yandex, and partners

**Author Name:**
- Default: "Weather Team"
- Customize to your organization name
- Used in article schema and meta tags

**Social Media URLs:**
- Facebook, Twitter, Instagram URLs
- Improves E-E-A-T signals
- Added to organization schema

## Usage

### Publishing Articles

```php
// Example article data
$article_data = array(
    'title' => 'Winter Storm in New York',
    'content' => 'Full article content here...',
    'format' => 'standard', // or 'qa' for Q&A format
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
    'snow_amount' => 12, // inches
    'alert_start' => '2024-01-01T00:00:00Z',
    'alert_end' => '2024-01-03T23:59:59Z',
    'details' => 'Heavy snowfall expected',
);

// Publish with full SEO
$post_id = Snow_Alerts_Scheduler::publish_article(
    $article_data,
    $location_data,
    $weather_data
);
```

**What Happens Automatically:**

1. ✅ Headline optimized with power words (50-60 chars)
2. ✅ Meta description generated (150-160 chars)
3. ✅ Featured image created (1200x630px)
4. ✅ NewsArticle schema added with E-E-A-T
5. ✅ Breadcrumb schema generated
6. ✅ FAQ schema added (if Q&A format)
7. ✅ Geo coordinates stored
8. ✅ Categories and tags assigned
9. ✅ IndexNow submission triggered
10. ✅ Google sitemap pinged

### Updating Articles

```php
// Update existing article
$updated = Snow_Alerts_Scheduler::update_article(
    $post_id,
    $article_data,
    $location_data,
    $weather_data
);
```

## SEO Checklist

- ✅ **IndexNow API** - Instant indexing to search engines
- ✅ **Enhanced Schema** - 4 types of structured data
- ✅ **Google Discover Tags** - Optimized meta tags
- ✅ **Optimized Headlines** - 50-60 characters with power words
- ✅ **Meta Descriptions** - 150-160 characters for snippets
- ✅ **Featured Images** - 1200x630px high-quality images
- ✅ **E-E-A-T Signals** - Organization author with social proof
- ✅ **Core Web Vitals** - Preconnect, lazy loading, optimization
- ✅ **Mobile-First** - Responsive design and viewport tags
- ✅ **Structured Data** - Valid JSON-LD for rich results

## Testing & Validation

### Schema Validation
- [Google Rich Results Test](https://search.google.com/test/rich-results)
- Paste article URL to validate all schemas
- Check for errors and warnings

### Performance Testing
- [PageSpeed Insights](https://pagespeed.web.dev/)
- Check Core Web Vitals scores
- Ensure LCP < 2.5s, FID < 100ms, CLS < 0.1

### Mobile-Friendly Test
- [Google Mobile-Friendly Test](https://search.google.com/test/mobile-friendly)
- Verify responsive design
- Check viewport configuration

### IndexNow Verification
Check server logs for successful submissions:
```
Snow Alerts: IndexNow submission successful to https://api.indexnow.org/indexnow
```

## Technical Details

### PHP Compatibility
- **PHP Version:** 7.4+
- **No Short Operators:** All code avoids `??` and `?:` operators
- **Array Access:** Uses `isset()` checks for all array access
- **WordPress Standards:** Follows WordPress coding standards

### WordPress Hooks

**Actions:**
- `snow_alerts_article_published` - Triggered on publish/update
- `wp_head` - Outputs schema markup and meta tags
- `rss2_item` - Enhances RSS feed with geo tags

**Filters:**
- `wp_sitemaps_posts_entry` - Modifies sitemap entries
- `wp_title` - Customizes title tags
- `pre_get_document_title` - Modern title tag filter

### Performance Considerations

- **Lazy Loading:** Iframes and images load on scroll
- **Preconnect:** External resources preconnected
- **Caching:** No additional caching needed (uses WordPress cache)
- **API Limits:** Unsplash rate limited (50 requests/hour free tier)
- **IndexNow:** No rate limits, best effort delivery

## Troubleshooting

### IndexNow Not Working
1. Check Settings > Snow Alerts > Enable IndexNow is checked
2. Verify key file exists: `yourdomain.com/[key].txt`
3. Check server error logs for API responses
4. Ensure server can make outbound HTTPS requests

### Featured Images Not Generating
1. Check Unsplash API key is valid (or leave blank for placeholder)
2. Verify `wp-content/uploads/` is writable
3. Check PHP GD library is installed
4. Review error logs for download failures

### Schema Validation Errors
1. Test with [Google Rich Results Test](https://search.google.com/test/rich-results)
2. Check all required schema properties are present
3. Verify dates are in ISO 8601 format
4. Ensure images are at least 1200px wide

## Changelog

### Version 1.0.0
- Initial release
- IndexNow API integration
- Enhanced schema markup (4 types)
- Google Discover optimization
- Headline and meta description optimization
- Automatic featured image generation
- E-E-A-T signals implementation
- Core Web Vitals optimization

## License

GPL v2 or later

## Support

For issues and questions, please visit the [GitHub repository](https://github.com/snowday25/snow).

## Credits

Developed by the Snow Alerts Team
