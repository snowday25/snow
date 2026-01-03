# Snow Alerts Plugin

A comprehensive WordPress plugin for automated snow alert article generation with AI-powered content and template-based fallback system.

## Features

- **AI-Powered Content Generation** - Uses OpenAI GPT to create unique, SEO-optimized snow alert articles
- **Template-Based Fallback** - Robust template system ensures articles are always generated, even without AI
- **50+ Unique Variations** - Generate diverse titles and descriptions to maintain content uniqueness
- **5 Storm Types Supported** - Winter Storm Warning, Blizzard Warning, Heavy Snow Warning, Winter Weather Advisory, Ice Storm Warning
- **100K+ US Cities Database** - Pre-filtered cities with population over 100,000
- **SEO Optimized** - All content is optimized for search engines with proper meta descriptions and keywords
- **Uniqueness Checking** - Prevents duplicate titles and overly similar content
- **PHP 7.4+ Compatible** - Uses proper isset() checks and avoids modern PHP operators for compatibility

## Installation

1. Download or clone this repository to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/snowday25/snow.git snow-alerts-plugin
   ```

2. Activate the plugin in WordPress admin panel:
   - Go to **Plugins** → **Installed Plugins**
   - Find "Snow Alerts" and click **Activate**

3. (Optional) Configure OpenAI API key for AI-powered generation:
   - Add to `wp-config.php`:
     ```php
     define('SNOW_ALERTS_OPENAI_API_KEY', 'your-api-key-here');
     ```
   - Or use WordPress options:
     ```php
     update_option('snow_alerts_openai_api_key', 'your-api-key-here');
     ```

## Directory Structure

```
snow-alerts-plugin/
├── snow-alerts-plugin.php          # Main plugin file
├── includes/                        # PHP class files
│   ├── class-api-manager.php       # API key management
│   ├── class-uniqueness-checker.php # Content uniqueness validation
│   ├── class-template-manager.php  # Template-based article generation
│   └── class-content-generator.php # AI + template fallback generation
├── data/                           # Data files
│   ├── article-templates.json      # Article templates for 5 storm types
│   ├── title-variations.json       # Title variation components
│   ├── description-variations.json # Meta description templates
│   └── us-cities-100k.json        # US cities database (generated)
├── scripts/                        # Utility scripts
│   ├── process-cities.py           # City data processor
│   ├── requirements.txt            # Python dependencies
│   └── README.md                   # Script documentation
└── README.md                       # This file
```

## Template System

The plugin includes a comprehensive template-based fallback system that activates when AI generation is unavailable or fails.

### Article Templates

Located in `data/article-templates.json`, includes templates for:

1. **Winter Storm Warning** - General winter storms (6+ inches)
2. **Blizzard Warning** - Severe conditions with high winds (12+ inches)
3. **Heavy Snow Warning** - Significant snowfall (6-12 inches)
4. **Winter Weather Advisory** - Light snow (< 6 inches)
5. **Ice Storm Warning** - Freezing rain and ice accumulation

Each storm type includes:
- 10 unique title templates
- 5 meta description templates
- 1+ detailed content templates (800-1200 words)

### Title Variations

Located in `data/title-variations.json`, provides components for generating unique titles:

- **Prefixes** (10): "Breaking:", "Alert:", "Update:", etc.
- **Action Words** (10): "Braces for", "Prepares for", "Faces", etc.
- **Intensity Modifiers** (10): "Major", "Significant", "Severe", etc.
- **Weather Events** (10): "Winter Storm", "Snow Event", "Snowstorm", etc.
- **Time References** (10): "This Week", "Tonight", "Tomorrow", etc.
- **Suffixes** (10): "- Latest Updates", "- What to Expect", etc.

The system can generate 50+ unique variations by combining these components.

### Meta Description Variations

Located in `data/description-variations.json`, contains 50 different meta description templates optimized for SEO (150-160 characters).

### Placeholder System

Templates use placeholders that are automatically replaced with real data:

- `{city}` - City name (e.g., "Boston")
- `{state}` - State abbreviation (e.g., "MA")
- `{state_name}` - Full state name (e.g., "Massachusetts")
- `{county}` - County name
- `{population}` - Formatted population
- `{amount}` - Snow amount with units (e.g., "12 inches")
- `{date}` - Formatted date
- `{start_date}` - Storm start date
- `{end_date}` - Storm end date
- `{weather_event}` - Event type (e.g., "Winter Storm")
- `{event}` - Same as weather_event
- `{day_of_week}` - Current day
- `{current_time}` - Current time
- `{current_date}` - Current date

## Fallback Mechanism

The plugin uses a smart fallback system:

```
1. Try OpenAI API (if API key available)
   ↓ (on failure)
2. Use Template System
   ↓
3. Generate unique title (up to 50 attempts)
   ↓
4. Generate meta description
   ↓
5. Generate content from template
   ↓
6. Replace all placeholders
   ↓
7. Return complete article
```

All fallback usage is logged to WordPress error log for monitoring.

## City Data Processing

### Requirements

- Python 3.6+
- pandas
- requests

### Installation

```bash
cd scripts
pip install -r requirements.txt
```

### Usage

```bash
cd scripts
python process-cities.py
```

### What It Does

1. Downloads SimpleMaps US Cities database
2. Filters cities with population > 100,000
3. Converts to JSON with required fields
4. Sorts by population (descending)
5. Saves to `data/us-cities-100k.json`
6. Prints comprehensive statistics

### Output Format

```json
[
  {
    "city": "New York",
    "state": "NY",
    "state_name": "New York",
    "county": "New York",
    "population": 8398748,
    "latitude": 40.7128,
    "longitude": -74.0060,
    "timezone": "America/New_York",
    "ranking": 1
  }
]
```

### Updating Cities Data

Simply re-run the script to download and process the latest data:

```bash
cd scripts
python process-cities.py
```

## Usage Examples

### Generate Article for a City

```php
// Example city data
$city_data = array(
    'city' => 'Boston',
    'state' => 'MA',
    'state_name' => 'Massachusetts',
    'county' => 'Suffolk',
    'population' => 694583
);

// Example weather data
$weather_data = array(
    'event' => 'Winter Storm Warning',
    'snow_amount' => 12,
    'start_date' => '2024-01-15',
    'end_date' => '2024-01-16'
);

// Generate article
$article = Snow_Alerts_Content_Generator::generate_article($city_data, $weather_data);

if ($article) {
    echo "Title: " . $article['title'] . "\n";
    echo "Meta Description: " . $article['meta_description'] . "\n";
    echo "Keywords: " . $article['keywords'] . "\n";
    echo "Content: " . $article['content'] . "\n";
}
```

### Check Title Uniqueness

```php
$title = "Boston, MA: Major Winter Storm Warning Issued";
$is_unique = Snow_Alerts_Uniqueness_Checker::is_title_unique($title);

if ($is_unique) {
    echo "Title is unique!";
} else {
    echo "Title already exists";
}
```

### Determine Storm Type

```php
$weather_data = array(
    'event' => 'Blizzard Warning',
    'snow_amount' => 18
);

$storm_type = Snow_Alerts_Template_Manager::determine_storm_type($weather_data);
echo "Storm Type: " . $storm_type; // Output: blizzard_warning
```

## PHP Compatibility

The plugin is fully compatible with PHP 7.4+ by avoiding modern operators:

- ✅ Uses `isset()` checks for all array access
- ✅ No `??` null coalescing operator
- ✅ No `?:` Elvis operator
- ✅ Proper error handling throughout
- ✅ Compatible with older WordPress installations

## Error Handling

The plugin includes comprehensive error handling:

- Template files missing → Uses hardcoded defaults
- JSON parse errors → Falls back to default templates
- API failures → Automatically uses template system
- All errors logged to WordPress error log

## SEO Optimization

All generated content is SEO-optimized:

- **Titles**: Under 60 characters, keyword-rich
- **Meta Descriptions**: 150-160 characters, compelling
- **Keywords**: Relevant, city-specific keywords
- **Content**: 800-1200 words, proper HTML structure
- **Headers**: Proper H2/H3 hierarchy
- **Lists**: Scannable bullet points

## Testing

### Test Template Loading

```php
$templates = Snow_Alerts_Template_Manager::load_templates();
var_dump($templates);
```

### Test Article Generation

```php
$city_data = array('city' => 'Test City', 'state' => 'TX');
$weather_data = array('event' => 'Winter Storm');
$article = Snow_Alerts_Template_Manager::generate_from_template($city_data, $weather_data);
var_dump($article);
```

### Test Python Script

```bash
cd scripts
python process-cities.py
```

## Troubleshooting

### Templates Not Loading

- Check that `data/` directory exists
- Verify JSON files are valid (use jsonlint.com)
- Check file permissions
- Look for errors in WordPress error log

### API Key Issues

- Verify API key is set correctly
- Check API key has proper permissions
- Monitor WordPress error log for API errors

### Python Script Fails

- Ensure Python 3.6+ is installed
- Install requirements: `pip install -r requirements.txt`
- Check internet connection for download
- See `scripts/README.md` for detailed troubleshooting

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This plugin is licensed under GPL v2 or later.

## Support

For issues, questions, or feature requests, please open an issue on GitHub.

## Credits

- SimpleMaps for US Cities database
- OpenAI for GPT API
- WordPress community

## Version History

### 1.0.0 (2024)
- Initial release
- AI-powered article generation
- Template-based fallback system
- 5 storm types supported
- City data processor
- Uniqueness checking
- SEO optimization
