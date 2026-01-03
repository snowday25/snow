# Changelog

All notable changes to the Snow Alerts WordPress Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-01-03

### Added

#### Core Plugin
- Initial release of Snow Alerts WordPress plugin
- Main plugin file with WordPress hooks and initialization
- Admin settings page with comprehensive options
- Plugin activation/deactivation hooks

#### Advanced SEO System
- **IndexNow API Integration**
  - Automatic URL submission to Bing, Yandex, and partners
  - API key generation and management
  - Key file creation in site root
  - Support for multiple IndexNow endpoints
  - Error logging for API failures
  - Google sitemap ping integration

- **Enhanced Schema Markup**
  - NewsArticle schema with E-E-A-T signals
  - BreadcrumbList schema (4 levels)
  - FAQPage schema with automatic Q&A detection
  - WeatherForecast schema with geo coordinates
  - Organization author with social media links
  - Temporal coverage (alert date ranges)
  - Spatial coverage (GeoCoordinates)
  - Speakable specification for voice search
  - Article section and word count

- **Google Discover Optimization**
  - Robots meta tags (max-image-preview, max-snippet, max-video-preview)
  - Article meta tags (published_time, modified_time, author, section, tags)
  - Viewport optimization for mobile
  - AMP HTML link support (if available)
  - Mobile-first meta tags

- **Sitemap Optimization**
  - Priority 0.9 for snow alert articles
  - Change frequency: hourly
  - Modified date for lastmod
  - Automatic filtering for snow alerts

- **RSS Feed Enhancements**
  - Geo tags (latitude/longitude)
  - Category tags for news aggregators
  - Weather and location categories
  - Enhanced metadata

- **Core Web Vitals Optimization**
  - Preconnect to embed.windy.com
  - DNS prefetch for external resources
  - Critical CSS hints
  - Lazy loading for iframes
  - JavaScript optimization hints

#### Image Optimization
- **Automatic Featured Image Generation**
  - Unsplash API integration (optional)
  - Placeholder image fallback (1200x630px)
  - Winter-themed color scheme (#1e3a8a blue)
  - Automatic download and attachment
  - Descriptive alt text generation
  - Image compression support

- **Image Optimization Features**
  - GD library support for WebP conversion
  - Quality optimization (85% JPEG, level 8 PNG)
  - Proper title and alt attributes
  - Google Discover optimal dimensions (1200x630px)

#### Headline & Meta Optimization
- **Headline Optimization**
  - Power words system (severe/moderate/light)
  - Emotion triggers for engagement
  - Multiple headline structures
  - Mobile-friendly length (50-60 chars)
  - Automatic truncation with ellipsis

- **Power Words by Severity:**
  - Severe: Extreme, Crippling, Historic, Devastating, Critical
  - Moderate: Major, Significant, Powerful, Massive, Dangerous
  - Light: Notable, Developing, Approaching, Breaking, Alert

- **Emotion Triggers:**
  - Brace Yourself, Get Ready, Prepare Now
  - Stay Safe, Be Aware, Take Action
  - Don't Miss, What You Need to Know

- **Meta Description Templates**
  - Urgency format for breaking news
  - Specific format with details
  - Question format for engagement
  - Local format for geographic targeting
  - Optimal length (150-160 chars)

#### Article Scheduler
- **Publication System**
  - Publish with full SEO optimization
  - Update existing articles
  - Schedule future publication
  - Automatic category and tag assignment
  - Geo coordinate storage
  - Meta data management

- **SEO Integration**
  - Headline optimization on publish
  - Meta description generation
  - Featured image creation
  - Schema markup generation
  - IndexNow submission trigger
  - WordPress category/tag assignment

#### SEO Optimizer
- **Schema Output**
  - Multiple schema types in one page
  - JSON-LD format
  - Proper escaping and validation
  - Support for all schema types

- **Meta Tag Management**
  - Custom meta descriptions
  - Open Graph tags
  - Twitter Card tags
  - Article meta properties

- **Title Optimization**
  - Legacy wp_title filter support
  - Modern pre_get_document_title filter
  - Optimized title structure

#### Settings & Configuration
- **API Keys Section**
  - Unsplash API key (optional)
  - Key validation and storage
  - Secure handling

- **SEO Settings Section**
  - Enable/disable IndexNow
  - Author name customization
  - Social media URLs (Facebook, Twitter, Instagram)
  - E-E-A-T signal configuration

- **Settings Page Features**
  - Active SEO optimizations list
  - Quick start guide
  - Testing & validation links
  - Documentation references

#### Documentation
- **README.md**
  - Comprehensive feature overview
  - IndexNow integration guide
  - Schema markup documentation
  - Google Discover requirements
  - Usage examples
  - SEO checklist
  - Testing & validation guide
  - Technical details
  - Troubleshooting section

- **INSTALL.md**
  - Step-by-step installation guide
  - Requirements documentation
  - Configuration instructions
  - Testing procedures
  - Troubleshooting tips
  - Uninstallation guide

- **examples.php**
  - 10 usage examples
  - Code samples for common tasks
  - Best practices demonstration
  - Integration examples

- **tests.php**
  - Unit tests for core functionality
  - Headline optimization tests
  - Meta description tests
  - Severity determination tests
  - Schema structure validation

### Technical Details

#### PHP Compatibility
- PHP 7.4+ support
- No null coalescing (??) operator
- No ternary shorthand (?:) operator
- All array access with isset() checks
- WordPress coding standards compliance

#### WordPress Integration
- Proper action and filter hooks
- Settings API usage
- Post meta storage
- Nonce verification
- Capability checks
- Internationalization ready

#### Performance
- Minimal database queries
- Efficient caching strategy
- Lazy loading for resources
- Optimized API calls
- No blocking operations

#### Security
- Nonce verification on forms
- Capability checks for settings
- Input sanitization
- Output escaping
- Secure API key storage
- No SQL injection vulnerabilities
- No XSS vulnerabilities

### Files Added
- `snow-alerts-plugin.php` - Main plugin file
- `includes/class-advanced-seo.php` - Advanced SEO features
- `includes/class-image-optimizer.php` - Image generation and optimization
- `includes/class-headline-optimizer.php` - Headline and meta optimization
- `includes/class-scheduler.php` - Article publishing system
- `includes/class-seo-optimizer.php` - Schema markup output
- `admin/views/settings.php` - Admin settings page
- `README.md` - Main documentation
- `INSTALL.md` - Installation guide
- `CHANGELOG.md` - Version history
- `examples.php` - Usage examples
- `tests.php` - Unit tests
- `.gitignore` - Git ignore rules

### Known Limitations
- Requires WordPress environment for full functionality
- Unsplash API has rate limits (50 requests/hour on free tier)
- IndexNow submissions are best-effort (no guaranteed delivery)
- Schema validation requires Google Rich Results Test
- Image optimization requires GD library (optional)

## [Unreleased]

### Planned Features
- WP-CLI commands for bulk operations
- REST API endpoints for external integrations
- Dashboard widget with SEO metrics
- Automatic content updates based on weather API
- Multi-language support
- Additional schema types (HowTo, Event)
- Integration with popular SEO plugins (Yoast, RankMath)
- Advanced analytics and reporting
- A/B testing for headlines
- Custom template support

### Future Enhancements
- GraphQL API support
- Gutenberg blocks for weather alerts
- Email notifications for published articles
- Social media auto-posting
- Image CDN integration
- Advanced caching strategies
- Performance monitoring
- SEO score calculation
- Competitive analysis tools
- Backlink tracking

---

## Version History

- **1.0.0** - Initial release with comprehensive SEO features

For detailed information about each version, see the sections above.
