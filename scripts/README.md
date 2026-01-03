# City Data Processing Script

This directory contains the Python script for downloading and processing US cities data.

## Overview

The `process-cities.py` script downloads the SimpleMaps US Cities database, filters cities with population over 100,000, and generates a JSON file for use by the Snow Alerts plugin.

## Requirements

- Python 3.6 or higher
- pip (Python package manager)

## Installation

1. Navigate to the scripts directory:
   ```bash
   cd scripts
   ```

2. Install required Python packages:
   ```bash
   pip install -r requirements.txt
   ```

   This will install:
   - `pandas` - For CSV data processing
   - `requests` - For downloading data

## Usage

Run the script from the scripts directory:

```bash
python process-cities.py
```

Or make it executable and run directly:

```bash
chmod +x process-cities.py
./process-cities.py
```

## What the Script Does

1. **Downloads** the SimpleMaps US Cities database (ZIP file) from simplemaps.com
2. **Extracts** the ZIP file to a temporary directory
3. **Reads** the CSV data using pandas
4. **Filters** cities with population > 100,000
5. **Converts** the data to JSON format with these fields:
   - `city` - City name
   - `state` - State abbreviation (e.g., "NY")
   - `state_name` - Full state name (e.g., "New York")
   - `county` - County name
   - `population` - Population count
   - `latitude` - Latitude coordinate
   - `longitude` - Longitude coordinate
   - `timezone` - Timezone identifier
   - `ranking` - Population ranking (1 = largest)
6. **Sorts** cities by population (descending)
7. **Saves** the result to `../data/us-cities-100k.json`
8. **Prints** comprehensive statistics including:
   - Total cities processed
   - Population ranges
   - Cities per state
   - Snow-prone state analysis
   - Top 10 largest cities
9. **Cleans up** temporary files

## Expected Output

The script will generate a JSON file at `data/us-cities-100k.json` containing approximately 300+ cities with population over 100,000.

Example output format:
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
  },
  ...
]
```

## Statistics

The script provides detailed statistics including:
- Total number of cities processed
- Population range (min, max, average)
- Top 10 states by number of cities
- Number and percentage of cities in snow-prone states
- Top 10 largest cities with snow-prone state indicators

## Troubleshooting

### Import Error
If you get an import error:
```
Error: Required packages not installed.
```
Run: `pip install -r requirements.txt`

### Download Fails
If the download fails:
- Check your internet connection
- Verify the SimpleMaps URL is still valid
- Try running the script again (downloads can timeout)

### Permission Error
If you get a permission error when saving:
- Ensure you have write permissions in the `data/` directory
- Check that the `data/` directory exists (it should be created automatically)

### CSV Processing Error
If CSV processing fails:
- The SimpleMaps data format may have changed
- Check the SimpleMaps website for updates
- Examine the downloaded CSV file manually

## Updating Cities Data

To update the cities database with the latest data:

1. Simply re-run the script:
   ```bash
   python process-cities.py
   ```

2. The script will download the latest data and regenerate the JSON file

3. Commit the updated `us-cities-100k.json` file to the repository

## Data Source

Data source: [SimpleMaps US Cities Database](https://simplemaps.com/data/us-cities)

The SimpleMaps database is updated regularly and provides comprehensive, accurate city data for the United States.

## License

This script is part of the Snow Alerts plugin and is licensed under GPL v2 or later.
