# Changelog

All notable changes to the Snow Alerts WordPress Plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2024-01-03

### Added - Complete Feature Set

#### Core Plugin (PR #1 Features)
- Main plugin file with activation/deactivation hooks
- Database manager with 3 tables (articles, cities, logs)
- API Manager with AES-256-CBC encryption
- Location Manager supporting 7,000-15,000 US locations
- Weather Fetcher (WeatherAPI.com + NWS + Windy integration)
- Content Generator with OpenAI GPT-4
- Basic Uniqueness Checker
- Scheduler with WordPress cron
- Admin settings page with API key management
- Admin dashboard with statistics
- CSS and JavaScript assets
- PHP 7.4 full compatibility

#### Template System (PR #2 Features)
- Template Manager class for article generation
- Article templates JSON (5 storm types, 50+ title variations)
- Title variations JSON (60+ components)
- Description variations JSON (50+ templates)
- AI + Template dual system with automatic fallback
- Python script for city data processing
- scripts/requirements.txt
- scripts/README.md documentation

#### Anti-Duplication System (PR #3 Features)
- Advanced Uniqueness Checker V2
  - Fuzzy title matching (85% similarity threshold)
  - N-gram content similarity (3-grams, 70% Jaccard index)
  - Weather change detection (24-48hr window)
  - 20+ title variation patterns
- Content Variation Generator (8 formats):
  - Standard article format
  - Update/breaking news format
  - Timeline format
  - Q&A format
  - Listicle format
  - Safety-focused format
  - Impact analysis format
  - Comparison format
- Location Manager enhancements:
  - Support for all snow-prone US locations
  - 4 location types (city/town/village/locality)
  - Weighted random selection algorithm
  - 39 snow-prone states coverage
- Enhanced Scheduler:
  - Update detection (24-48hr monitoring)
  - Automatic format selection
  - Title retry logic (up to 5 attempts)
  - Bidirectional article linking
- Python script for comprehensive snow location data
- 500,000+ unique article combinations possible

#### Advanced SEO (PR #4 Features)
- Advanced SEO class:
  - IndexNow API integration (Bing, Yandex instant indexing)
  - Google sitemap ping
  - Sitemap optimization (priority 0.9, hourly changefreq)
  - Google Discover meta tags
  - Enhanced Schema.org markup (4 types):
    - NewsArticle schema
    - Breadcrumb schema
    - FAQ schema
    - WeatherForecast schema
  - E-E-A-T signals implementation
  - Core Web Vitals optimization
- Image Optimizer:
  - Unsplash API integration
  - 1200x630px optimized images
  - Automatic alt text generation
  - Placeholder fallback system
- Headline Optimizer:
  - 15+ power word variations
  - 8 emotion trigger types
  - CTR optimization algorithms
  - 4 meta description templates
- Enhanced SEO Optimizer:
  - Output all 4 schema types
  - Social media meta tags (Open Graph + Twitter Cards)
- Updated settings page:
  - Unsplash API key field
  - Social media URL fields (Facebook, Twitter, Instagram)

### Technical Improvements

#### Security
- AES-256-CBC encryption for all API keys
- WordPress nonces for CSRF protection
- Prepared SQL statements throughout
- Input sanitization on all user inputs
- Output escaping for XSS prevention

#### Performance
- Lazy loading for images
- Preconnect hints for external APIs
- Cached location data
- Hash-first duplicate checking
- Optimized database queries with proper indexes

#### Compatibility
- Full PHP 7.4 compatibility
- No null coalescing (??) operators
- No Elvis (?:) operators
- Proper isset() checks throughout
- WordPress 6.0+ compatibility
- MySQL 5.7+ support

#### Code Quality
- WordPress coding standards
- Comprehensive inline documentation
- Modular class structure (13 core classes)
- Clean separation of concerns
- DRY principles applied

### Database Schema

#### wp_snow_articles
- Stores article metadata and tracking
- Indexes on post_id, location, hashes, dates
- Supports update tracking and parent linking

#### wp_snow_cities
- Location database with coordinates
- Population and elevation data
- Activity tracking and weighting
- Last checked timestamp

#### wp_snow_api_logs
- API call logging and monitoring
- Success rate tracking
- Error message storage
- Execution time metrics

### Files Added
- Main plugin: `snow-alerts-plugin.php`
- Core classes: 13 files in `/includes/`
- Admin classes: 2 files in `/admin/`
- Admin views: 2 files in `/admin/views/`
- Assets: 4 files in `/assets/` (2 CSS, 2 JS)
- Data files: 4 JSON files in `/data/`
- Scripts: 3 files in `/scripts/`
- Documentation: README.md, CHANGELOG.md, LICENSE

### Configuration Options
- Auto-generation toggle
- Check interval (30min to daily)
- Minimum snow amount threshold
- Author name customization
- Social media URLs
- Location import functionality

### Statistics & Monitoring
- Total articles count
- Daily generation metrics
- Location coverage statistics
- API call tracking
- Success rate monitoring
- Recent articles display

### Known Limitations
- Sample location dataset included (30 cities)
- Production requires 7,000-15,000 location integration
- Weather API rate limits apply
- OpenAI API costs per generation

### Future Enhancements
- Integration with comprehensive location databases
- Multi-language support
- Advanced analytics dashboard
- Custom post type support
- Webhook notifications
- Email digest system

## [1.0.0] - Initial Concept
- Project conception and planning

---

For more information, see [README.md](README.md)
